<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sessionId = 1; // Assume session 1

$recounts = \App\Models\OpnameRecountAssignment::all();
echo "Total Recount Assignments: " . $recounts->count() . "\n";
foreach($recounts as $r) {
    echo "ID: {$r->assignment_id} | To: {$r->assigned_to} | Round: {$r->round_number} | Status: {$r->status} | Bin: {$r->location_code} | SKU: {$r->id_product}\n";
}

$results = \App\Models\OpnameResult::where('result_status', 'RECOUNT')->get();
echo "\nTotal Results needing RECOUNT: " . $results->count() . "\n";
foreach($results as $r) {
    echo "SKU: {$r->reference_detail_id} | R1: {$r->recount1_qty} | R2: {$r->recount2_qty}\n";
}
