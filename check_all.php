<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$count = DB::table('bad_stock_records')->count();
echo "Count: " . $count . "\n";
$all = DB::table('bad_stock_records')->get();
echo json_encode($all);
