<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaPemeriksaanHeader extends Model
{
    use HasFactory;

    protected $table = 'ba_pemeriksaan_headers';

    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(BaPemeriksaanDetail::class, 'id_ba', 'id_ba');
    }
}
