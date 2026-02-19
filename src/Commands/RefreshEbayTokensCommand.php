<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Zislogic\Ebay\Connector\Exceptions\TokenRefreshException;
use Zislogic\Ebay\Connector\Models\EbayCredential;
use Zislogic\Ebay\Connector\Services\EbayTokenManager;

final class RefreshEbayTokensCommand extends Command
{
    /** @var string */
    protected $signature = 'ebay:refresh-tokens';

    /** @var string */
    protected $description = 'Refresh all active eBay access tokens';

    public function handle(EbayTokenManager $tokenManager): int
    {
        $credentials = EbayCredential::query()
            ->active()
            ->where(function ($query): void {
                $query->whereNull('refresh_token_expires_at')
                    ->orWhere('refresh_token_expires_at', '>', now());
            })
            ->get();

        if ($credentials->isEmpty()) {
            $this->info('No active eBay credentials to refresh.');

            return self::SUCCESS;
        }

        $this->info("Refreshing tokens for {$credentials->count()} credential(s)...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($credentials as $credential) {
            /** @var EbayCredential $credential */
            try {
                $tokenManager->getSellerAccessToken($credential->id);
                $successCount++;
                $this->info("Refreshed token for credential #{$credential->id} ({$credential->ebay_user_id})");

                sleep(random_int(1, 5));
            } catch (TokenRefreshException $e) {
                $failureCount++;
                $this->error("Failed to refresh credential #{$credential->id}: {$e->getMessage()}");

                Log::error('eBay token refresh command failed', [
                    'credential_id' => $credential->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Refresh complete: {$successCount} succeeded, {$failureCount} failed.");

        return $failureCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
