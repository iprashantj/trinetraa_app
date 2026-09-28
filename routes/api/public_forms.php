<?php

use App\Http\Controllers\Api\Public\AppointmentController;
use App\Http\Controllers\Api\Public\CheckPincodeController;
use App\Http\Controllers\Api\Public\ContactController;
use App\Http\Controllers\Api\Public\EnquiryController;
use App\Http\Controllers\Api\Public\LoyaltyController;
use App\Http\Controllers\Api\Public\NewsletterController;
use App\Http\Controllers\Api\Public\NotifyMeController;
use App\Http\Controllers\Api\Public\PushSubscribeController;
use App\Http\Controllers\Api\Public\RecommendController;
use App\Http\Controllers\Api\Public\ReviewController;
use App\Http\Controllers\Api\Public\SubscriptionController;
use App\Http\Controllers\Api\Public\VisionAssessmentController;
use Illuminate\Support\Facades\Route;

// ─── Reviews ─────────────────────────────────────────────────────────────────
Route::get('public/reviews/captcha', [ReviewController::class, 'captcha']);
Route::get('public/reviews', [ReviewController::class, 'index']);
Route::post('public/reviews', [ReviewController::class, 'store'])->middleware('throttle:forms');

// ─── Contact ─────────────────────────────────────────────────────────────────
Route::post('public/contact-us', [ContactController::class, 'store'])->middleware('throttle:forms');

// ─── Product enquiry ───────────────────────────────────────────────────────--
Route::post('public/enquiry', [EnquiryController::class, 'store'])->middleware('throttle:forms');

// ─── Appointments ──────────────────────────────────────────────────────────--
Route::post('public/appointments', [AppointmentController::class, 'store'])->middleware('throttle:forms');

// ─── Notify me ───────────────────────────────────────────────────────────────
Route::post('public/notify-me', [NotifyMeController::class, 'store'])->middleware('throttle:forms');

// ─── Newsletter ──────────────────────────────────────────────────────────────
Route::post('public/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:forms');

// ─── Check pincode ─────────────────────────────────────────────────────────--
Route::get('public/check-pincode', [CheckPincodeController::class, 'index']);

// ─── Frame recommendation quiz ─────────────────────────────────────────────--
Route::post('public/recommend', [RecommendController::class, 'store']);

// ─── Vision assessment ─────────────────────────────────────────────────────--
Route::post('public/vision-assessment', [VisionAssessmentController::class, 'store']);

// ─── Loyalty (customer-auth) ───────────────────────────────────────────────--
Route::get('public/loyalty', [LoyaltyController::class, 'index'])->middleware('customer.auth');

// ─── Lens subscriptions (customer-auth) ────────────────────────────────────--
Route::get('public/subscriptions', [SubscriptionController::class, 'index'])->middleware('customer.auth');
Route::post('public/subscriptions', [SubscriptionController::class, 'store'])->middleware('customer.auth');

// ─── Push subscriptions (customer-auth) ────────────────────────────────────--
Route::post('public/push-subscribe', [PushSubscribeController::class, 'store'])->middleware('customer.auth');
Route::delete('public/push-subscribe', [PushSubscribeController::class, 'destroy'])->middleware('customer.auth');
