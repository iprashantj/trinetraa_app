<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpExportLog extends Model
{
    protected $table = 'erp_export_logs';
    protected $fillable = ['export_type', 'format', 'row_count', 'filename', 'status', 'created_at'];
    public $timestamps = false;
    protected $casts = ['row_count' => 'integer', 'created_at' => 'datetime'];
}
