<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Zislogic\Ebay\Connector\Auth\EbayOAuthClient;
use Zislogic\Ebay\Connector\Exceptions\TokenRefreshException;
use Zislogic\Ebay\Connector\Models\EbayCredential;

final class EbayTokenManager
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly EbayOAuthClient $oauthClient,
        private readonly array $config,
    ) {}

    public function getSellerAccessToken(int $credentialId): string
    {
        $cacheKey = $this->getSellerCacheKey($credentialId);

        $token = Cache::get($cacheKey);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        return $this->refreshSellerToken($credentialId);
    }

    public function getApplicationAccessToken(): string
    {
        $cacheKey = $this->getApplicationCacheKey();

        $token = Cache::get($cacheKey);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        $tokenResponse = $this->oauthClient->getApplicationToken();
        $this->cacheApplicationToken($tokenResponse->accessToken, $tokenResponse->expiresIn);

        return $tokenResponse->accessToken;
    }

    public function cacheAccessToken(int $credentialId, string $accessToken, int $expiresIn): void
    {
        $cacheKey = $this->getSellerCacheKey($credentialId);
        $ttl = $this->calculateCacheTtl($expiresIn);

        Cache::put($cacheKey, $accessToken, $ttl);
    }

    public function cacheApplicationToken(string $accessToken, int $expiresIn): void
    {
        $cacheKey = $this->getApplicationCacheKey();
        $ttl = $this->calculateCacheTtl($expiresIn);

        Cache::put($cacheKey, $accessToken, $ttl);
    }

    private function refreshSellerToken(int $credentialId): string
    {
        $credential = EbayCredential::query()->find($credentialId);

        if (! $credential instanceof EbayCredential) {
            throw TokenRefreshException::credentialNotFound($credentialId);
        }

        if (! $credential->is_active) {
            throw TokenRefreshException::credentialInactive($credentialId);
        }

        if ($credential->isRefreshTokenExpired()) {
            $credential->deactivate();

            throw TokenRefreshException::refreshTokenExpired($credentialId);
        }

        try {
            $refreshToken = $credential->refresh_token;

            if ($refreshToken === null) {
                throw TokenRefreshException::refreshFailed($credentialId, 'No refresh token available');
            }

            $tokenResponse = $this->oauthClient->refreshAccessToken($refreshToken);

            if ($tokenResponse->refreshToken !== null) {
                $credential->refresh_token = $tokenResponse->refreshToken;

                if ($tokenResponse->refreshTokenExpiresIn !== null) {
                    $credential->refresh_token_expires_at = now()->addSeconds($tokenResponse->refreshTokenExpiresIn);
                }

                $credential->save();
            }

            $this->cacheAccessToken($credentialId, $tokenResponse->accessToken, $tokenResponse->expiresIn);

            return $tokenResponse->accessToken;
        } catch (TokenRefreshException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $credential->deactivate();

            Log::error('eBay token refresh failed', [
                'credential_id' => $credentialId,
                'error' => $e->getMessage(),
            ]);

            throw TokenRefreshException::refreshFailed($credentialId, $e->getMessage());
        }
    }

    private function calculateCacheTtl(int $expiresIn): int
    {
        $buffer = (int) ($this->config['cache']['ttl_buffer'] ?? 300);

        return max(0, $expiresIn - $buffer);
    }

    private function getSellerCacheKey(int $credentialId): string
    {
        $prefix = (string) ($this->config['cache']['prefix'] ?? 'ebay');

        return "{$prefix}.access_token.{$credentialId}";
    }

    private function getApplicationCacheKey(): string
    {
        $prefix = (string) ($this->config['cache']['prefix'] ?? 'ebay');
        $environment = (string) ($this->config['environment'] ?? 'sandbox');

        return "{$prefix}.application_token.{$environment}";
    }
}
