<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameReferenceDetail extends Model
{
    protected $guarded = [];
    protected $table = 'opname_reference_details';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function bin()
    {
        return $this->belongsTo(Bin::class, 'bin_code', 'bin_code');
    }
}
