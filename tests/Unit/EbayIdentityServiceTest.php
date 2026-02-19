<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;
use Zislogic\Ebay\Connector\Services\EbayIdentityService;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class EbayIdentityServiceTest extends TestCase
{
    private EbayIdentityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(EbayIdentityService::class);
    }

    #[Test]
    public function it_gets_user_data(): void
    {
        Http::fake([
            'apiz.sandbox.ebay.com/*' => Http::response([
                'userId' => 'testuser123',
                'username' => 'testuser',
                'email' => 'test@example.com',
            ], 200),
        ]);

        $userData = $this->service->getUser('test-access-token');

        $this->assertSame('testuser123', $userData['userId']);
        $this->assertSame('testuser', $userData['username']);
    }

    #[Test]
    public function it_throws_on_api_failure(): void
    {
        Http::fake([
            'apiz.sandbox.ebay.com/*' => Http::response([], 401),
        ]);

        $this->expectException(EbayAuthException::class);
        $this->expectExceptionMessage('HTTP 401');

        $this->service->getUser('invalid-token');
    }

    #[Test]
    public function it_throws_on_missing_user_id(): void
    {
        Http::fake([
            'apiz.sandbox.ebay.com/*' => Http::response([
                'username' => 'testuser',
            ], 200),
        ]);

        $this->expectException(EbayAuthException::class);
        $this->expectExceptionMessage('Missing userId');

        $this->service->getUser('test-token');
    }

    #[Test]
    public function it_sends_bearer_token(): void
    {
        Http::fake([
            'apiz.sandbox.ebay.com/*' => Http::response([
                'userId' => 'testuser123',
                'username' => 'testuser',
            ], 200),
        ]);

        $this->service->getUser('my-access-token');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer my-access-token');
        });
    }
}
