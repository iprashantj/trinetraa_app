<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\Admin\BillingController;
use App\Http\Controllers\Api\Admin\BranchController;
use App\Http\Controllers\Api\Admin\EyeTestController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\StockInwardController;
use App\Http\Controllers\Api\Admin\StockTransferController;
use App\Http\Controllers\Api\Admin\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Branches (read for pickers by everyone; manage = settings permission) ──
    Route::get('admin/branches', [BranchController::class, 'index']);
    Route::middleware('admin.perm:settings')->group(function () {
        Route::post('admin/branches', [BranchController::class, 'store']);
        Route::put('admin/branches/{id}', [BranchController::class, 'update']);
        Route::delete('admin/branches/{id}', [BranchController::class, 'destroy']);
    });

    // ─── Suppliers, inward, corrections, ledger, alert rules ─────────────────
    Route::middleware('admin.perm:inventory')->group(function () {
        Route::get('admin/suppliers', [SupplierController::class, 'index']);
        Route::post('admin/suppliers', [SupplierController::class, 'store']);
        Route::put('admin/suppliers/{id}', [SupplierController::class, 'update']);
        Route::delete('admin/suppliers/{id}', [SupplierController::class, 'destroy']);

        Route::get('admin/stock-inwards', [StockInwardController::class, 'index']);
        Route::post('admin/stock-inwards', [StockInwardController::class, 'store']);
        Route::post('admin/stock-adjustments', [StockInwardController::class, 'adjust']);
        Route::get('admin/stock-movements', [StockInwardController::class, 'movements']);
        Route::get('admin/stock-alert-rules', [StockInwardController::class, 'rules']);
        Route::post('admin/stock-alert-rules', [StockInwardController::class, 'storeRule']);
        Route::delete('admin/stock-alert-rules/{id}', [StockInwardController::class, 'destroyRule']);
    });
    Route::get('admin/stock-alerts', [StockInwardController::class, 'alerts']);

    // ─── Transfers between branches ──────────────────────────────────────────
    Route::middleware('admin.perm:transfers')->group(function () {
        Route::get('admin/stock-transfers', [StockTransferController::class, 'index']);
        Route::post('admin/stock-transfers', [StockTransferController::class, 'store']);
        Route::post('admin/stock-transfers/{id}/dispatch', [StockTransferController::class, 'dispatch']);
        Route::post('admin/stock-transfers/{id}/receive', [StockTransferController::class, 'receive']);
        Route::post('admin/stock-transfers/{id}/cancel', [StockTransferController::class, 'cancel']);
    });

    // ─── Billing: payment, sharing, returns ──────────────────────────────────
    Route::middleware('admin.perm:billing')->group(function () {
        Route::get('admin/frame-bills/{id}/upi', [BillingController::class, 'upi']);
        Route::post('admin/frame-bills/{id}/mark-paid', [BillingController::class, 'markPaid']);
        Route::post('admin/frame-bills/{id}/payment-link', [BillingController::class, 'paymentLink']);
        Route::get('admin/frame-bills/{id}/payment-status', [BillingController::class, 'paymentStatus']);
        Route::get('admin/frame-bills/{id}/share', [BillingController::class, 'share']);
        Route::get('admin/eye-checkup-bills/{id}/share', [BillingController::class, 'shareEyeCheckup']);
        Route::post('admin/frame-bills/{id}/email', [BillingController::class, 'email']);
        Route::get('admin/bill-returns', [BillingController::class, 'returns']);
        Route::post('admin/bill-returns', [BillingController::class, 'storeReturn']);
    });

    // ─── Customer profile + eye tests ────────────────────────────────────────
    Route::middleware('admin.perm:customers')->get('admin/customers/{id}/profile', [EyeTestController::class, 'profile']);
    Route::middleware('admin.perm:prescriptions')->group(function () {
        Route::post('admin/customers/{id}/eye-tests', [EyeTestController::class, 'store']);
        Route::post('admin/eye-tests/{id}', [EyeTestController::class, 'update']);
        Route::delete('admin/eye-tests/{id}', [EyeTestController::class, 'destroy']);
    });

    // ─── Reports ─────────────────────────────────────────────────────────────
    Route::middleware('admin.perm:reports')->get('admin/reports', [ReportController::class, 'index']);

    // ─── Security: MFA ───────────────────────────────────────────────────────
    Route::post('auth/mfa/setup', [AdminAuthController::class, 'mfaSetup']);
    Route::post('auth/mfa/enable', [AdminAuthController::class, 'mfaEnable']);
    Route::post('auth/mfa/disable', [AdminAuthController::class, 'mfaDisable']);
});
