<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    protected $table = 'stock_items';
    protected $fillable = [
        'name', 'model_number', 'sku', 'category_id', 'current_stock', 'sale_price',
        'cost_price', 'description', 'image',
    ];
    protected $casts = [
        'current_stock' => 'integer',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'stock_item_id');
    }
}
