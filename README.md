```
  ----++                                ----++                    ---+++     
  ---+++                                ---++                     ---++      
 ----+---     -----     ---------  --------++ ------     -----   ----++----- 
 ---------+ --------++----------++--------+++--------+ --------++---++---++++
 ---+++---++ ++++---++---+++---++---+++---++---+++---++---++---++------++++  
----++ ---++--------++---++----++---++ ---++---++ ---+---++     -------++    
----+----+---+++---++---++----++---++----++---++---+++--++ --------+---++   
---------++--------+++--------+++--------++ -------+++ -------++---++----++  
 +++++++++   +++++++++- +++---++   ++++++++    ++++++    ++++++  ++++  ++++  
                     --------+++                                             
                       +++++++                                               
```

# bagdock-php

The official PHP SDK for the Bagdock API — manage facilities, contacts, tenancies, invoices, marketplace listings, and loyalty programs with Guzzle-powered HTTP.

[![Packagist Version](https://img.shields.io/packagist/v/bagdock/bagdock-php.svg)](https://packagist.org/packages/bagdock/bagdock-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

## Install

```bash
composer require bagdock/bagdock-php
```

## Quick start

```php
<?php

use Bagdock\Bagdock;

$client = new Bagdock(apiKey: 'sk_live_...');

// List operator facilities
$facilities = $client->operator->listFacilities();

// Create a contact
$contact = $client->operator->createContact([
    'email' => 'jane@example.com',
    'first_name' => 'Jane',
    'last_name' => 'Doe',
]);

// Search marketplace
$results = $client->marketplace->search(['city' => 'London', 'size' => 'medium']);
```

## API reference

### `$client->operator`

| Method | Description |
|--------|-------------|
| `listFacilities(params?)` | List facilities |
| `getFacility(id)` | Get a facility |
| `listContacts(params?)` | List contacts |
| `createContact(data)` | Create a contact |
| `listListings(params?)` | List listings |
| `listTenancies(params?)` | List tenancies |
| `listUnits(params?)` | List units |
| `listInvoices(params?)` | List invoices |
| `listPayments(params?)` | List payments |

### `$client->marketplace`

| Method | Description |
|--------|-------------|
| `search(params?)` | Search marketplace locations |
| `getListing(id)` | Get a listing |
| `createRental(data)` | Create a rental |
| `getRental(id)` | Get a rental |

### `$client->loyalty`

| Method | Description |
|--------|-------------|
| `createMember(data)` | Create a loyalty member |
| `getMember(id)` | Get a member |
| `awardPoints(data)` | Award points |
| `listRewards(params?)` | List rewards |

## Error handling

```php
use Bagdock\Exceptions\ApiException;

try {
    $client->operator->getFacility('fac_nonexistent');
} catch (ApiException $e) {
    echo "API error {$e->statusCode}: {$e->errorCode} — {$e->getMessage()}\n";
    echo "Request ID: {$e->requestId}\n";
}
```

## Authentication

The SDK supports three authentication modes: API keys, OAuth access tokens, and OAuth2 client credentials.

### API key

```php
$bagdock = new \Bagdock\Bagdock(['api_key' => 'sk_live_...']);
```

### OAuth access token

```php
$bagdock = new \Bagdock\Bagdock(['access_token' => 'eyJhbGciOiJSUzI1NiIs...']);
```

### Client credentials

```php
$bagdock = new \Bagdock\Bagdock([
    'client_id' => 'oac_your_client_id',
    'client_secret' => 'bdok_secret_your_secret',
    'scopes' => ['facilities:read', 'contacts:read'],
]);
```

### OAuth2 helpers

Use `Bagdock\OAuth\OAuthHelper` for authorization code (PKCE), token exchange, and device flow. For external integrations, Bagdock **connect webviews** open OAuth for access control, security, and insurance without you reimplementing the full redirect flow—use these helpers when your app drives the OAuth dance directly (for example, server-side or native clients).

```php
use Bagdock\OAuth\OAuthHelper;

$pkce = OAuthHelper::generatePKCE();
$url = OAuthHelper::buildAuthorizeUrl('oac_...', 'https://your-app.com/callback', $pkce['code_challenge'], 'openid contacts:read');

$tokens = OAuthHelper::exchangeCode('oac_...', $code, 'https://your-app.com/callback', $pkce['code_verifier']);

$device = OAuthHelper::deviceAuthorize('bagdock-cli', 'developer:read');
echo "Open {$device['verification_uri']} and enter: {$device['user_code']}\n";
$deviceTokens = OAuthHelper::pollDeviceToken('bagdock-cli', $device['device_code']);
```

## Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `api_key` | `string` | — | Bagdock API key (API key auth). Also accepted as `apiKey` |
| `access_token` | `string` | — | OAuth access token. Also accepted as `accessToken` |
| `client_id` | `string` | — | OAuth2 client ID for client-credentials flow (use with `client_secret` and `scopes`) |
| `client_secret` | `string` | — | OAuth2 client secret |
| `scopes` | `array` | — | OAuth2 scopes for client credentials |
| `baseUrl` | `string` | `https://api.bagdock.com/api/v1` | API base URL |
| `timeout` | `float` | `30.0` | Request timeout in seconds |
| `maxRetries` | `int` | `3` | Max retry attempts for transient errors (capped at 5) |

Provide one authentication method: API key (`api_key` / `apiKey`), `access_token`, or client credentials (`client_id`, `client_secret`, `scopes`).

## Documentation

- [Full documentation](https://bagdock.com/docs)
- [PHP SDK quickstart](https://bagdock.com/docs/sdks/php)
- [API reference](https://bagdock.com/docs/api)
- [OAuth2 / OIDC guide](https://bagdock.com/docs/auth/oauth2)

## License

MIT — see [LICENSE](LICENSE)
