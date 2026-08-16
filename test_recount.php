<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OpnameRecountAssignment;

try {
    $assignments = OpnameRecountAssignment::all();
    echo "Found " . $assignments->count() . " recount assignments.\n";
    if ($assignments->count() > 0) {
        print_r($assignments->first()->toArray());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
