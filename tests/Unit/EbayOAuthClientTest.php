<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Auth\EbayOAuthClient;
use Zislogic\Ebay\Connector\Auth\TokenResponse;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class EbayOAuthClientTest extends TestCase
{
    private EbayOAuthClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->app->make(EbayOAuthClient::class);
    }

    #[Test]
    public function it_builds_authorization_url(): void
    {
        $url = $this->client->getAuthorizationUrl('test-state');

        $this->assertStringContainsString('auth.sandbox.ebay.com', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('state=test-state', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
    }

    #[Test]
    public function it_builds_authorization_url_without_state(): void
    {
        $url = $this->client->getAuthorizationUrl();

        $this->assertStringContainsString('auth.sandbox.ebay.com', $url);
        $this->assertStringNotContainsString('state=', $url);
    }

    #[Test]
    public function it_exchanges_code_for_tokens(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 7200,
                'refresh_token' => 'new-refresh-token',
                'refresh_token_expires_in' => 47304000,
                'token_type' => 'User Access Token',
            ], 200),
        ]);

        $response = $this->client->exchangeCodeForTokens('auth-code-123');

        $this->assertInstanceOf(TokenResponse::class, $response);
        $this->assertSame('new-access-token', $response->accessToken);
        $this->assertSame(7200, $response->expiresIn);
        $this->assertSame('new-refresh-token', $response->refreshToken);
        $this->assertSame(47304000, $response->refreshTokenExpiresIn);
    }

    #[Test]
    public function it_throws_on_exchange_failure(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code is invalid',
            ], 400),
        ]);

        $this->expectException(EbayAuthException::class);
        $this->expectExceptionMessage('Authorization code is invalid');

        $this->client->exchangeCodeForTokens('bad-code');
    }

    #[Test]
    public function it_refreshes_access_token(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'refreshed-access-token',
                'expires_in' => 7200,
                'token_type' => 'User Access Token',
            ], 200),
        ]);

        $response = $this->client->refreshAccessToken('refresh-token-123');

        $this->assertInstanceOf(TokenResponse::class, $response);
        $this->assertSame('refreshed-access-token', $response->accessToken);
        $this->assertNull($response->refreshToken);
    }

    #[Test]
    public function it_gets_application_token(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'app-token',
                'expires_in' => 7200,
                'token_type' => 'Application Access Token',
            ], 200),
        ]);

        $response = $this->client->getApplicationToken();

        $this->assertInstanceOf(TokenResponse::class, $response);
        $this->assertSame('app-token', $response->accessToken);
        $this->assertNull($response->refreshToken);
    }

    #[Test]
    public function it_throws_on_refresh_failure(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'Refresh token is invalid',
            ], 400),
        ]);

        $this->expectException(EbayAuthException::class);
        $this->expectExceptionMessage('Refresh token is invalid');

        $this->client->refreshAccessToken('bad-refresh-token');
    }

    #[Test]
    public function it_sends_basic_auth_header(): void
    {
        Http::fake([
            'api.sandbox.ebay.com/*' => Http::response([
                'access_token' => 'test',
                'expires_in' => 7200,
            ], 200),
        ]);

        $this->client->getApplicationToken();

        Http::assertSent(function ($request) {
            $expectedAuth = 'Basic ' . base64_encode('test-client-id:test-client-secret');

            return $request->hasHeader('Authorization', $expectedAuth);
        });
    }
}
