<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    protected $guarded = [];
    protected $table = 'bins';
    // Parse Bin Code Data
    public static function parseBinCodeData($binCode)
    {
        $exceptions = ["BADSTOCK", "INTRUCK.A", "KARANTINA", "MISSING.STOCK", "QUARANTINE", "TAC"];
        if (in_array(strtoupper($binCode), $exceptions)) {
            return [
                'zone' => null,
                'ganjil_genap' => null,
                'level' => null
            ];
        }

        $parts = explode('.', $binCode);
        $zone = isset($parts[0]) ? $parts[0] : null;
        
        $ganjil_genap = null;
        if (isset($parts[1])) {
            $num = (int)$parts[1];
            $ganjil_genap = ($num % 2 == 0) ? 'Genap' : 'Ganjil';
        }

        $level = null;
        if (isset($parts[2])) {
            $level = (int)$parts[2];
        }

        return [
            'zone' => $zone,
            'ganjil_genap' => $ganjil_genap,
            'level' => $level
        ];
    }
}
