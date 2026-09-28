<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Eyewear;
use App\Models\GalleryItem;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $featuredEyewears = Eyewear::query()
            ->where('is_active', true)
            ->with(['category:id,name,slug'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $approvedReviews = Review::query()
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $galleryItems = GalleryItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(12)
            ->get();

        $offers = Offer::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return $this->data([
            'featured_eyewears' => $featuredEyewears,
            'approved_reviews' => $approvedReviews,
            'gallery_items' => $galleryItems,
            'brands' => $brands,
            'offers' => $offers,
            'services' => $services,
        ]);
    }
}
