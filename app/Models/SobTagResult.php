<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobTagResult extends Model
{
    protected $table = 'sob_tag_results';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function header()
    {
        return $this->belongsTo(SobSweepHeader::class, 'header_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }

    public function recountedByUser()
    {
        return $this->belongsTo(User::class, 'recounted_by');
    }
}
