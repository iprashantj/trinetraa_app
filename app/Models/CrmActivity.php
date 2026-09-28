<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    protected $table = 'crm_activities';
    protected $fillable = ['contact_id', 'type', 'description', 'done_at'];
    public $timestamps = false;
    protected $casts = ['done_at' => 'datetime'];

    public function contact()
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }
}
