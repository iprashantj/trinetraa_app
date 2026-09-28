<?php

use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\EyeCheckupBillController;
use App\Http\Controllers\Api\Admin\FrameBillController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\StockItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Stock Items ─────────────────────────────────────────────────────────
    Route::get('admin/stock-items/lookup', [StockItemController::class, 'lookup']);
    Route::get('admin/stock-items', [StockItemController::class, 'index']);
    Route::post('admin/stock-items', [StockItemController::class, 'store']);
    Route::put('admin/stock-items/{id}', [StockItemController::class, 'update']);
    Route::delete('admin/stock-items/{id}', [StockItemController::class, 'destroy']);

    // ─── Customers ───────────────────────────────────────────────────────────
    Route::get('admin/customers/lookup', [CustomerController::class, 'lookup']);
    Route::get('admin/customers', [CustomerController::class, 'index']);
    Route::post('admin/customers', [CustomerController::class, 'store']);
    Route::put('admin/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('admin/customers/{id}', [CustomerController::class, 'destroy']);

    // ─── Orders ──────────────────────────────────────────────────────────────
    Route::get('admin/orders', [OrderController::class, 'index']);
    Route::post('admin/orders', [OrderController::class, 'store']);
    Route::put('admin/orders/{id}', [OrderController::class, 'update']);
    Route::delete('admin/orders/{id}', [OrderController::class, 'destroy']);

    // ─── Frame Bills ─────────────────────────────────────────────────────────
    Route::get('admin/frame-bills', [FrameBillController::class, 'index']);
    Route::post('admin/frame-bills', [FrameBillController::class, 'store']);
    Route::put('admin/frame-bills/{id}', [FrameBillController::class, 'update']);
    Route::delete('admin/frame-bills/{id}', [FrameBillController::class, 'destroy']);

    // ─── Eye Checkup Bills ───────────────────────────────────────────────────
    Route::get('admin/eye-checkup-bills', [EyeCheckupBillController::class, 'index']);
    Route::post('admin/eye-checkup-bills', [EyeCheckupBillController::class, 'store']);
    Route::put('admin/eye-checkup-bills/{id}', [EyeCheckupBillController::class, 'update']);
    Route::delete('admin/eye-checkup-bills/{id}', [EyeCheckupBillController::class, 'destroy']);
});
