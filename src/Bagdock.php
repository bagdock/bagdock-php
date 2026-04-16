<?php

declare(strict_types=1);

namespace Bagdock;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Bagdock\Resources\OperatorResource;
use Bagdock\Resources\MarketplaceResource;
use Bagdock\Resources\LoyaltyResource;
use Bagdock\OAuth\TokenManager;

class Bagdock
{
    private const DEFAULT_MAX_RETRIES = 3;
    private const MAX_RETRY_CAP = 5;

    private HttpClient $http;
    private ?TokenManager $tokenManager;
    private ?string $staticToken;
    private string $authMode;
    private int $maxRetries;
    public readonly OperatorResource $operator;
    public readonly MarketplaceResource $marketplace;
    public readonly LoyaltyResource $loyalty;

    /**
     * @param array{
     *   api_key?: string,
     *   access_token?: string,
     *   client_id?: string,
     *   client_secret?: string,
     *   scopes?: string[],
     *   base_url?: string,
     *   timeout?: float,
     *   max_retries?: int,
     * } $config
     */
    public function __construct(array $config = [])
    {
        $baseUrl = rtrim($config['base_url'] ?? 'https://api.bagdock.com/api/v1', '/');
        $timeout = $config['timeout'] ?? 30.0;
        $this->maxRetries = min($config['max_retries'] ?? self::DEFAULT_MAX_RETRIES, self::MAX_RETRY_CAP);

        if (!empty($config['api_key'])) {
            $this->authMode = 'api_key';
            $this->staticToken = $config['api_key'];
            $this->tokenManager = null;
        } elseif (!empty($config['access_token'])) {
            $this->authMode = 'access_token';
            $this->staticToken = $config['access_token'];
            $this->tokenManager = null;
        } elseif (!empty($config['client_id']) && !empty($config['client_secret'])) {
            $this->authMode = 'client_credentials';
            $this->staticToken = null;
            $this->tokenManager = new TokenManager(
                $config['client_id'],
                $config['client_secret'],
                $config['scopes'] ?? [],
            );
        } else {
            throw new Exceptions\AuthenticationException(
                'Provide one of: api_key, access_token, or client_id + client_secret.'
            );
        }

        $this->http = new HttpClient([
            'base_uri' => $baseUrl . '/',
            'timeout' => $timeout,
            'headers' => [
                'Authorization' => "Bearer {$this->resolveToken()}",
                'Content-Type' => 'application/json',
                'User-Agent' => 'bagdock-php/0.1.0',
            ],
        ]);

        $this->operator = new OperatorResource($this->http);
        $this->marketplace = new MarketplaceResource($this->http);
        $this->loyalty = new LoyaltyResource($this->http);
    }

    private function resolveToken(): string
    {
        if ($this->staticToken !== null) {
            return $this->staticToken;
        }
        return $this->tokenManager->getToken();
    }

    /**
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, ?array $body = null, ?array $query = null): array
    {
        try {
            $options = $this->buildOptions($body, $query);
            $response = $this->http->request($method, ltrim($path, '/'), $options);

            if ($response->getStatusCode() === 204) {
                return [];
            }

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401 && $this->tokenManager !== null) {
                $this->tokenManager->invalidate();
                $options = $this->buildOptions($body, $query);
                $options['headers'] = ['Authorization' => "Bearer {$this->resolveToken()}"];
                $response = $this->http->request($method, ltrim($path, '/'), $options);

                if ($response->getStatusCode() === 204) {
                    return [];
                }
                return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            }
            throw Exceptions\ApiException::fromResponse($e->getResponse());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOptions(?array $body, ?array $query): array
    {
        $options = [];
        if ($this->tokenManager !== null) {
            $options['headers'] = ['Authorization' => "Bearer {$this->resolveToken()}"];
        }
        if ($body !== null) {
            $options['json'] = $body;
        }
        if ($query !== null) {
            $options['query'] = $query;
        }
        return $options;
    }
}
