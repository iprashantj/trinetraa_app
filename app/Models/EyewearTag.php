<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyewearTag extends Model
{
    protected $table = 'eyewear_tags';
    protected $fillable = ['eyewear_id', 'tag'];
    public $timestamps = false;

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
