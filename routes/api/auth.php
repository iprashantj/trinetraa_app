<?php

use App\Http\Controllers\Api\Admin\ActivityController;
use App\Http\Controllers\Api\Admin\TeamController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use Illuminate\Support\Facades\Route;

// ─── Admin auth ──────────────────────────────────────────────────────────────
Route::post('auth/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('auth/logout', [AdminAuthController::class, 'logout']);
Route::get('auth/me', [AdminAuthController::class, 'me'])->middleware('admin.auth');
Route::post('auth/change-password', [AdminAuthController::class, 'changePassword'])->middleware(['admin.auth', 'admin.log']);

// ─── Team & audit trail (owner-only) ─────────────────────────────────────────
Route::middleware(['admin.auth', 'admin.owner', 'admin.log'])->group(function () {
    Route::get('admin/team', [TeamController::class, 'index']);
    Route::post('admin/team', [TeamController::class, 'store']);
    Route::put('admin/team/{id}', [TeamController::class, 'update']);
    Route::delete('admin/team/{id}', [TeamController::class, 'destroy']);
    Route::get('admin/activity', [ActivityController::class, 'index']);
});

// ─── Customer auth ───────────────────────────────────────────────────────────
Route::post('customer/auth/login', [CustomerAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('customer/auth/register', [CustomerAuthController::class, 'register'])->middleware('throttle:auth');
Route::post('customer/auth/logout', [CustomerAuthController::class, 'logout']);
Route::get('customer/auth/me', [CustomerAuthController::class, 'me'])->middleware('customer.auth');
