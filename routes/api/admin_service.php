<?php

use App\Http\Controllers\Api\Admin\ReviewRequestController;
use App\Http\Controllers\Api\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log', 'admin.perm:billing'])->group(function () {
    // ─── Work orders: repair / lens / delivery tracking ──────────────────────
    Route::get('admin/work-orders', [WorkOrderController::class, 'index']);
    Route::post('admin/work-orders', [WorkOrderController::class, 'store']);
    Route::get('admin/work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::put('admin/work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::post('admin/work-orders/{id}/status', [WorkOrderController::class, 'setStatus']);
    Route::post('admin/work-orders/{id}/renotify', [WorkOrderController::class, 'renotify']);
    Route::delete('admin/work-orders/{id}', [WorkOrderController::class, 'destroy']);

    // ─── Google review request after a sale ──────────────────────────────────
    Route::post('admin/frame-bills/{id}/review-request', [ReviewRequestController::class, 'store']);
});
