<?php

use App\Http\Controllers\Api\Public\ReservationController;
use Illuminate\Support\Facades\Route;

// Store-pickup reservations (5 per hour per IP; honeypot + availability check inside the controller).
Route::post('public/reservations', [ReservationController::class, 'store'])->middleware('throttle:reserve');
