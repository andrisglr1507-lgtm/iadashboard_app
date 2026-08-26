<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement("ALTER TABLE bad_stock_records MODIFY category ENUM('BAD_STOCK', 'EXPIRED_STOCK', 'GOOD_STOCK') NOT NULL");
echo 'Done altering table';
