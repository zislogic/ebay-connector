<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests\Unit;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Zislogic\Ebay\Connector\Services\EbayHttpClient;
use Zislogic\Ebay\Connector\Tests\TestCase;

final class EbayHttpClientTest extends TestCase
{
    private EbayHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->app->make(EbayHttpClient::class);
    }

    #[Test]
    public function it_creates_seller_client(): void
    {
        $credential = $this->createCredential();
        Cache::put("ebay.access_token.{$credential->id}", 'test-token', 3600);

        $httpClient = $this->client->forSeller($credential->id);

        $this->assertInstanceOf(PendingRequest::class, $httpClient);
    }

    #[Test]
    public function it_creates_application_client(): void
    {
        Cache::put('ebay.application_token.sandbox', 'app-token', 3600);

        $httpClient = $this->client->forApplication();

        $this->assertInstanceOf(PendingRequest::class, $httpClient);
    }

    #[Test]
    public function it_gets_raw_seller_token(): void
    {
        $credential = $this->createCredential();
        Cache::put("ebay.access_token.{$credential->id}", 'test-token', 3600);

        $token = $this->client->getSellerAccessToken($credential->id);

        $this->assertSame('test-token', $token);
    }

    #[Test]
    public function it_gets_raw_application_token(): void
    {
        Cache::put('ebay.application_token.sandbox', 'app-token', 3600);

        $token = $this->client->getApplicationAccessToken();

        $this->assertSame('app-token', $token);
    }
}
