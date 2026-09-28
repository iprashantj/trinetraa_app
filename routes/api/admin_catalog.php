<?php

use App\Http\Controllers\Api\Admin\BlogController;
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\EyewearController;
use App\Http\Controllers\Api\Admin\GalleryController;
use App\Http\Controllers\Api\Admin\ImageUploadController;
use App\Http\Controllers\Api\Admin\OfferController;
use App\Http\Controllers\Api\Admin\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.auth', 'admin.log'])->group(function () {
    // ─── Image Upload ────────────────────────────────────────────────────────
    Route::post('admin/upload-image', [ImageUploadController::class, 'store']);
    // ─── Eyewears ────────────────────────────────────────────────────────────
    Route::get('admin/eyewears', [EyewearController::class, 'index']);
    Route::post('admin/eyewears', [EyewearController::class, 'store']);
    Route::get('admin/eyewears/{id}', [EyewearController::class, 'show']);
    Route::put('admin/eyewears/{id}', [EyewearController::class, 'update']);
    Route::delete('admin/eyewears/{id}', [EyewearController::class, 'destroy']);

    // ─── Categories ──────────────────────────────────────────────────────────
    Route::get('admin/categories', [CategoryController::class, 'index']);
    Route::post('admin/categories', [CategoryController::class, 'store']);
    Route::get('admin/categories/{id}', [CategoryController::class, 'show']);
    Route::put('admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('admin/categories/{id}', [CategoryController::class, 'destroy']);

    // ─── Brands ──────────────────────────────────────────────────────────────
    Route::get('admin/brands', [BrandController::class, 'index']);
    Route::post('admin/brands', [BrandController::class, 'store']);
    Route::put('admin/brands/{id}', [BrandController::class, 'update']);
    Route::delete('admin/brands/{id}', [BrandController::class, 'destroy']);

    // ─── Services ────────────────────────────────────────────────────────────
    Route::get('admin/services', [ServiceController::class, 'index']);
    Route::post('admin/services', [ServiceController::class, 'store']);
    Route::put('admin/services/{id}', [ServiceController::class, 'update']);
    Route::delete('admin/services/{id}', [ServiceController::class, 'destroy']);

    // ─── Offers ──────────────────────────────────────────────────────────────
    Route::get('admin/offers', [OfferController::class, 'index']);
    Route::post('admin/offers', [OfferController::class, 'store']);
    Route::put('admin/offers/{id}', [OfferController::class, 'update']);
    Route::delete('admin/offers/{id}', [OfferController::class, 'destroy']);

    // ─── Blog ────────────────────────────────────────────────────────────────
    Route::get('admin/blog', [BlogController::class, 'index']);
    Route::post('admin/blog', [BlogController::class, 'store']);
    Route::get('admin/blog/{id}', [BlogController::class, 'show']);
    Route::put('admin/blog/{id}', [BlogController::class, 'update']);
    Route::delete('admin/blog/{id}', [BlogController::class, 'destroy']);

    // ─── Gallery ─────────────────────────────────────────────────────────────
    Route::get('admin/gallery', [GalleryController::class, 'index']);
    Route::post('admin/gallery', [GalleryController::class, 'store']);
    Route::put('admin/gallery/{id}', [GalleryController::class, 'update']);
    Route::delete('admin/gallery/{id}', [GalleryController::class, 'destroy']);
});
