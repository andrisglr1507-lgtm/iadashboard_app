<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$activeSession = \App\Models\OpnameSession::where('status', 'ACTIVE')->first();
echo "Active Session ID: " . ($activeSession ? $activeSession->id : 'NONE') . "\n";

$recounts = \App\Models\OpnameResult::where('result_status', 'RECOUNT')->get();
echo "Results needing RECOUNT: " . $recounts->count() . "\n";
foreach($recounts as $r) {
    echo "SKU: {$r->reference_detail_id} | Session: {$r->session_id} | R1: {$r->recount1_qty} | R2: {$r->recount2_qty}\n";
}
