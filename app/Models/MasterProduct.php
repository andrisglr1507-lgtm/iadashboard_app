<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;

    protected $table = 'master_products';
    
    // Primary key is string and non-incrementing
    protected $primaryKey = 'id_product';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_product',
        'product_name',
        'principal',
        'barcode',
        'carton_code',
        'packname',
        'uom',
        'is_active',
    ];

    // Cast attributes if needed
    protected $casts = [
        'is_active' => 'boolean',
        'uom' => 'integer',
    ];

    // ---- Relationships ----

    /**
     * Get the opname products referencing this master product.
     */
    public function opnameProducts()
    {
        return $this->hasMany(OpnameProduct::class, 'id_product', 'id_product');
    }

    /**
     * Get the count details referencing this master product.
     */
    public function countDetails()
    {
        return $this->hasMany(OpnameCountDetail::class, 'id_product', 'id_product');
    }
}
