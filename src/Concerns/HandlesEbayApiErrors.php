<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Shared error handling for eBay API commands.
 *
 * Provides retry logic for common eBay API errors:
 * - HTTP 401: Token expired → clear cache, retry once
 * - HTTP 429: Rate limited → wait 60s, retry once
 * - HTTP 4xx/5xx: Log and fail
 *
 * Must be used in a Laravel Console Command (relies on $this->warn(), $this->error()).
 */
trait HandlesEbayApiErrors
{
    /**
     * Execute an eBay API call with automatic retry for auth and rate limit errors.
     *
     * @template T
     *
     * @param  callable(): T  $apiCall
     * @return T
     */
    protected function callWithRetry(callable $apiCall): mixed
    {
        try {
            return $apiCall();
        } catch (\Throwable $e) {
            $code = (int) $e->getCode();

            return match (true) {
                $code === 401 => $this->retryAfterTokenRefresh($apiCall),
                $code === 429 => $this->retryAfterRateLimit($apiCall),
                $code >= 400 => $this->failWithApiError($e),
                default => throw $e,
            };
        }
    }

    private function retryAfterTokenRefresh(callable $apiCall): mixed
    {
        $this->warn('HTTP 401 — refreshing token and retrying...');

        /** @var string $environment */
        $environment = config('ebay.environment', 'sandbox');
        Cache::forget("ebay.application_token.{$environment}");

        try {
            return $apiCall();
        } catch (\Throwable $e) {
            $this->failWithApiError($e);
        }
    }

    private function retryAfterRateLimit(callable $apiCall): mixed
    {
        $this->warn('HTTP 429 — rate limited, waiting 60 seconds...');
        sleep(60);

        try {
            return $apiCall();
        } catch (\Throwable $e) {
            $this->failWithApiError($e);
        }
    }

    private function failWithApiError(\Throwable $e): never
    {
        $this->error("eBay API error [{$e->getCode()}]: {$e->getMessage()}");

        if (method_exists($e, 'getResponseBody')) {
            /** @var string|null $body */
            $body = $e->getResponseBody();
            if ($body !== null && $body !== '') {
                $this->line('');
                $this->line($body);
            }
        }

        exit(self::FAILURE);
    }
}
