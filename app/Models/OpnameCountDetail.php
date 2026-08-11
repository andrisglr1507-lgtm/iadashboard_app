<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameCountDetail extends Model
{
    protected $table = 'opname_count_details';

    protected $primaryKey = 'detail_id';

    // Timestamps are non-standard (created_at + update_at instead of updated_at)
    public $timestamps = false;

    protected $fillable = [
        'count_id',
        'user_id',
        'id_product',
        'stock_condition',
        'qty_karton',
        'qty_pcs',
        'final_qty',
        'client_uuid',
        'is_manual',
    ];

    protected $casts = [
        'count_id'    => 'integer',
        'user_id'     => 'integer',
        'qty_karton'  => 'integer',
        'qty_pcs'     => 'integer',
        'final_qty'   => 'integer',
        'is_manual'   => 'boolean',
        'created_at'  => 'datetime',
        'update_at'   => 'datetime',
    ];

    // ---- Relationships ----

    /**
     * Get the count header this detail belongs to.
     */
    public function header()
    {
        return $this->belongsTo(OpnameCountHeader::class, 'count_id', 'count_id');
    }

    /**
     * Get the master product for this detail.
     */
    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'id_product', 'id_product');
    }

    /**
     * Get the user who performed this count.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
