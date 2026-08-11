<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameRecountAssignment extends Model
{
    protected $table = 'opname_recount_assignments';

    protected $primaryKey = 'assignment_id';

    // All timestamp columns are custom, not Laravel standard
    public $timestamps = false;

    protected $fillable = [
        'previous_assignment_id',
        'session_id',
        'location_code',
        'id_product',
        'round_number',
        'assigned_to',
        'assigned_by',
        'status',
        'is_final',
        'evaluation_result',
        'finalized_at',
        'assigned_at',
        'distributed_at',
        'started_at',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'previous_assignment_id' => 'integer',
        'round_number'           => 'integer',
        'is_final'               => 'boolean',
        'finalized_at'           => 'datetime',
        'assigned_at'            => 'datetime',
        'distributed_at'         => 'datetime',
        'started_at'             => 'datetime',
        'submitted_at'           => 'datetime',
        'approved_at'            => 'datetime',
    ];

    // ---- Relationships ----

    /**
     * Get the opname session this assignment belongs to.
     */
    public function session()
    {
        return $this->belongsTo(OpnameSession::class, 'session_id', 'session_id');
    }

    /**
     * Get the user this task is assigned to.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    /**
     * Get the user who made this assignment.
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }

    /**
     * Get the previous assignment in the recount chain.
     */
    public function previousAssignment()
    {
        return $this->belongsTo(self::class, 'previous_assignment_id', 'assignment_id');
    }

    /**
     * Get the master product for this assignment.
     */
    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'id_product', 'id_product');
    }
}
