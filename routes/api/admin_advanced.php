<?php

use App\Http\Controllers\Api\Admin\CrmContactController;
use App\Http\Controllers\Api\Admin\ErpExportController;
use App\Http\Controllers\Api\Admin\LoyaltyController;
use App\Http\Controllers\Api\Admin\PushNotifyController;
use App\Http\Controllers\Api\Admin\ReminderController;
use App\Http\Controllers\Api\Admin\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Loyalty ───────────────────────────────────────────────────────────
    Route::get('admin/loyalty', [LoyaltyController::class, 'index']);
    Route::post('admin/loyalty', [LoyaltyController::class, 'store']);

    // ─── Lens Subscriptions ──────────────────────────────────────────────────
    Route::get('admin/subscriptions', [SubscriptionController::class, 'index']);
    Route::put('admin/subscriptions/{id}', [SubscriptionController::class, 'update']);
    Route::delete('admin/subscriptions/{id}', [SubscriptionController::class, 'destroy']);

    // ─── CRM Contacts ────────────────────────────────────────────────────────
    Route::get('admin/crm/contacts', [CrmContactController::class, 'index']);
    Route::post('admin/crm/contacts', [CrmContactController::class, 'store']);
    Route::get('admin/crm/contacts/{id}', [CrmContactController::class, 'show']);
    Route::put('admin/crm/contacts/{id}', [CrmContactController::class, 'update']);
    Route::delete('admin/crm/contacts/{id}', [CrmContactController::class, 'destroy']);

    // ─── Reminders ───────────────────────────────────────────────────────────
    Route::get('admin/reminders', [ReminderController::class, 'index']);
    Route::post('admin/reminders', [ReminderController::class, 'store']);

    // ─── Push Notify ─────────────────────────────────────────────────────────
    Route::get('admin/push-notify', [PushNotifyController::class, 'index']);
    Route::post('admin/push-notify', [PushNotifyController::class, 'store']);

    // ─── ERP Export ──────────────────────────────────────────────────────────
    Route::get('admin/erp-export', [ErpExportController::class, 'export']);
    Route::get('admin/erp-export/logs', [ErpExportController::class, 'logs']);
});
