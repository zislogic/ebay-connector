<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Exceptions\TokenRefreshException;
use Zislogic\Ebay\Connector\Services\EbayTokenManager;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class EbayTokenManagerTest extends TestCase
{
    private EbayTokenManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(EbayTokenManager::class);
    }

    #[Test]
    public function it_gets_token_from_cache(): void
    {
        $credential = $this->createCredential();
        Cache::put("ebay.access_token.{$credential->id}", 'cached-token', 3600);

        $token = $this->manager->getSellerAccessToken($credential->id);

        $this->assertSame('cached-token', $token);
    }

    #[Test]
    public function it_refreshes_token_on_cache_miss(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'fresh-token',
                'expires_in' => 7200,
            ], 200),
        ]);

        $credential = $this->createCredential([
            'refresh_token' => 'valid-refresh-token',
        ]);

        $token = $this->manager->getSellerAccessToken($credential->id);

        $this->assertSame('fresh-token', $token);
        $this->assertSame('fresh-token', Cache::get("ebay.access_token.{$credential->id}"));
    }

    #[Test]
    public function it_throws_on_missing_credential(): void
    {
        $this->expectException(TokenRefreshException::class);
        $this->expectExceptionMessage('not found');

        $this->manager->getSellerAccessToken(99999);
    }

    #[Test]
    public function it_throws_on_inactive_credential(): void
    {
        $credential = $this->createCredential(['is_active' => false]);

        $this->expectException(TokenRefreshException::class);
        $this->expectExceptionMessage('is inactive');

        $this->manager->getSellerAccessToken($credential->id);
    }

    #[Test]
    public function it_deactivates_credential_on_refresh_failure(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'error' => 'invalid_grant',
            ], 400),
        ]);

        $credential = $this->createCredential(['is_active' => true]);

        try {
            $this->manager->getSellerAccessToken($credential->id);
            $this->fail('Expected TokenRefreshException');
        } catch (TokenRefreshException) {
            // Expected
        }

        $credential->refresh();
        $this->assertFalse($credential->is_active);
    }

    #[Test]
    public function it_deactivates_on_expired_refresh_token(): void
    {
        $credential = $this->createCredential([
            'is_active' => true,
            'refresh_token_expires_at' => now()->subDay(),
        ]);

        try {
            $this->manager->getSellerAccessToken($credential->id);
            $this->fail('Expected TokenRefreshException');
        } catch (TokenRefreshException) {
            // Expected
        }

        $credential->refresh();
        $this->assertFalse($credential->is_active);
    }

    #[Test]
    public function it_updates_refresh_token_if_provided(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'fresh-token',
                'expires_in' => 7200,
                'refresh_token' => 'new-refresh-token',
                'refresh_token_expires_in' => 47304000,
            ], 200),
        ]);

        $credential = $this->createCredential([
            'refresh_token' => 'old-refresh-token',
        ]);

        $this->manager->getSellerAccessToken($credential->id);

        $credential->refresh();
        $this->assertSame('new-refresh-token', $credential->refresh_token);
    }

    #[Test]
    public function it_caches_application_token(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'app-token',
                'expires_in' => 7200,
            ], 200),
        ]);

        $token = $this->manager->getApplicationAccessToken();

        $this->assertSame('app-token', $token);
        $this->assertSame('app-token', Cache::get('ebay.application_token.sandbox'));
    }

    #[Test]
    public function it_returns_cached_application_token(): void
    {
        Cache::put('ebay.application_token.sandbox', 'cached-app-token', 3600);

        $token = $this->manager->getApplicationAccessToken();

        $this->assertSame('cached-app-token', $token);
    }
}
