<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameCount extends Model
{
    protected $guarded = [];
    protected $table = 'opname_counts';

    public function user()
    {
        return $this->belongsTo(User::class, 'counted_by', 'id');
    }

    public function referenceDetail()
    {
        return $this->belongsTo(OpnameReferenceDetail::class, 'reference_detail_id', 'id');
    }
}
