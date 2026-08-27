<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadStockRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku_code',
        'qty_carton',
        'qty_pcs',
        'total_qty',
        'item_status',
        'category',
        'batch_no',
        'expired_date',
        'recorded_by',
        'koli'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'sku_code', 'sku_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }
}
