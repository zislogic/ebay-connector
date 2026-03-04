<?php

declare(strict_types=1);

namespace Zislogic\Ebay\Connector\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Zislogic\Ebay\Connector\Auth\EbayOAuthClient;
use Zislogic\Ebay\Connector\Exceptions\EbayAuthException;
use Zislogic\Ebay\Connector\Models\EbayCredential;
use Zislogic\Ebay\Connector\Services\EbayIdentityService;
use Zislogic\Ebay\Connector\Services\EbayTokenManager;

final class EbayCredentialController extends Controller
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly EbayTokenManager $tokenManager,
        private readonly array $config,
    ) {}

    public function index(): Response
    {
        $credentials = EbayCredential::query()
            ->orderByDesc('id')
            ->get()
            ->map(fn (EbayCredential $credential): array => $this->formatCredential($credential));

        return Inertia::render('Ebay/Credentials/Index', [
            'credentials' => $credentials,
            'codeExchangeMethod' => $this->getCodeExchangeMethod(),
        ]);
    }

    public function show(EbayCredential $credential): Response
    {
        return Inertia::render('Ebay/Credentials/Show', [
            'credential' => $this->formatCredential($credential),
        ]);
    }

    public function edit(EbayCredential $credential): Response
    {
        return Inertia::render('Ebay/Credentials/Edit', [
            'credential' => $this->formatCredential($credential),
        ]);
    }

    public function update(Request $request, EbayCredential $credential): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $credential->name = $validated['name'] ?? null;
        $credential->is_active = (bool) $validated['is_active'];
        $credential->save();

        return redirect()
            ->route('ebay.credentials.show', $credential)
            ->with('success', 'Credential updated successfully');
    }

    public function destroy(EbayCredential $credential): RedirectResponse
    {
        $credential->delete();

        return redirect()
            ->route('ebay.credentials.index')
            ->with('success', 'Credential deleted successfully');
    }

    public function token(): Response
    {
        return Inertia::render('Ebay/Credentials/Token', [
            'codeExchangeMethod' => $this->getCodeExchangeMethod(),
        ]);
    }

    public function tokenRedirect(Request $request): Response|\Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'environment' => 'required|string|in:sandbox,production',
        ]);

        $environment = (string) $request->input('environment');

        $request->session()->put('ebay_oauth_environment', $environment);
        $request->session()->put('ebay_oauth_success_redirect', route('ebay.credentials.index'));
        $request->session()->put('ebay_oauth_error_redirect', route('ebay.credentials.token'));

        $oauthClient = new EbayOAuthClient($this->config, $environment);

        $state = $request->session()->token();
        $request->session()->put('ebay_oauth_state', $state);

        $authUrl = $oauthClient->getAuthorizationUrl($state);

        if ($this->getCodeExchangeMethod() === 'manual') {
            return Inertia::render('Ebay/Credentials/Token', [
                'codeExchangeMethod' => 'manual',
                'authUrl' => $authUrl,
                'selectedEnvironment' => $environment,
            ]);
        }

        return Inertia::location($authUrl);
    }

    public function code(): Response
    {
        return Inertia::render('Ebay/Credentials/Code');
    }

    public function codeExchange(Request $request): RedirectResponse
    {
        $request->validate([
            'environment' => 'required|string|in:sandbox,production',
            'callback_url' => 'required|string',
        ]);

        try {
            $environment = (string) $request->input('environment');
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

            return redirect()
                ->route('ebay.credentials.index')
                ->with('success', 'eBay account connected successfully');
        } catch (\Throwable $e) {
            Log::error('eBay code exchange failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('ebay.credentials.code')
                ->with('error', 'Failed to connect eBay account: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCredential(EbayCredential $credential): array
    {
        return [
            'id' => $credential->id,
            'name' => $credential->name,
            'environment' => $credential->environment,
            'ebay_user_id' => $credential->ebay_user_id,
            'is_active' => $credential->is_active,
            'has_refresh_token' => $credential->refresh_token !== null,
            'refresh_token_expires_at' => $credential->refresh_token_expires_at?->toIso8601String(),
            'is_refresh_token_expired' => $credential->isRefreshTokenExpired(),
            'created_at' => $credential->created_at?->toIso8601String(),
            'updated_at' => $credential->updated_at?->toIso8601String(),
        ];
    }

    private function getCodeExchangeMethod(): string
    {
        return (string) ($this->config['code_exchange_method'] ?? 'manual');
    }
}
