<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaPemeriksaanDetail extends Model
{
    use HasFactory;

    protected $table = 'ba_pemeriksaan_detail';

    protected $guarded = ['id'];

    public function header()
    {
        return $this->belongsTo(BaPemeriksaanHeader::class, 'id_ba', 'id_ba');
    }
}
