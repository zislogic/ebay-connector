<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Models\EbayCredential;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class EbayCredentialTest extends TestCase
{
    #[Test]
    public function it_encrypts_refresh_token(): void
    {
        $credential = $this->createCredential([
            'refresh_token' => 'my-secret-token',
        ]);

        $rawValue = $credential->getAttributes()['refresh_token'];
        $this->assertNotSame('my-secret-token', $rawValue);

        $decrypted = Crypt::decryptString($rawValue);
        $this->assertSame('my-secret-token', $decrypted);
    }

    #[Test]
    public function it_decrypts_refresh_token_on_access(): void
    {
        $credential = $this->createCredential([
            'refresh_token' => 'my-secret-token',
        ]);

        $fresh = EbayCredential::query()->find($credential->id);
        $this->assertNotNull($fresh);
        $this->assertSame('my-secret-token', $fresh->refresh_token);
    }

    #[Test]
    public function it_filters_active_credentials(): void
    {
        $this->createCredential(['is_active' => true]);
        $this->createCredential(['is_active' => false]);

        $activeCount = EbayCredential::query()->active()->count();

        $this->assertSame(1, $activeCount);
    }

    #[Test]
    public function it_filters_by_environment(): void
    {
        $this->createCredential(['environment' => 'sandbox']);
        $this->createCredential(['environment' => 'production']);

        $sandboxCount = EbayCredential::query()->environment('sandbox')->count();

        $this->assertSame(1, $sandboxCount);
    }

    #[Test]
    public function it_deactivates(): void
    {
        $credential = $this->createCredential(['is_active' => true]);

        $credential->deactivate();

        $this->assertFalse($credential->is_active);
        $credential->refresh();
        $this->assertFalse($credential->is_active);
    }

    #[Test]
    public function it_detects_expired_refresh_token(): void
    {
        $expiredCredential = $this->createCredential([
            'refresh_token_expires_at' => now()->subDay(),
        ]);

        $validCredential = $this->createCredential([
            'refresh_token_expires_at' => now()->addDay(),
        ]);

        $nullCredential = $this->createCredential([
            'refresh_token_expires_at' => null,
        ]);

        $this->assertTrue($expiredCredential->isRefreshTokenExpired());
        $this->assertFalse($validCredential->isRefreshTokenExpired());
        $this->assertFalse($nullCredential->isRefreshTokenExpired());
    }

    #[Test]
    public function it_enforces_unique_user_environment(): void
    {
        $this->createCredential([
            'ebay_user_id' => 'user-123',
            'environment' => 'sandbox',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->createCredential([
            'ebay_user_id' => 'user-123',
            'environment' => 'sandbox',
        ]);
    }
}
