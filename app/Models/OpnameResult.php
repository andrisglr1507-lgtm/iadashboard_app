<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameResult extends Model
{
    protected $guarded = [];
    protected $table = 'opname_results';

    public function referenceDetail()
    {
        return $this->belongsTo(OpnameReferenceDetail::class, 'reference_detail_id');
    }
}
