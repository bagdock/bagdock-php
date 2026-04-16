<?php

declare(strict_types=1);

namespace Bagdock\Resources;

use GuzzleHttp\Client as HttpClient;

class OperatorResource
{
    public function __construct(private readonly HttpClient $http) {}

    public function listFacilities(array $params = []): array
    {
        return $this->get('/operator/facilities', $params);
    }

    public function getFacility(string $id): array
    {
        return $this->get("/operator/facilities/{$id}");
    }

    public function listContacts(array $params = []): array
    {
        return $this->get('/operator/contacts', $params);
    }

    public function createContact(array $data): array
    {
        return $this->post('/operator/contacts', $data);
    }

    public function listListings(array $params = []): array
    {
        return $this->get('/operator/listings', $params);
    }

    public function listTenancies(array $params = []): array
    {
        return $this->get('/operator/tenancies', $params);
    }

    public function listUnits(array $params = []): array
    {
        return $this->get('/operator/units', $params);
    }

    public function listInvoices(array $params = []): array
    {
        return $this->get('/operator/invoices', $params);
    }

    public function listPayments(array $params = []): array
    {
        return $this->get('/operator/payments', $params);
    }

    private function get(string $path, array $params = []): array
    {
        $response = $this->http->get(ltrim($path, '/'), ['query' => $params]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function post(string $path, array $data): array
    {
        $response = $this->http->post(ltrim($path, '/'), ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
