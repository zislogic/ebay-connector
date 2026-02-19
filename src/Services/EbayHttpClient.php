<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class EbayHttpClient
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly EbayTokenManager $tokenManager,
        private readonly array $config,
        private readonly string $environment,
    ) {}

    public function forSeller(int $credentialId): PendingRequest
    {
        $accessToken = $this->tokenManager->getSellerAccessToken($credentialId);

        return Http::withToken($accessToken)
            ->baseUrl($this->getApiBaseUrl());
    }

    public function forApplication(): PendingRequest
    {
        $accessToken = $this->tokenManager->getApplicationAccessToken();

        return Http::withToken($accessToken)
            ->baseUrl($this->getApiBaseUrl());
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
