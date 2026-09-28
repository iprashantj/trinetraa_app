<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $fillable = ['user_name', 'user_email', 'rating', 'review_text', 'image', 'is_approved', 'eyewear_id'];
    protected $casts = ['is_approved' => 'boolean', 'rating' => 'integer'];

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
