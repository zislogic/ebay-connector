<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class RefreshTokensCommandTest extends TestCase
{
    #[Test]
    public function it_refreshes_active_credentials(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'refreshed-token',
                'expires_in' => 7200,
            ], 200),
        ]);

        $this->createCredential(['is_active' => true]);
        $this->createCredential(['is_active' => false]);

        $this->artisan('ebay:refresh-tokens')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_reports_no_credentials(): void
    {
        $this->artisan('ebay:refresh-tokens')
            ->expectsOutputToContain('No active eBay credentials to refresh')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_handles_refresh_failures(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $credential = $this->createCredential(['is_active' => true]);

        $this->artisan('ebay:refresh-tokens')
            ->assertExitCode(1);

        $credential->refresh();
        $this->assertFalse($credential->is_active);
    }

    #[Test]
    public function it_skips_credentials_with_expired_refresh_tokens(): void
    {
        $this->createCredential([
            'is_active' => true,
            'refresh_token_expires_at' => now()->subDay(),
        ]);

        $this->artisan('ebay:refresh-tokens')
            ->expectsOutputToContain('No active eBay credentials to refresh')
            ->assertExitCode(0);
    }
}
