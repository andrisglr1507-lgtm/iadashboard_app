<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobSweepCount extends Model
{
    protected $table = 'sob_sweep_counts';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function header()
    {
        return $this->belongsTo(SobSweepHeader::class, 'header_id');
    }

    public function counter()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
