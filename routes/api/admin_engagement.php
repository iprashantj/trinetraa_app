<?php

use App\Http\Controllers\Api\Admin\AppointmentController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EnquiryController;
use App\Http\Controllers\Api\Admin\NewsletterController;
use App\Http\Controllers\Api\Admin\NotifyMeController;
use App\Http\Controllers\Api\Admin\PincodeController;
use App\Http\Controllers\Api\Admin\ReviewController;
use App\Http\Controllers\Api\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Reviews ───────────────────────────────────────────────────────────
    Route::get('admin/reviews', [ReviewController::class, 'index']);
    Route::delete('admin/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::post('admin/reviews/{id}/approve', [ReviewController::class, 'approve']);

    // ─── Enquiries ─────────────────────────────────────────────────────────
    Route::get('admin/enquiries', [EnquiryController::class, 'index']);
    Route::get('admin/enquiries/product', [EnquiryController::class, 'product']);

    // ─── Appointments ──────────────────────────────────────────────────────
    Route::get('admin/appointments', [AppointmentController::class, 'index']);
    Route::put('admin/appointments/{id}', [AppointmentController::class, 'update']);

    // ─── Newsletter ────────────────────────────────────────────────────────
    Route::get('admin/newsletter', [NewsletterController::class, 'index']);
    Route::delete('admin/newsletter', [NewsletterController::class, 'destroy']);

    // ─── Notify-me ─────────────────────────────────────────────────────────
    Route::get('admin/notify-me', [NotifyMeController::class, 'index']);
    Route::put('admin/notify-me', [NotifyMeController::class, 'update']);

    // ─── Pincodes ──────────────────────────────────────────────────────────
    Route::get('admin/pincodes', [PincodeController::class, 'index']);
    Route::post('admin/pincodes', [PincodeController::class, 'store']);
    Route::put('admin/pincodes/{id}', [PincodeController::class, 'update']);
    Route::delete('admin/pincodes/{id}', [PincodeController::class, 'destroy']);

    // ─── Settings ──────────────────────────────────────────────────────────
    Route::get('admin/settings', [SettingsController::class, 'index']);
    Route::post('admin/settings', [SettingsController::class, 'update']);

    // ─── Dashboard ─────────────────────────────────────────────────────────
    Route::get('admin/dashboard', [DashboardController::class, 'index']);
});
