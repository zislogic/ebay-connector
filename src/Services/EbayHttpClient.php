<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class EbayHttpClient
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly EbayTokenManager $tokenManager,
        private readonly array $config,
        private readonly string $environment,
    ) {}

    public function forSeller(int $credentialId): PendingRequest
    {
        $accessToken = $this->tokenManager->getSellerAccessToken($credentialId);

        return $this->baseRequest()
            ->withToken($accessToken)
            ->baseUrl($this->getApiBaseUrl());
    }

    public function forApplication(): PendingRequest
    {
        $accessToken = $this->tokenManager->getApplicationAccessToken();

        return $this->baseRequest()
            ->withToken($accessToken)
            ->baseUrl($this->getApiBaseUrl());
    }

    private function baseRequest(): PendingRequest
    {
        $options = [];

        if (! ($this->config['verify_ssl'] ?? true)) {
            $options['verify'] = false;
        }

        $proxy = self::resolveProxy($this->config);
        if ($proxy !== null) {
            $options['proxy'] = $proxy;
        }

        return Http::withOptions($options);
    }

    /**
     * Resolve proxy from config, falling back to HTTPS_PROXY / HTTP_PROXY env vars.
     *
     * @param  array<string, mixed>  $config
     */
    public static function resolveProxy(array $config): ?string
    {
        $configured = $config['proxy'] ?? null;
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        // Fall back to standard CLI proxy env vars (used by Charles, mitmproxy, etc.)
        $envProxy = getenv('HTTPS_PROXY') ?: getenv('HTTP_PROXY') ?: null;

        return is_string($envProxy) ? $envProxy : null;
    }

    public function getSellerAccessToken(int $credentialId): string
    {
        return $this->tokenManager->getSellerAccessToken($credentialId);
    }

    public function getApplicationAccessToken(): string
    {
        return $this->tokenManager->getApplicationAccessToken();
    }

    private function getApiBaseUrl(): string
    {
        return (string) $this->config['urls'][$this->environment]['api'];
    }
}
