# eBay Connector Guidelines

## Overview
`zislogic/ebay-connector` is the base eBay infrastructure package. It provides OAuth 2.0, token management, and an authenticated HTTP client factory. All other eBay API packages depend on it.

## Token Strategy
- **Application tokens** (Client Credentials Grant): cached, no user consent needed. Use `EbayHttpClient::getApplicationAccessToken()`.
- **Seller tokens** (Authorization Code Grant): refresh token in DB (`ebay_credentials` table, encrypted), access token in cache only. Use `EbayHttpClient::getSellerAccessToken($credentialId)`.
- Never store access tokens in the database — cache is the only source.
- Application tokens use only the base scope `https://api.ebay.com/oauth/api_scope`.

## HTTP Client Usage
```php
// For seller-specific API calls (requires OAuth consent)
$client = app(EbayHttpClient::class);
$request = $client->forSeller($credentialId); // Returns PendingRequest

// For public/app-level API calls (no consent needed)
$request = $client->forApplication(); // Returns PendingRequest

// For OpenAPI-generated clients (raw token string)
$token = $client->getSellerAccessToken($credentialId);
$appToken = $client->getApplicationAccessToken();
```

## Error Handling Trait
All eBay API commands should use `HandlesEbayApiErrors` from `Zislogic\Ebay\Connector\Concerns`:
```php
use Zislogic\Ebay\Connector\Concerns\HandlesEbayApiErrors;

class MyCommand extends Command
{
    use HandlesEbayApiErrors;

    public function handle(): int
    {
        $result = $this->callWithRetry(fn () => $apiCall());
    }
}
```
This provides automatic retry for HTTP 401 (token refresh) and HTTP 429 (rate limit, 60s wait).

## Configuration
Credentials come from `.env`, never hardcoded:
- `EBAY_ENVIRONMENT` — `sandbox` or `production`
- `EBAY_SANDBOX_CLIENT_ID`, `EBAY_SANDBOX_CLIENT_SECRET`, `EBAY_SANDBOX_REDIRECT_URI`
- `EBAY_PRODUCTION_CLIENT_ID`, `EBAY_PRODUCTION_CLIENT_SECRET`, `EBAY_PRODUCTION_REDIRECT_URI`

## Database Tables
- `ebay_credentials` — seller OAuth credentials (encrypted refresh tokens)
- `ebay_marketplaces` — reference data (site_id, marketplace_id, locale)

## Table Naming Convention
All eBay packages use prefixed table names:
- `ebay_` — connector base tables
- `ebay_meta_` — metadata API tables
- `ebay_taxo_` — taxonomy API tables
- Future packages follow the pattern: `ebay_{shortname}_`

## Code Style
- `declare(strict_types=1)` on all files
- `final` classes by default
- `readonly` on value objects/DTOs
- Return type declarations on all methods
- PHPStan level 8
