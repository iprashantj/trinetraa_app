<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $fillable = ['name', 'email', 'phone', 'service', 'appt_date', 'time_slot', 'notes', 'status'];
    protected $casts = ['appt_date' => 'date'];
}
