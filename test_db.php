<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

try {
    echo "--- HEADER ---\n";
    $headers = DB::table('opname_count_headers')->limit(5)->get();
    foreach($headers as $h) {
        echo "count_id: {$h->count_id} | session_id: {$h->session_id} \n";
    }
    
    echo "--- DETAILS ---\n";
    $details = DB::table('opname_count_details')->limit(5)->get();
    foreach($details as $d) {
        echo "detail_id: {$d->detail_id} | count_id: {$d->count_id} | user_id: {$d->user_id} | product: {$d->id_product}\n";
    }
    
    // Test the actual query
    echo "--- TEST QUERY ---\n";
    if (count($headers) > 0) {
        $testSessionId = $headers[0]->session_id;
        echo "Testing with session_id: $testSessionId\n";
        
        $records = DB::table('opname_count_headers as h')
            ->where('h.session_id', $testSessionId)
            ->join('opname_count_details as d', 'h.count_id', '=', 'd.count_id')
            ->leftJoin('users as u', 'd.user_id', '=', 'u.id')
            ->leftJoin('master_products as p', 'd.id_product', '=', 'p.id_product')
            ->select(
                'h.session_id',
                'h.status',
                'u.name as user_name',
                'd.id_product',
                'p.nama_product',
                'd.final_qty as qty_fisik',
                'h.created_at'
            )
            ->get();
            
        echo "Records found: " . count($records) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
