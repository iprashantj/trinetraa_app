<?php

use App\Http\Controllers\Api\Public\WorkOrderStatusController;
use Illuminate\Support\Facades\Route;

// ─── Order status lookup (order number + phone) ──────────────────────────────
Route::get('public/work-orders/status', [WorkOrderStatusController::class, 'show'])->middleware('throttle:forms');
