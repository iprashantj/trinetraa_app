<?php

use App\Http\Controllers\Api\Admin\ReservationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log', 'admin.perm:billing'])->group(function () {
    Route::get('admin/reservations', [ReservationController::class, 'index']);
    Route::get('admin/reservations/pending-count', [ReservationController::class, 'pendingCount']);
    Route::post('admin/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
    Route::post('admin/reservations/{id}/collect', [ReservationController::class, 'collect']);
    Route::post('admin/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
});
