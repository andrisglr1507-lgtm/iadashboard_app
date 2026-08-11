<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameProduct extends Model
{
    protected $table = 'opname_products';

    // Only created_at exists, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'no_urut',
        'session_id',
        'id_product',
        'stock_system',
        'harga',
        'is_manual',
    ];

    protected $casts = [
        'no_urut'      => 'integer',
        'stock_system'  => 'integer',
        'harga'        => 'decimal:2',
        'is_manual'    => 'boolean',
    ];

    // ---- Relationships ----

    /**
     * Get the opname session this product belongs to.
     */
    public function session()
    {
        return $this->belongsTo(OpnameSession::class, 'session_id', 'session_id');
    }

    /**
     * Get the master product data.
     */
    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class, 'id_product', 'id_product');
    }
}
