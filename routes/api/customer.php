<?php

use App\Http\Controllers\Api\Customer\RecentlyViewedController;
use App\Http\Controllers\Api\Customer\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('customer.auth')->group(function () {
    // ─── Wishlist ──────────────────────────────────────────────────────────--
    Route::get('customer/wishlist', [WishlistController::class, 'index']);
    Route::post('customer/wishlist', [WishlistController::class, 'store']);
    // check/{eyewearId} must come before {eyewearId} so it isn't shadowed.
    Route::get('customer/wishlist/check/{eyewearId}', [WishlistController::class, 'check']);
    Route::delete('customer/wishlist/{eyewearId}', [WishlistController::class, 'destroy']);

    // ─── Recently viewed ───────────────────────────────────────────────────--
    Route::get('customer/recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('customer/recently-viewed', [RecentlyViewedController::class, 'store']);
});
