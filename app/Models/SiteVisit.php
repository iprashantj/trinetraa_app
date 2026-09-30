<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $table = 'site_visits';
    protected $fillable = ['visit_date', 'count'];
    protected $casts = [
        'visit_date' => 'date',
        'count' => 'integer',
    ];
}
