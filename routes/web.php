<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zislogic\Ebay\Connector\Http\Controllers\AccountDeletionController;
use Zislogic\Ebay\Connector\Http\Controllers\EbayOAuthController;

Route::prefix('ebay')->name('ebay.')->group(function (): void {
    Route::middleware(['web', 'auth'])->group(function (): void {
        Route::get('oauth/redirect', [EbayOAuthController::class, 'redirect'])
            ->name('oauth.redirect');
        Route::post('oauth/exchange', [EbayOAuthController::class, 'exchange'])
            ->name('oauth.exchange');
    });

    Route::middleware('web')
        ->get('oauth/callback', [EbayOAuthController::class, 'callback'])
        ->name('oauth.callback');

    Route::match(['get', 'post'], 'account-deletion', AccountDeletionController::class)
        ->name('account-deletion');
});
