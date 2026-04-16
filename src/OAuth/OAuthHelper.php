<?php

declare(strict_types=1);

namespace Bagdock\OAuth;

use GuzzleHttp\Client;

class OAuthException extends \RuntimeException
{
    public readonly string $errorCode;
    public readonly int $statusCode;

    public function __construct(string $message, string $errorCode = 'oauth_error', int $statusCode = 0)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }
}

class OAuthHelper
{
    private const DEFAULT_ISSUER = 'https://api.bagdock.com';

    public static function generatePKCE(): array
    {
        $verifierBytes = random_bytes(32);
        $codeVerifier = rtrim(strtr(base64_encode($verifierBytes), '+/', '-_'), '=');
        $digest = hash('sha256', $codeVerifier, true);
        $codeChallenge = rtrim(strtr(base64_encode($digest), '+/', '-_'), '=');

        return [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
        ];
    }

    public static function buildAuthorizeUrl(
        string $clientId,
        string $redirectUri,
        string $codeChallenge,
        ?string $scope = null,
        ?string $state = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): string {
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];
        if ($scope !== null) $params['scope'] = $scope;
        if ($state !== null) $params['state'] = $state;

        return rtrim($issuer, '/') . '/oauth2/authorize?' . http_build_query($params);
    }

    public static function exchangeCode(
        string $clientId,
        string $code,
        string $redirectUri,
        string $codeVerifier,
        ?string $clientSecret = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): array {
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
        ];
        if ($clientSecret !== null) $data['client_secret'] = $clientSecret;

        return self::postForm(rtrim($issuer, '/') . '/oauth2/token', $data);
    }

    public static function refreshToken(
        string $clientId,
        string $refreshToken,
        ?string $clientSecret = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): array {
        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'refresh_token' => $refreshToken,
        ];
        if ($clientSecret !== null) $data['client_secret'] = $clientSecret;

        return self::postForm(rtrim($issuer, '/') . '/oauth2/token', $data);
    }

    public static function revokeToken(
        string $token,
        ?string $tokenTypeHint = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): void {
        $data = ['token' => $token];
        if ($tokenTypeHint !== null) $data['token_type_hint'] = $tokenTypeHint;

        self::postForm(rtrim($issuer, '/') . '/oauth2/token/revoke', $data);
    }

    public static function introspectToken(
        string $token,
        ?string $tokenTypeHint = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): array {
        $data = ['token' => $token];
        if ($tokenTypeHint !== null) $data['token_type_hint'] = $tokenTypeHint;

        return self::postForm(rtrim($issuer, '/') . '/oauth2/token/introspect', $data);
    }

    public static function deviceAuthorize(
        string $clientId,
        ?string $scope = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): array {
        $data = ['client_id' => $clientId];
        if ($scope !== null) $data['scope'] = $scope;

        return self::postForm(rtrim($issuer, '/') . '/oauth2/device/authorize', $data);
    }

    public static function pollDeviceToken(
        string $clientId,
        string $deviceCode,
        int $interval = 5,
        int $timeout = 600,
        ?string $clientSecret = null,
        string $issuer = self::DEFAULT_ISSUER,
    ): array {
        $deadline = time() + $timeout;
        $pollInterval = $interval;

        while (time() < $deadline) {
            sleep($pollInterval);
            $data = [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                'client_id' => $clientId,
                'device_code' => $deviceCode,
            ];
            if ($clientSecret !== null) $data['client_secret'] = $clientSecret;

            try {
                return self::postForm(rtrim($issuer, '/') . '/oauth2/token', $data);
            } catch (OAuthException $e) {
                if ($e->errorCode === 'authorization_pending') continue;
                if ($e->errorCode === 'slow_down') {
                    $pollInterval += 5;
                    continue;
                }
                throw $e;
            }
        }

        throw new OAuthException('Device authorization timed out', 'expired_token', 408);
    }

    private static function postForm(string $url, array $data): array
    {
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $data,
            'http_errors' => false,
        ]);

        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if ($response->getStatusCode() >= 400) {
            throw new OAuthException(
                $body['error_description'] ?? $body['error'] ?? "HTTP {$response->getStatusCode()}",
                $body['error'] ?? 'oauth_error',
                $response->getStatusCode(),
            );
        }

        return $body;
    }
}
