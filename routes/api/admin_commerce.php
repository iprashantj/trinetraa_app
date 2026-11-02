<?php

use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\EyeCheckupBillController;
use App\Http\Controllers\Api\Admin\FrameBillController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\StockItemController;
use Illuminate\Support\Facades\Route;

// Each module is gated by a named permission (owners always pass; accounts without a
// permission list stay unrestricted — see App\Support\Access).
Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Stock Items ─────────────────────────────────────────────────────────
    Route::middleware('admin.perm:inventory')->group(function () {
        Route::get('admin/stock-items/lookup', [StockItemController::class, 'lookup']);
        Route::get('admin/stock-items/scan', [StockItemController::class, 'scan']);
        Route::get('admin/stock-items/{id}/units', [StockItemController::class, 'units']);
        Route::get('admin/stock-items', [StockItemController::class, 'index']);
        Route::post('admin/stock-items', [StockItemController::class, 'store']);
        Route::get('admin/stock-items/{id}', [StockItemController::class, 'show']);
        Route::put('admin/stock-items/{id}', [StockItemController::class, 'update']);
        Route::delete('admin/stock-items/{id}', [StockItemController::class, 'destroy']);
    });

    // ─── Customers ───────────────────────────────────────────────────────────
    Route::middleware('admin.perm:customers')->group(function () {
        Route::get('admin/customers/lookup', [CustomerController::class, 'lookup']);
        Route::get('admin/customers', [CustomerController::class, 'index']);
        Route::post('admin/customers', [CustomerController::class, 'store']);
        Route::put('admin/customers/{id}', [CustomerController::class, 'update']);
        Route::delete('admin/customers/{id}', [CustomerController::class, 'destroy']);
    });

    // ─── Orders, Frame Bills, Eye Checkup Bills ──────────────────────────────
    Route::middleware('admin.perm:billing')->group(function () {
        Route::get('admin/orders', [OrderController::class, 'index']);
        Route::post('admin/orders', [OrderController::class, 'store']);
        Route::put('admin/orders/{id}', [OrderController::class, 'update']);
        Route::delete('admin/orders/{id}', [OrderController::class, 'destroy']);

        Route::get('admin/frame-bills', [FrameBillController::class, 'index']);
        Route::post('admin/frame-bills', [FrameBillController::class, 'store']);
        Route::put('admin/frame-bills/{id}', [FrameBillController::class, 'update']);
        Route::delete('admin/frame-bills/{id}', [FrameBillController::class, 'destroy']);

        Route::get('admin/eye-checkup-bills', [EyeCheckupBillController::class, 'index']);
        Route::post('admin/eye-checkup-bills', [EyeCheckupBillController::class, 'store']);
        Route::put('admin/eye-checkup-bills/{id}', [EyeCheckupBillController::class, 'update']);
        Route::delete('admin/eye-checkup-bills/{id}', [EyeCheckupBillController::class, 'destroy']);
    });
});
