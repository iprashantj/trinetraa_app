<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyMeRequest extends Model
{
    protected $table = 'notify_me_requests';
    protected $fillable = ['email', 'name', 'eyewear_id', 'is_notified'];
    protected $casts = ['is_notified' => 'boolean'];

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
