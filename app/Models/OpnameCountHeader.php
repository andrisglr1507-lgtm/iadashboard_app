<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameCountHeader extends Model
{
    protected $table = 'opname_count_headers';

    protected $primaryKey = 'count_id';

    // Only created_at exists, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'team',
        'location_code',
        'round_number',
        'status',
    ];

    protected $casts = [
        'round_number' => 'integer',
    ];

    // ---- Relationships ----

    /**
     * Get the details (line items) for this count header.
     */
    public function details()
    {
        return $this->hasMany(OpnameCountDetail::class, 'count_id', 'count_id');
    }

    /**
     * Get the opname session this header belongs to.
     */
    public function session()
    {
        return $this->belongsTo(OpnameSession::class, 'session_id', 'session_id');
    }
}
