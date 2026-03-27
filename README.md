# eBay Connector for Laravel

eBay OAuth 2.0 and infrastructure package for Laravel 12. Provides token management, authenticated HTTP client factory, marketplace reference data, and account deletion notifications.

## Features

- OAuth 2.0 authorization flow (automatic callback + manual exchange)
- Secure token storage (encrypted refresh tokens in DB, access tokens in cache)
- Automatic token refresh with 5-minute buffer
- Marketplace reference data for all eBay sites
- eBay account deletion compliance endpoint
- Identity API integration
- PHPStan level 8 compliant

## Installation

```bash
composer require zislogic/ebay-connector
```

## Configuration

Publish configuration:

```bash
php artisan vendor:publish --tag=ebay-config
```

Run migrations:

```bash
php artisan migrate
```

Seed marketplace data:

```bash
php artisan db:seed --class="Zislogic\Ebay\Connector\Database\Seeders\EbayMarketplaceSeeder"
```

Add to `.env`:

```env
# Active environment: "sandbox" or "production"
EBAY_ENVIRONMENT=sandbox

# OAuth callback method: "automatic" (eBay redirects to callback route)
# or "manual" (user pastes the callback URL via POST /ebay/oauth/exchange)
EBAY_CODE_EXCHANGE_METHOD=manual

# Sandbox credentials (from eBay Developer Program)
EBAY_SANDBOX_CLIENT_ID=your-sandbox-client-id
EBAY_SANDBOX_CLIENT_SECRET=your-sandbox-client-secret
EBAY_SANDBOX_REDIRECT_URI=your-sandbox-RuName

# Production credentials
EBAY_PRODUCTION_CLIENT_ID=your-production-client-id
EBAY_PRODUCTION_CLIENT_SECRET=your-production-client-secret
EBAY_PRODUCTION_REDIRECT_URI=your-production-RuName

# Account deletion compliance (optional)
EBAY_DELETION_VERIFICATION_TOKEN=your-verification-token
```

> **Note:** The config also supports simplified keys (`EBAY_CLIENT_ID`, `EBAY_CLIENT_SECRET`, `EBAY_REDIRECT_URI`) as fallbacks when you only need one environment. Per-environment keys take priority.

## Usage

### OAuth Flow

Redirect user to eBay:

```php
return redirect()->route('ebay.oauth.redirect');
```

eBay will redirect back to `/ebay/oauth/callback` automatically.

### Using the HTTP Client

```php
use Zislogic\Ebay\Connector\Services\EbayHttpClient;

$client = app(EbayHttpClient::class);

// For seller operations
$response = $client->forSeller($credentialId)
    ->get('/sell/inventory/v1/inventory_item');

// For application operations (public API)
$response = $client->forApplication()
    ->get('/buy/browse/v1/item/v1|123456789|0');

// Raw token for OpenAPI-generated clients
$token = $client->getSellerAccessToken($credentialId);
```

### Token Refresh Command

```bash
php artisan ebay:refresh-tokens
```

Schedule hourly in your application:

```php
$schedule->command('ebay:refresh-tokens')->hourly();
```

## Testing

```bash
composer test
composer analyse
```

## License

MIT
