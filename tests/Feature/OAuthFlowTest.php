<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Feature;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class OAuthFlowTest extends TestCase
{
    #[Test]
    public function it_redirects_to_ebay_authorization(): void
    {
        $user = new class extends Authenticatable
        {
            protected $guarded = [];

            public function getAuthIdentifier(): int
            {
                return 1;
            }
        };

        $response = $this->actingAs($user)
            ->get('/ebay/oauth/redirect');

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('auth.sandbox.ebay.com', $location);
        $this->assertStringContainsString('client_id=test-client-id', $location);
        $this->assertStringContainsString('response_type=code', $location);
    }

    #[Test]
    public function it_handles_callback_and_creates_credential(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/identity/v1/oauth2/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 7200,
                'refresh_token' => 'new-refresh-token',
                'refresh_token_expires_in' => 47304000,
            ], 200),
            'apiz.sandbox.ebay.com/commerce/identity/v1/user/' => Http::response([
                'userId' => 'ebay-user-123',
                'username' => 'testuser',
            ], 200),
        ]);

        $response = $this->withSession(['ebay_oauth_state' => 'test-state', '_token' => 'test-state'])
            ->get('/ebay/oauth/callback?code=auth-code&state=test-state');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ebay_credentials', [
            'ebay_user_id' => 'ebay-user-123',
            'environment' => 'sandbox',
            'name' => 'testuser',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_rejects_invalid_state(): void
    {
        $response = $this->withSession(['ebay_oauth_state' => 'valid-state'])
            ->get('/ebay/oauth/callback?code=auth-code&state=invalid-state');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('ebay_credentials', 0);
    }

    #[Test]
    public function it_rejects_missing_code(): void
    {
        $response = $this->withSession(['ebay_oauth_state' => 'test-state'])
            ->get('/ebay/oauth/callback?state=test-state');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');
    }

    #[Test]
    public function it_handles_exchange_with_callback_url(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/identity/v1/oauth2/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 7200,
                'refresh_token' => 'new-refresh-token',
            ], 200),
            'apiz.sandbox.ebay.com/commerce/identity/v1/user/' => Http::response([
                'userId' => 'ebay-user-456',
                'username' => 'testuser2',
            ], 200),
        ]);

        $user = new class extends Authenticatable
        {
            protected $guarded = [];

            public function getAuthIdentifier(): int
            {
                return 1;
            }
        };

        $response = $this->actingAs($user)
            ->post('/ebay/oauth/exchange', [
                'callback_url' => 'http://localhost/ebay/oauth/callback?code=manual-code&state=xyz',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ebay_credentials', [
            'ebay_user_id' => 'ebay-user-456',
        ]);
    }

    #[Test]
    public function it_updates_existing_credential_on_reconnect(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/identity/v1/oauth2/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 7200,
                'refresh_token' => 'updated-refresh-token',
                'refresh_token_expires_in' => 47304000,
            ], 200),
            'apiz.sandbox.ebay.com/commerce/identity/v1/user/' => Http::response([
                'userId' => 'existing-user',
                'username' => 'updated-name',
            ], 200),
        ]);

        $this->createCredential([
            'ebay_user_id' => 'existing-user',
            'environment' => 'sandbox',
            'name' => 'old-name',
            'is_active' => false,
        ]);

        $response = $this->withSession(['ebay_oauth_state' => 'test-state'])
            ->get('/ebay/oauth/callback?code=auth-code&state=test-state');

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseCount('ebay_credentials', 1);
        $this->assertDatabaseHas('ebay_credentials', [
            'ebay_user_id' => 'existing-user',
            'name' => 'updated-name',
            'is_active' => true,
        ]);
    }
}
