<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function eyewears()
    {
        return $this->hasMany(Eyewear::class, 'category_id');
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class, 'category_id');
    }
}
