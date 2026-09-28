<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table = 'blog_posts';
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'image', 'is_published',
        'published_at', 'meta_title', 'meta_description',
    ];
    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime'];
}
