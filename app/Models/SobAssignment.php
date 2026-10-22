<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobAssignment extends Model
{
    protected $table = 'sob_assignments';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function zone()
    {
        return $this->belongsTo(SobZone::class, 'zone_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
