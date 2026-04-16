<?php

declare(strict_types=1);

namespace Bagdock\OAuth;

use GuzzleHttp\Client;

class TokenManager
{
    private string $clientId;
    private string $clientSecret;
    /** @var string[] */
    private array $scopes;
    private string $tokenUrl;
    private ?string $accessToken = null;
    private float $expiresAt = 0;

    /**
     * @param string[] $scopes
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        array $scopes = [],
        string $issuer = 'https://api.bagdock.com',
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;
        $this->tokenUrl = rtrim($issuer, '/') . '/oauth2/token';
    }

    public function getToken(): string
    {
        if ($this->accessToken !== null && microtime(true) < $this->expiresAt) {
            return $this->accessToken;
        }

        return $this->fetchToken();
    }

    public function invalidate(): void
    {
        $this->accessToken = null;
        $this->expiresAt = 0;
    }

    private function fetchToken(): string
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        if (!empty($this->scopes)) {
            $data['scope'] = implode(' ', $this->scopes);
        }

        $client = new Client();
        $response = $client->post($this->tokenUrl, [
            'form_params' => $data,
            'http_errors' => false,
        ]);

        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if ($response->getStatusCode() >= 400) {
            throw new OAuthException(
                $body['error_description'] ?? 'Token fetch failed',
                $body['error'] ?? 'oauth_error',
                $response->getStatusCode(),
            );
        }

        $this->accessToken = $body['access_token'];
        $this->expiresAt = microtime(true) + $body['expires_in'] - 60;

        return $this->accessToken;
    }
}
