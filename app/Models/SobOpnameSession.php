<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobOpnameSession extends Model
{
    protected $table = 'sob_opname_sessions';
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(SobAssignment::class, 'session_id');
    }

    public function sweepHeaders()
    {
        return $this->hasMany(SobSweepHeader::class, 'session_id');
    }

    public function sweepCounts()
    {
        return $this->hasMany(SobSweepCount::class, 'session_id');
    }

    public function tagResults()
    {
        return $this->hasMany(SobTagResult::class, 'session_id');
    }

    public function stockSnapshots()
    {
        return $this->hasMany(SobStockSnapshot::class, 'session_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(SobReconciliation::class, 'session_id');
    }
}
