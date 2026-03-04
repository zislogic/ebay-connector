<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Zislogic\Ebay\Connector\Auth\EbayOAuthClient;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;
use Zislogic\Ebay\Connector\Models\EbayCredential;
use Zislogic\Ebay\Connector\Services\EbayIdentityService;
use Zislogic\Ebay\Connector\Services\EbayTokenManager;

final class EbayOAuthController extends Controller
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly EbayOAuthClient $oauthClient,
        private readonly EbayTokenManager $tokenManager,
        private readonly array $config,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        $state = $request->session()->token();
        $request->session()->put('ebay_oauth_state', $state);

        $authUrl = $this->oauthClient->getAuthorizationUrl($state);

        return redirect($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $state = $request->input('state');
            $sessionState = $request->session()->pull('ebay_oauth_state');

            if (! is_string($state) || $state !== $sessionState) {
                throw EbayAuthException::invalidResponse('Invalid state parameter');
            }

            $code = $request->input('code');

            if (! is_string($code) || $code === '') {
                throw EbayAuthException::invalidResponse('Missing authorization code');
            }

            $environment = $request->session()->pull(
                'ebay_oauth_environment',
                (string) ($this->config['environment'] ?? 'sandbox'),
            );

            $this->processAuthorizationCode($code, (string) $environment);

            $successRedirect = (string) $request->session()->pull(
                'ebay_oauth_success_redirect',
                (string) ($this->config['routes']['success_redirect'] ?? '/dashboard'),
            );

            return redirect($successRedirect)
                ->with('success', 'eBay account connected successfully');
        } catch (\Throwable $e) {
            Log::error('eBay OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            $errorRedirect = (string) ($request->session()->pull(
                'ebay_oauth_error_redirect',
                (string) ($this->config['routes']['error_redirect'] ?? '/dashboard'),
            ));

            return redirect($errorRedirect)
                ->with('error', 'Failed to connect eBay account: ' . $e->getMessage());
        }
    }

    public function exchange(Request $request): RedirectResponse
    {
        $request->validate([
            'callback_url' => 'required|url',
            'environment' => 'sometimes|string|in:sandbox,production',
        ]);

        try {
            $callbackUrl = (string) $request->input('callback_url');
            $parsedUrl = parse_url($callbackUrl);

            $queryString = '';
            if (is_array($parsedUrl) && isset($parsedUrl['query'])) {
                $queryString = (string) $parsedUrl['query'];
            }

            parse_str($queryString, $queryParams);

            $code = $queryParams['code'] ?? null;

            if (! is_string($code) || $code === '') {
                throw EbayAuthException::invalidResponse('No authorization code found in URL');
            }

            $environment = (string) ($request->input('environment') ?? $this->config['environment'] ?? 'sandbox');

            $this->processAuthorizationCode($code, $environment);

            $successRedirect = (string) ($this->config['routes']['success_redirect'] ?? '/dashboard');

            return redirect($successRedirect)
                ->with('success', 'eBay account connected successfully');
        } catch (\Throwable $e) {
            Log::error('eBay OAuth exchange failed', [
                'error' => $e->getMessage(),
            ]);

            $errorRedirect = (string) ($this->config['routes']['error_redirect'] ?? '/dashboard');

            return redirect($errorRedirect)
                ->with('error', 'Failed to connect eBay account: ' . $e->getMessage());
        }
    }

    private function processAuthorizationCode(string $code, ?string $environment = null): EbayCredential
    {
        $environment = $environment ?? (string) ($this->config['environment'] ?? 'sandbox');

        $oauthClient = new EbayOAuthClient($this->config, $environment);
        $identityService = new EbayIdentityService($this->config, $environment);

        $tokenResponse = $oauthClient->exchangeCodeForTokens($code);

        $userData = $identityService->getUser($tokenResponse->accessToken);

        /** @var string $ebayUserId */
        $ebayUserId = $userData['userId'];

        /** @var EbayCredential $credential */
        $credential = EbayCredential::query()->firstOrNew([
            'ebay_user_id' => $ebayUserId,
            'environment' => $environment,
        ]);

        $credential->name = isset($userData['username']) ? (string) $userData['username'] : null;
        $credential->refresh_token = $tokenResponse->refreshToken;
        $credential->is_active = true;

        if ($tokenResponse->refreshTokenExpiresIn !== null) {
            $credential->refresh_token_expires_at = now()->addSeconds($tokenResponse->refreshTokenExpiresIn);
        }

        $credential->save();

        $this->tokenManager->cacheAccessToken(
            $credential->id,
            $tokenResponse->accessToken,
            $tokenResponse->expiresIn,
        );

        return $credential;
    }
}
