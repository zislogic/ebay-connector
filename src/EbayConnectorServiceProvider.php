<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector;

use Illuminate\Support\ServiceProvider;
use Zislogic\Ebay\Connector\Auth\EbayOAuthClient;
use Zislogic\Ebay\Connector\Commands\RefreshEbayTokensCommand;
use Zislogic\Ebay\Connector\Http\Controllers\AccountDeletionController;
use Zislogic\Ebay\Connector\Http\Controllers\EbayOAuthController;
use Zislogic\Ebay\Connector\Services\EbayHttpClient;
use Zislogic\Ebay\Connector\Services\EbayIdentityService;
use Zislogic\Ebay\Connector\Services\EbayTokenManager;

final class EbayConnectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ebay.php', 'ebay');

        $this->app->singleton(EbayOAuthClient::class, function ($app): EbayOAuthClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']['ebay'];
            $environment = (string) ($config['environment'] ?? 'sandbox');

            return new EbayOAuthClient($config, $environment);
        });

        $this->app->singleton(EbayTokenManager::class, function ($app): EbayTokenManager {
            /** @var array<string, mixed> $config */
            $config = $app['config']['ebay'];

            return new EbayTokenManager(
                $app->make(EbayOAuthClient::class),
                $config,
            );
        });

        $this->app->singleton(EbayIdentityService::class, function ($app): EbayIdentityService {
            /** @var array<string, mixed> $config */
            $config = $app['config']['ebay'];
            $environment = (string) ($config['environment'] ?? 'sandbox');

            return new EbayIdentityService($config, $environment);
        });

        $this->app->singleton(EbayHttpClient::class, function ($app): EbayHttpClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']['ebay'];
            $environment = (string) ($config['environment'] ?? 'sandbox');

            return new EbayHttpClient(
                $app->make(EbayTokenManager::class),
                $config,
                $environment,
            );
        });

        $this->app->when(EbayOAuthController::class)
            ->needs('$config')
            ->giveConfig('ebay');

        $this->app->when(AccountDeletionController::class)
            ->needs('$config')
            ->giveConfig('ebay');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ebay.php' => config_path('ebay.php'),
        ], 'ebay-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'ebay-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RefreshEbayTokensCommand::class,
            ]);
        }
    }
}
