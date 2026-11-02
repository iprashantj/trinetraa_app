<?php

use App\Http\Controllers\Api\Admin\CouponController;
use App\Http\Controllers\Api\Admin\FinanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // Coupons: staff with billing access can check them at the POS; creating/editing them is a settings-level action.
    Route::middleware('admin.perm:billing')->group(function () {
        Route::post('admin/coupons/check', [CouponController::class, 'check']);
        Route::post('admin/loyalty-check', [CouponController::class, 'loyalty']);
    });
    Route::middleware('admin.perm:settings')->group(function () {
        Route::get('admin/coupons', [CouponController::class, 'index']);
        Route::post('admin/coupons', [CouponController::class, 'store']);
        Route::put('admin/coupons/{id}', [CouponController::class, 'update']);
        Route::delete('admin/coupons/{id}', [CouponController::class, 'destroy']);
    });
    Route::middleware('admin.perm:reports')->group(function () {
        Route::get('admin/reports/gst', [FinanceReportController::class, 'gst']);
        Route::get('admin/reports/profit', [FinanceReportController::class, 'profit']);
    });
});
