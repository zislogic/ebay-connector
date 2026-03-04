<?php

declare(strict_types=1);

return [
    'environment' => env('EBAY_ENVIRONMENT', 'sandbox'),

    'code_exchange_method' => env('EBAY_CODE_EXCHANGE_METHOD', 'manual'),

    'credentials' => [
        'sandbox' => [
            'client_id' => env('EBAY_SANDBOX_CLIENT_ID', env('EBAY_CLIENT_ID')),
            'client_secret' => env('EBAY_SANDBOX_CLIENT_SECRET', env('EBAY_CLIENT_SECRET')),
            'redirect_uri' => env('EBAY_SANDBOX_REDIRECT_URI', env('EBAY_REDIRECT_URI')),
        ],
        'production' => [
            'client_id' => env('EBAY_PRODUCTION_CLIENT_ID', env('EBAY_CLIENT_ID')),
            'client_secret' => env('EBAY_PRODUCTION_CLIENT_SECRET', env('EBAY_CLIENT_SECRET')),
            'redirect_uri' => env('EBAY_PRODUCTION_REDIRECT_URI', env('EBAY_REDIRECT_URI')),
        ],
    ],

    'scopes' => [
        'sandbox' => [
            'https://api.ebay.com/oauth/api_scope',
            'https://api.ebay.com/oauth/api_scope/sell.marketing.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.marketing',
            'https://api.ebay.com/oauth/api_scope/sell.inventory.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.inventory',
            'https://api.ebay.com/oauth/api_scope/sell.account.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.account',
            'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
            'https://api.ebay.com/oauth/api_scope/sell.analytics.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.finances',
            'https://api.ebay.com/oauth/api_scope/sell.payment.dispute',
            'https://api.ebay.com/oauth/api_scope/commerce.identity.readonly',
            'https://api.ebay.com/oauth/api_scope/commerce.notification.subscription',
            'https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly',
        ],
        'production' => [
            'https://api.ebay.com/oauth/api_scope',
            'https://api.ebay.com/oauth/api_scope/sell.marketing.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.marketing',
            'https://api.ebay.com/oauth/api_scope/sell.inventory.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.inventory',
            'https://api.ebay.com/oauth/api_scope/sell.account.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.account',
            'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
            'https://api.ebay.com/oauth/api_scope/sell.analytics.readonly',
            'https://api.ebay.com/oauth/api_scope/sell.finances',
            'https://api.ebay.com/oauth/api_scope/sell.payment.dispute',
            'https://api.ebay.com/oauth/api_scope/commerce.identity.readonly',
            'https://api.ebay.com/oauth/api_scope/commerce.notification.subscription',
            'https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly',
        ],
    ],

    'urls' => [
        'sandbox' => [
            'auth' => 'https://auth.sandbox.ebay.com/oauth2/authorize',
            'token' => 'https://api.sandbox.ebay.com/identity/v1/oauth2/token',
            'api' => 'https://api.sandbox.ebay.com',
            'apiz' => 'https://apiz.sandbox.ebay.com',
        ],
        'production' => [
            'auth' => 'https://auth.ebay.com/oauth2/authorize',
            'token' => 'https://api.ebay.com/identity/v1/oauth2/token',
            'api' => 'https://api.ebay.com',
            'apiz' => 'https://apiz.ebay.com',
        ],
    ],

    'deletion_notification' => [
        'verification_token' => env('EBAY_DELETION_VERIFICATION_TOKEN'),
        'endpoint_url' => env('EBAY_DELETION_ENDPOINT_URL', env('APP_URL', 'http://localhost') . '/ebay/account-deletion'),
    ],

    'cache' => [
        'prefix' => 'ebay',
        'ttl_buffer' => 300,
    ],

    'routes' => [
        'success_redirect' => env('EBAY_SUCCESS_REDIRECT', '/dashboard'),
        'error_redirect' => env('EBAY_ERROR_REDIRECT', '/dashboard'),
    ],
];
