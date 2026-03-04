<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zislogic\Ebay\Connector\Http\Controllers\AccountDeletionController;
use Zislogic\Ebay\Connector\Http\Controllers\EbayCredentialController;
use Zislogic\Ebay\Connector\Http\Controllers\EbayOAuthController;

Route::prefix('ebay')->name('ebay.')->group(function (): void {
    Route::middleware(['web', 'auth'])->group(function (): void {
        Route::get('oauth/redirect', [EbayOAuthController::class, 'redirect'])
            ->name('oauth.redirect');
        Route::post('oauth/exchange', [EbayOAuthController::class, 'exchange'])
            ->name('oauth.exchange');

        Route::get('credentials', [EbayCredentialController::class, 'index'])
            ->name('credentials.index');
        Route::get('credentials/token', [EbayCredentialController::class, 'token'])
            ->name('credentials.token');
        Route::post('credentials/token', [EbayCredentialController::class, 'tokenRedirect'])
            ->name('credentials.tokenRedirect');
        Route::get('credentials/code', [EbayCredentialController::class, 'code'])
            ->name('credentials.code');
        Route::post('credentials/code', [EbayCredentialController::class, 'codeExchange'])
            ->name('credentials.codeExchange');
        Route::get('credentials/{credential}', [EbayCredentialController::class, 'show'])
            ->name('credentials.show');
        Route::get('credentials/{credential}/edit', [EbayCredentialController::class, 'edit'])
            ->name('credentials.edit');
        Route::put('credentials/{credential}', [EbayCredentialController::class, 'update'])
            ->name('credentials.update');
        Route::delete('credentials/{credential}', [EbayCredentialController::class, 'destroy'])
            ->name('credentials.destroy');
    });

    Route::middleware('web')
        ->get('oauth/callback', [EbayOAuthController::class, 'callback'])
        ->name('oauth.callback');

    Route::match(['get', 'post'], 'account-deletion', AccountDeletionController::class)
        ->name('account-deletion');
});
