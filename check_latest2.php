<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

use Illuminate\Support\Facades\DB;

\ = DB::table('bad_stock_records')->orderBy('id', 'desc')->first();
echo json_encode(\);
