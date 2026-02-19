# CLAUDE.md

## Package
`zislogic/ebay-connector` — eBay infrastructure package for Laravel 12.
Provides OAuth 2.0, token management, authenticated HTTP client factory,
marketplace reference data, account deletion notifications, and inline Identity API.

This is the base package that ALL eBay API plugins depend on.
It does NOT make eBay API calls beyond OAuth token endpoints and Identity resolution.

## Tech Stack
- PHP 8.2+, Laravel 12
- No Guzzle dependency — uses Laravel's HTTP client (`illuminate/http`)
- Orchestra Testbench for testing
- PHPStan level 8

## Namespace
`Zislogic\Ebay\Connector\`

## Commands
- `composer test` — run PHPUnit
- `composer analyse` — run PHPStan level 8
- `php artisan ebay:refresh-tokens` — proactive token refresh

## Architecture

### Token Strategy
- **Refresh tokens** → DB (`ebay_credentials` table, encrypted via `Crypt::encryptString()`)
- **Access tokens** → Cache (Redis/file), TTL = expiry minus 5-minute buffer
- **Application tokens** → Cache only (Client Credentials Grant, no DB, no refresh token)
- **No access_token column in DB** — cache is the only source for access tokens
- eBay tokens are **site-agnostic** — one token works across all marketplaces (DE, US, UK)
- Site targeting happens at API call level (`X-EBAY-C-MARKETPLACE-ID` header)

### App Credentials
- Stored in `config/ebay.php` via env vars, NOT in the database
- One keyset per environment (sandbox / production)
- Scopes defined in config per environment

### OAuth Flow (3 routes)
1. `GET /ebay/oauth/redirect` — builds consent URL, redirects to eBay
2. `GET /ebay/oauth/callback` — automatic: eBay redirects here with code
3. `POST /ebay/oauth/exchange` — manual: user pastes full callback URL

After code exchange (both flows):
→ Exchange code for tokens → Cache access token → Store refresh token in DB
→ Call Identity API to resolve seller UserID → findOrCreate credential by (ebay_user_id, environment)

### HTTP Client Factory
`EbayHttpClient` returns authenticated Laravel `PendingRequest` instances:
- `forSeller(int $credentialId)` — Bearer token + base URL (for hand-written plugins)
- `forApplication()` — app token + base URL (for public API calls)
- `getSellerAccessToken(int $credentialId)` — raw token string (for OpenAPI-generated plugins)
- `getApplicationAccessToken()` — raw app token string

### Account Deletion Notifications
- `GET/POST /ebay/account-deletion` — eBay compliance endpoint
- Challenge verification: `sha256(challenge_code + verification_token + endpoint_url)`
- Verification token from config: `ebay.deletion_notification.verification_token`

## Key Files
```
src/
├── EbayConnectorServiceProvider.php
├── Auth/
│   ├── EbayOAuthClient.php              # HTTP calls to eBay token endpoints
│   └── TokenResponse.php                # Value object (readonly)
├── Services/
│   ├── EbayTokenManager.php             # Cache-first token retrieval + refresh
│   ├── EbayHttpClient.php               # Factory: authenticated PendingRequest
│   └── EbayIdentityService.php          # Resolves seller UserID via REST
├── Models/
│   ├── EbayCredential.php               # Encrypted refresh_token, unique (user_id, env)
│   └── EbayMarketplace.php              # Reference data (site_id, marketplace_id, etc.)
├── Http/Controllers/
│   ├── EbayOAuthController.php          # redirect, callback, exchange
│   └── AccountDeletionController.php    # eBay compliance endpoint
├── Commands/
│   └── RefreshEbayTokensCommand.php     # Scheduled hourly, jitter between refreshes
└── Exceptions/
    ├── EbayAuthException.php
    └── TokenRefreshException.php
```

## Database Tables
- `ebay_credentials` — id, name, environment, ebay_user_id, refresh_token (encrypted), refresh_token_expires_at, is_active. Unique: (ebay_user_id, environment)
- `ebay_marketplaces` — reference data seeded with all eBay sites (site_id, marketplace_id, site_code, locale)

## Code Style
- `declare(strict_types=1)` everywhere
- `final` classes by default
- `readonly` properties on value objects and DTOs
- Return type declarations on all methods
- No `mixed` types unless absolutely unavoidable

## Important Rules
- NEVER hardcode eBay API credentials — always config/env
- NEVER store unencrypted OAuth tokens
- NEVER store access tokens in the database — cache only
- NEVER make eBay API calls beyond OAuth token endpoints and Identity API
- All errors from eBay throw typed exceptions (EbayAuthException / TokenRefreshException)
- Deactivate credential (`is_active = false`) on refresh failure
- Token endpoint uses Basic auth: `base64(client_id:client_secret)`
- Token endpoint uses `Content-Type: application/x-www-form-urlencoded`
- Callback route must allow unauthenticated access (eBay redirects here)

## Dependency Role
```
zislogic/ebay-connector (this package — no Zislogic dependencies in v1)
    ↑
    ├── zislogic/ebay-api-fulfillment (future)
    ├── zislogic/ebay-api-account (future)
    ├── zislogic/ebay-api-inventory (future)
    ├── zislogic/ebay-api-browse (future)
    └── zislogic/ebay-mip (future)
```

## Build Spec
See `Phase1_eBay_Connector_Build_Spec.md` for the complete build specification
with all class signatures, test plans, and validation checklist.
