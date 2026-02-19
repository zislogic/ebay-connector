<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Auth;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;

final class EbayOAuthClient
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly array $config,
        private readonly string $environment,
    ) {}

    public function getAuthorizationUrl(?string $state = null): string
    {
        $params = [
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', $this->getScopes()),
        ];

        if ($state !== null) {
            $params['state'] = $state;
        }

        return $this->getAuthUrl() . '?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code): TokenResponse
    {
        $response = $this->createTokenRequest()
            ->asForm()
            ->post($this->getTokenUrl(), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->getRedirectUri(),
            ]);

        if ($response->failed()) {
            throw EbayAuthException::tokenExchangeFailed(
                $response->json('error_description') ?? $response->body()
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        return TokenResponse::fromArray($json);
    }

    public function refreshAccessToken(string $refreshToken): TokenResponse
    {
        $response = $this->createTokenRequest()
            ->asForm()
            ->post($this->getTokenUrl(), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if ($response->failed()) {
            throw EbayAuthException::httpError(
                $response->status(),
                $response->json('error_description') ?? $response->body()
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        return TokenResponse::fromArray($json);
    }

    public function getApplicationToken(): TokenResponse
    {
        $response = $this->createTokenRequest()
            ->asForm()
            ->post($this->getTokenUrl(), [
                'grant_type' => 'client_credentials',
                'scope' => implode(' ', $this->getScopes()),
            ]);

        if ($response->failed()) {
            throw EbayAuthException::httpError(
                $response->status(),
                $response->json('error_description') ?? $response->body()
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json();

        return TokenResponse::fromArray($json);
    }

    private function createTokenRequest(): PendingRequest
    {
        $credentials = base64_encode($this->getClientId() . ':' . $this->getClientSecret());

        return Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ]);
    }

    private function getAuthUrl(): string
    {
        return (string) $this->config['urls'][$this->environment]['auth'];
    }

    private function getTokenUrl(): string
    {
        return (string) $this->config['urls'][$this->environment]['token'];
    }

    private function getClientId(): string
    {
        $clientId = $this->config['credentials'][$this->environment]['client_id'] ?? null;

        if ($clientId === null || $clientId === '') {
            throw EbayAuthException::missingConfiguration("credentials.{$this->environment}.client_id");
        }

        return (string) $clientId;
    }

    private function getClientSecret(): string
    {
        $clientSecret = $this->config['credentials'][$this->environment]['client_secret'] ?? null;

        if ($clientSecret === null || $clientSecret === '') {
            throw EbayAuthException::missingConfiguration("credentials.{$this->environment}.client_secret");
        }

        return (string) $clientSecret;
    }

    private function getRedirectUri(): string
    {
        $redirectUri = $this->config['credentials'][$this->environment]['redirect_uri'] ?? null;

        if ($redirectUri === null || $redirectUri === '') {
            throw EbayAuthException::missingConfiguration("credentials.{$this->environment}.redirect_uri");
        }

        return (string) $redirectUri;
    }

    /**
     * @return array<int, string>
     */
    private function getScopes(): array
    {
        /** @var array<int, string> */
        return $this->config['scopes'][$this->environment] ?? [];
    }
}
