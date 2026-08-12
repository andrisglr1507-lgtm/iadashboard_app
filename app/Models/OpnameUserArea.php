<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpnameUserArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'warehouse_id',
        'aisle',
        'user_id',
        'team_role'
    ];

    public function session()
    {
        return $this->belongsTo(OpnameSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
