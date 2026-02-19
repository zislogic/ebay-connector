<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zislogic\Ebay\Connector\EbayConnectorServiceProvider;
use Zislogic\Ebay\Connector\Models\EbayCredential;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EbayConnectorServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('ebay.environment', 'sandbox');
        $app['config']->set('ebay.credentials.sandbox', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect_uri' => 'http://localhost/ebay/oauth/callback',
        ]);
        $app['config']->set('ebay.deletion_notification.verification_token', 'test-token');
        $app['config']->set('ebay.deletion_notification.endpoint_url', 'http://localhost/ebay/account-deletion');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function createCredential(array $attributes = []): EbayCredential
    {
        return EbayCredential::query()->create(array_merge([
            'name' => 'Test User',
            'environment' => 'sandbox',
            'ebay_user_id' => 'test-user-' . uniqid(),
            'refresh_token' => 'test-refresh-token-' . uniqid(),
            'refresh_token_expires_at' => now()->addDays(30),
            'is_active' => true,
        ], $attributes));
    }
}
