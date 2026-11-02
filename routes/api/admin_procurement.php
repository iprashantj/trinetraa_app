<?php

use App\Http\Controllers\Api\Admin\PurchaseOrderController;
use App\Http\Controllers\Api\Admin\StockTakeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    Route::middleware('admin.perm:inventory')->group(function () {
        // ─── Purchase orders ─────────────────────────────────────────────
        Route::get('admin/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('admin/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::get('admin/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->whereNumber('id');
        Route::put('admin/purchase-orders/{id}', [PurchaseOrderController::class, 'update'])->whereNumber('id');
        Route::delete('admin/purchase-orders/{id}', [PurchaseOrderController::class, 'destroy'])->whereNumber('id');
        Route::post('admin/purchase-orders/{id}/place', [PurchaseOrderController::class, 'place'])->whereNumber('id');
        Route::post('admin/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])->whereNumber('id');
        Route::post('admin/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->whereNumber('id');
        Route::get('admin/purchase-orders/{id}/csv', [PurchaseOrderController::class, 'csv'])->whereNumber('id');

        // ─── Stock take ──────────────────────────────────────────────────
        Route::get('admin/stock-takes', [StockTakeController::class, 'index']);
        Route::post('admin/stock-takes', [StockTakeController::class, 'store']);
        Route::get('admin/stock-takes/{id}', [StockTakeController::class, 'show'])->whereNumber('id');
        Route::put('admin/stock-takes/{id}/items/{itemId}', [StockTakeController::class, 'setCount'])->whereNumber(['id', 'itemId']);
        Route::post('admin/stock-takes/{id}/scan', [StockTakeController::class, 'scan'])->whereNumber('id');
        Route::post('admin/stock-takes/{id}/complete', [StockTakeController::class, 'complete'])->whereNumber('id');
        Route::post('admin/stock-takes/{id}/cancel', [StockTakeController::class, 'cancel'])->whereNumber('id');
    });
});
