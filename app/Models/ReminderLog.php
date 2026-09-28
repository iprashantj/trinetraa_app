<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $table = 'reminder_logs';
    protected $fillable = ['type', 'recipient_id', 'email', 'subject', 'status', 'sent_at', 'error'];
    public $timestamps = false;
    protected $casts = ['sent_at' => 'datetime'];
}
