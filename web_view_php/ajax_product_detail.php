<?php
// ajax_product_detail.php - Mengambil histori hitung produk spesifik (dengan detail_id)
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once "db.php";

header('Content-Type: application/json');

$sessionId = $_GET['session_id'] ?? null;
$productId = $_GET['id_product'] ?? null;

if (!$sessionId || !$productId) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

// 1. Ambil info produk
$stmtProd = $conn->prepare("SELECT product_name, uom, packname, principal FROM master_products WHERE id_product = ?");
$stmtProd->bind_param("s", $productId);
$stmtProd->execute();
$product = $stmtProd->get_result()->fetch_assoc();
$stmtProd->close();

$productName = $product['product_name'] ?? 'Unknown Product';
$principal = $product['principal'] ?? '-';
$uom = $product['uom'] ?? '-';
$packname = $product['packname'] ?? '-';

// Konversi karton ke pcs: Karton * UOM + Pcs (sama seperti di app Flutter)
$cartonSize = is_numeric($uom) ? max(1, (int)$uom) : 1;

// 2. Ambil histori hitung (Termasuk detail_id dari opname_count_details)
$query = "
    SELECT 
        d.detail_id,
        h.count_id,
        d.id_product,
        h.location_code,
        h.team,
        d.qty_karton,
        d.qty_pcs,
        d.final_qty,
        h.created_at
    FROM opname_count_headers h
    JOIN opname_count_details d ON h.count_id = d.count_id
    WHERE h.session_id = ? AND d.id_product = ?
    ORDER BY h.team, h.location_code
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $sessionId, $productId);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        'detail_id' => (int)$row['detail_id'],
        'count_id' => (int)$row['count_id'],
        'id_product' => $row['id_product'],
        'location' => $row['location_code'],
        'team' => $row['team'],
        'qty_karton' => (int)$row['qty_karton'],
        'qty_pcs' => (int)$row['qty_pcs'],
        'final_qty' => (int)$row['final_qty'],
        'timestamp' => $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-'
    ];
}
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'product' => [
        'id_product' => $productId,
        'product_name' => $productName,
        'principal' => $principal,
        'uom' => $uom,
        'packname' => $packname,
        'carton_size' => $cartonSize
    ],
    'history' => $history
]);
?>
