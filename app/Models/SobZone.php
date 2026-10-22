<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobZone extends Model
{
    protected $table = 'sob_zones';
    protected $guarded = [];

    public function assignments()
    {
        return $this->hasMany(SobAssignment::class, 'zone_id');
    }

    public function sweepHeaders()
    {
        return $this->hasMany(SobSweepHeader::class, 'zone_id');
    }
}
