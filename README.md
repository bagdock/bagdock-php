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

## Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `apiKey` | `string` | — | **Required.** Your Bagdock API key |
| `baseUrl` | `string` | `https://api.bagdock.com/api/v1` | API base URL |
| `timeout` | `float` | `30.0` | Request timeout in seconds |
| `maxRetries` | `int` | `2` | Max retry attempts for transient errors |

## License

MIT — see [LICENSE](LICENSE)
