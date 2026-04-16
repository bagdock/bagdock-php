<?php

declare(strict_types=1);

namespace Bagdock\Resources;

use GuzzleHttp\Client as HttpClient;

class MarketplaceResource
{
    public function __construct(private readonly HttpClient $http) {}

    public function search(array $params = []): array
    {
        $response = $this->http->get('marketplace/search', ['query' => $params]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getListing(string $id): array
    {
        $response = $this->http->get("marketplace/listings/{$id}");
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function createRental(array $data): array
    {
        $response = $this->http->post('marketplace/rentals', ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getRental(string $id): array
    {
        $response = $this->http->get("marketplace/rentals/{$id}");
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
