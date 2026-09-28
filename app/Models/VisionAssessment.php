<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisionAssessment extends Model
{
    protected $table = 'vision_assessments';
    protected $fillable = ['name', 'email', 'phone', 'answers', 'result', 'created_at'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];
}
