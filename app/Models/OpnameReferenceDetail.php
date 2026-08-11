<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameReferenceDetail extends Model
{
    protected $guarded = [];
    protected $table = 'equuddbx_so_dc.opname_reference_details';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
