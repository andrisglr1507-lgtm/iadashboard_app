<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \App\Models\OpnameResult::where('result_status', 'RECOUNT')->get();
echo "RECOUNT Results:\n";
foreach ($results as $res) {
    echo "ID: {$res->id} | SKU: {$res->reference_detail_id} | R1 Qty: ";
    var_dump($res->recount1_qty);
    echo " | R2 Qty: ";
    var_dump($res->recount2_qty);
    
    $roundNumber = 1;
    if ($res->recount1_qty !== null && $res->recount2_qty === null) {
        $roundNumber = 2;
    }
    echo " => Calculated Round: {$roundNumber}\n\n";
}

$assignments = \App\Models\OpnameRecountAssignment::all();
echo "ASSIGNMENTS IN DB:\n";
foreach ($assignments as $a) {
    echo "ID: {$a->assignment_id} | Bin: {$a->location_code} | SKU: {$a->id_product} | Round: {$a->round_number} | Status: {$a->status}\n";
}
