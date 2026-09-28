<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';
    protected $fillable = ['user_id', 'user_email', 'method', 'path', 'action', 'status', 'ip', 'created_at'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime', 'status' => 'integer'];
}
