<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES FROM equuddbx_so_dc');
foreach($tables as $table) {
    $tableName = array_values((array)$table)[0];
    $create = DB::select("SHOW CREATE TABLE equuddbx_so_dc." . $tableName);
    echo "\n-- Table: $tableName --\n";
    echo array_values((array)$create[0])[1];
    echo "\n";
}
