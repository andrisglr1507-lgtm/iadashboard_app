<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SobReconciliation extends Model
{
    protected $table = 'sob_reconciliations';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(SobOpnameSession::class, 'session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
