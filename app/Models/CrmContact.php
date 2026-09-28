<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmContact extends Model
{
    protected $table = 'crm_contacts';
    protected $fillable = ['name', 'email', 'phone', 'source', 'status', 'notes'];

    public function activities()
    {
        return $this->hasMany(CrmActivity::class, 'contact_id');
    }
}
