<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameSession extends Model
{
    protected $table = 'opname_sessions';
    protected $guarded = [];
    
    public function reference()
    {
        return $this->belongsTo(OpnameReference::class, 'reference_id', 'id');
    }
}
