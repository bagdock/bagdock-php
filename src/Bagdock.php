<?php

declare(strict_types=1);

namespace Bagdock;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Bagdock\Resources\OperatorResource;
use Bagdock\Resources\MarketplaceResource;
use Bagdock\Resources\LoyaltyResource;

class Bagdock
{
    private HttpClient $http;
    public readonly OperatorResource $operator;
    public readonly MarketplaceResource $marketplace;
    public readonly LoyaltyResource $loyalty;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.bagdock.com/api/v1',
        float $timeout = 30.0,
        int $maxRetries = 2,
    ) {
        if (empty($apiKey)) {
            throw new Exceptions\AuthenticationException('Missing API key.');
        }

        $this->http = new HttpClient([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout' => $timeout,
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'User-Agent' => 'bagdock-php/0.1.0',
            ],
        ]);

        $this->operator = new OperatorResource($this->http);
        $this->marketplace = new MarketplaceResource($this->http);
        $this->loyalty = new LoyaltyResource($this->http);
    }

    /**
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, ?array $body = null, ?array $query = null): array
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }
            if ($query !== null) {
                $options['query'] = $query;
            }

            $response = $this->http->request($method, ltrim($path, '/'), $options);

            if ($response->getStatusCode() === 204) {
                return [];
            }

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            throw Exceptions\ApiException::fromResponse($e->getResponse());
        }
    }
}
