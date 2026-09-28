<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web (page) routes — ported from the Next.js app/ page tree.
| Pages are client-rendered: each Blade view fetches from /api/* just like
| the original React pages did.
|--------------------------------------------------------------------------
*/

// ─── Sitemaps ─────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml',          [SitemapController::class, 'index']);
Route::get('/sitemap-pages.xml',    [SitemapController::class, 'pages']);
Route::get('/sitemap-eyewears.xml', [SitemapController::class, 'eyewears']);
Route::get('/sitemap-brands.xml',   [SitemapController::class, 'brands']);
Route::get('/sitemap-blog.xml',     [SitemapController::class, 'blog']);

// ─── Public site ─────────────────────────────────────────────────────────────
Route::view('/', 'public.home');
Route::view('/landing', 'public.landing');
Route::view('/about', 'public.about');
Route::view('/appointment', 'public.appointment');
Route::view('/blog', 'public.blog.index');
Route::get('/blog/{slug}', fn ($slug) => view('public.blog.show', ['slug' => $slug]));
Route::view('/brands', 'public.brands.index');
Route::get('/brands/{slug}', fn ($slug) => view('public.brands.show', ['slug' => $slug]));
Route::view('/cart', 'public.cart');
Route::view('/compare', 'public.compare');
Route::view('/contact', 'public.contact');
Route::view('/cookie-policy', 'public.cookie-policy');
Route::view('/disclaimer', 'public.disclaimer');
Route::view('/eyewears', 'public.eyewears.index');
Route::get('/eyewears/{slug}', fn ($slug) => view('public.eyewears.show', ['slug' => $slug]));
Route::view('/gallery', 'public.gallery');
Route::view('/loyalty', 'public.loyalty');
Route::view('/offers', 'public.offers');
Route::view('/privacy-policy', 'public.privacy-policy');
Route::view('/recommend', 'public.recommend');
Route::view('/refund-policy', 'public.refund-policy');
Route::view('/reviews', 'public.reviews');
Route::view('/search', 'public.search');
Route::view('/services', 'public.services');
Route::view('/shipping-policy', 'public.shipping-policy');
Route::view('/shop/amazon', 'public.shop.amazon');
Route::view('/shop/flipkart', 'public.shop.flipkart');
Route::view('/subscriptions', 'public.subscriptions');
Route::view('/terms', 'public.terms');
Route::view('/try-on', 'public.try-on');
Route::view('/vision-test', 'public.vision-test');

// ─── Customer account ────────────────────────────────────────────────────────
Route::view('/account/login', 'account.login');
Route::view('/account/register', 'account.register');
Route::view('/account', 'account.dashboard')->middleware('customer.auth');

// ─── Admin ───────────────────────────────────────────────────────────────────
Route::view('/admin/login', 'admin.login');
Route::redirect('/admin', '/admin/dashboard');
Route::middleware('admin.auth')->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard');
    Route::view('/admin/eyewears', 'admin.eyewears');
    Route::view('/admin/categories', 'admin.categories');
    Route::view('/admin/brands', 'admin.brands');
    Route::view('/admin/services', 'admin.services');
    Route::view('/admin/offers', 'admin.offers');
    Route::view('/admin/blog', 'admin.blog');
    Route::view('/admin/reviews', 'admin.reviews');
    Route::view('/admin/enquiries', 'admin.enquiries');
    Route::view('/admin/appointments', 'admin.appointments');
    Route::view('/admin/gallery', 'admin.gallery');
    Route::view('/admin/newsletter', 'admin.newsletter');
    Route::view('/admin/notify-me', 'admin.notify-me');
    Route::view('/admin/pincodes', 'admin.pincodes');
    Route::view('/admin/settings', 'admin.settings');
    Route::view('/admin/stock-items', 'admin.stock-items');
    Route::view('/admin/customers', 'admin.customers');
    Route::view('/admin/orders', 'admin.orders');
    Route::view('/admin/frame-bills', 'admin.frame-bills');
    Route::view('/admin/eye-checkup-bills', 'admin.eye-checkup-bills');
    Route::get('/admin/frame-bills/invoice-pdf', [\App\Http\Controllers\Admin\InvoicePdfController::class, 'frameBills']);
    Route::get('/admin/eye-checkup-bills/invoice-pdf', [\App\Http\Controllers\Admin\InvoicePdfController::class, 'eyeCheckupBills']);
    Route::view('/admin/loyalty', 'admin.loyalty');
    Route::view('/admin/subscriptions', 'admin.subscriptions');
    Route::view('/admin/crm', 'admin.crm');
    Route::view('/admin/reminders', 'admin.reminders');
    Route::view('/admin/push-notify', 'admin.push-notify');
    Route::view('/admin/erp-export', 'admin.erp-export');
    Route::view('/admin/team', 'admin.team');
    Route::view('/admin/activity', 'admin.activity');
});
