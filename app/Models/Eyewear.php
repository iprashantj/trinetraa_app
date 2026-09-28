<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eyewear extends Model
{
    protected $table = 'eyewears';
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'discount_percentage', 'image',
        'brand', 'is_active', 'sort_order', 'amazon_url', 'flipkart_url', 'amazon_enabled',
        'flipkart_enabled', 'availability', 'marketplace_sku', 'try_on_image', 'try_on_offset_x',
        'try_on_offset_y', 'try_on_scale', 'face_shape_tags',
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'amazon_enabled' => 'boolean',
        'flipkart_enabled' => 'boolean',
        'try_on_offset_x' => 'float',
        'try_on_offset_y' => 'float',
        'try_on_scale' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class, 'eyewear_id');
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class, 'eyewear_id');
    }

    public function notifyMeRequests()
    {
        return $this->hasMany(NotifyMeRequest::class, 'eyewear_id');
    }

    public function tags()
    {
        return $this->hasMany(EyewearTag::class, 'eyewear_id');
    }
}
