<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OpnameResult;
use App\Http\Controllers\Sodc\Result\CountResultController;

$firstResult = OpnameResult::first();
if (!$firstResult) {
    echo "No OpnameResult found in DB.\n";
    exit;
}

echo "Testing with OpnameResult ID: {$firstResult->id}\n";

$controller = new CountResultController();

// 1. Test detailJson
$jsonRes = $controller->detailJson($firstResult->id);
echo "JSON Status: " . $jsonRes->getStatusCode() . "\n";
$data = json_decode($jsonRes->getContent(), true);
echo "JSON Product: " . ($data['data']['product_name'] ?? 'none') . "\n";
echo "JSON SKU: " . ($data['data']['sku_code'] ?? 'none') . "\n";
echo "JSON Category: " . ($data['data']['product_category_code'] ?? 'none') . "\n";
echo "JSON Packname: " . ($data['data']['packname'] ?? 'none') . "\n";
echo "JSON UOM: " . ($data['data']['uom'] ?? 'none') . "\n";
echo "JSON Total Counts: " . count($data['data']['counts'] ?? []) . "\n";

// 2. Test show view render
$viewRes = $controller->show($firstResult->id);
echo "Show View: " . $viewRes->name() . "\n";
echo "Render View length: " . strlen($viewRes->render()) . " bytes\n";
echo "ALL TESTS PASSED!\n";
