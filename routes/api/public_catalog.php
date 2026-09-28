<?php

use App\Http\Controllers\Api\Public\BlogController;
use App\Http\Controllers\Api\Public\BrandController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\EyewearController;
use App\Http\Controllers\Api\Public\GalleryController;
use App\Http\Controllers\Api\Public\HomeController;
use App\Http\Controllers\Api\Public\OfferController;
use App\Http\Controllers\Api\Public\SearchController;
use App\Http\Controllers\Api\Public\ServiceController;
use App\Http\Controllers\Api\Public\SettingsController;
use Illuminate\Support\Facades\Route;

// ─── Home ────────────────────────────────────────────────────────────────────
Route::get('public/home', [HomeController::class, 'index']);

// ─── Eyewears ──────────────────────────────────────────────────────────────--
Route::get('public/eyewears', [EyewearController::class, 'index']);
Route::get('public/eyewears/{slug}', [EyewearController::class, 'show']);

// ─── Brands ──────────────────────────────────────────────────────────────────
Route::get('public/brands', [BrandController::class, 'index']);
Route::get('public/brands/{slug}', [BrandController::class, 'show']);

// ─── Categories ────────────────────────────────────────────────────────────--
Route::get('public/categories', [CategoryController::class, 'index']);

// ─── Blog ──────────────────────────────────────────────────────────────────--
Route::get('public/blog', [BlogController::class, 'index']);
Route::get('public/blog/{slug}', [BlogController::class, 'show']);

// ─── Gallery ─────────────────────────────────────────────────────────────────
Route::get('public/gallery', [GalleryController::class, 'index']);

// ─── Services ────────────────────────────────────────────────────────────────
Route::get('public/services', [ServiceController::class, 'index']);

// ─── Offers ──────────────────────────────────────────────────────────────────
Route::get('public/offers', [OfferController::class, 'index']);

// ─── Search ──────────────────────────────────────────────────────────────────
Route::get('public/search', [SearchController::class, 'index']);

// ─── Settings ────────────────────────────────────────────────────────────────
Route::get('public/settings', [SettingsController::class, 'index']);
