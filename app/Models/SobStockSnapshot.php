<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobStockSnapshot extends Model
{
    protected $table = 'sob_stock_snapshots';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
