<?php

declare(strict_types=1);

namespace Bagdock\Resources;

use GuzzleHttp\Client as HttpClient;

class LoyaltyResource
{
    public function __construct(private readonly HttpClient $http) {}

    public function createMember(array $data): array
    {
        $response = $this->http->post('loyalty/members', ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getMember(string $id): array
    {
        $response = $this->http->get("loyalty/members/{$id}");
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function awardPoints(array $data): array
    {
        $response = $this->http->post('loyalty/points/award', ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function listRewards(array $params = []): array
    {
        $response = $this->http->get('loyalty/rewards', ['query' => $params]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
