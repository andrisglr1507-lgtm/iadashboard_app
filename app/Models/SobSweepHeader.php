<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobSweepHeader extends Model
{
    protected $table = 'sob_sweep_headers';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function zone()
    {
        return $this->belongsTo(SobZone::class, 'zone_id');
    }

    public function teamAUser()
    {
        return $this->belongsTo(User::class, 'team_a_user_id');
    }

    public function teamBUser()
    {
        return $this->belongsTo(User::class, 'team_b_user_id');
    }

    public function counts()
    {
        return $this->hasMany(SobSweepCount::class, 'header_id');
    }

    public function tagResults()
    {
        return $this->hasMany(SobTagResult::class, 'header_id');
    }
}
