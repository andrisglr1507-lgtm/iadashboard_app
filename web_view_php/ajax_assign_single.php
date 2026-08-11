<?php
// ajax_assign_single.php - Assignment handler untuk mode Single
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

$sessionId = $_POST['session_id'] ?? null;
$assignedTo = $_POST['assigned_to'] ?? null;
$selectedProductsRaw = $_POST['selected_products'] ?? null;
$assignedBy = $_POST['assigned_by'] ?? 1;

if (is_string($selectedProductsRaw)) {
    $selectedProducts = json_decode($selectedProductsRaw, true);
} else {
    $selectedProducts = $selectedProductsRaw ?? [];
}

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Session ID tidak ditemukan']);
    exit;
}

if (!$assignedTo) {
    echo json_encode(['success' => false, 'message' => 'Pilih user terlebih dahulu']);
    exit;
}

if (empty($selectedProducts)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang dipilih']);
    exit;
}

$inserted = 0;
$skipped = 0;

foreach ($selectedProducts as $product) {
    // Format: "productId|roundNumber"
    $parts = explode('|', $product);
    if (count($parts) !== 2) {
        $skipped++;
        continue;
    }
    
    $productId = $parts[0];
    $roundNumber = (int)$parts[1];
    
    if (!in_array($roundNumber, [2, 3])) {
        $skipped++;
        continue;
    }
    
    // Cari semua lokasi unik dimana produk ini di-opname dalam session ini
    $locQuery = $conn->prepare("
        SELECT DISTINCT h.location_code 
        FROM opname_count_headers h
        JOIN opname_count_details d ON h.count_id = d.count_id
        WHERE h.session_id = ? AND d.id_product = ?
    ");
    $locQuery->bind_param("ss", $sessionId, $productId);
    $locQuery->execute();
    $locResult = $locQuery->get_result();
    
    $locations = [];
    while ($row = $locResult->fetch_assoc()) {
        $locations[] = $row['location_code'];
    }
    $locQuery->close();
    
    // Fallback jika tidak ada record scan (cari bin_location session)
    if (empty($locations)) {
        $sessQuery = $conn->prepare("SELECT bin_location FROM opname_sessions WHERE session_id = ?");
        $sessQuery->bind_param("s", $sessionId);
        $sessQuery->execute();
        $sess = $sessQuery->get_result()->fetch_assoc();
        $sessQuery->close();
        
        $locations[] = $sess['bin_location'] ?? '-';
    }
    
    foreach ($locations as $locationCode) {
        // Cek duplikat
        $checkStmt = $conn->prepare("
            SELECT assignment_id FROM opname_recount_assignments 
            WHERE session_id = ? AND id_product = ? AND location_code = ? AND round_number = ?
        ");
        $checkStmt->bind_param("sssi", $sessionId, $productId, $locationCode, $roundNumber);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        
        if ($existing) {
            $skipped++;
            continue;
        }
        
        // Cari previous assignment untuk R2
        $previousId = null;
        if ($roundNumber == 3) {
            $prevStmt = $conn->prepare("
                SELECT assignment_id FROM opname_recount_assignments 
                WHERE session_id = ? AND id_product = ? AND location_code = ? AND round_number = 2
            ");
            $prevStmt->bind_param("sss", $sessionId, $productId, $locationCode);
            $prevStmt->execute();
            $prev = $prevStmt->get_result()->fetch_assoc();
            if ($prev) {
                $previousId = $prev['assignment_id'];
            }
            $prevStmt->close();
        }
        
        // Insert
        $insertStmt = $conn->prepare("
            INSERT INTO opname_recount_assignments 
            (session_id, location_code, id_product, round_number, assigned_to, assigned_by, status, assigned_at, is_final, previous_assignment_id)
            VALUES (?, ?, ?, ?, ?, ?, 'assigned', NOW(), 0, ?)
        ");
        
        $insertStmt->bind_param("sssiiii", $sessionId, $locationCode, $productId, $roundNumber, $assignedTo, $assignedBy, $previousId);
        
        if ($insertStmt->execute()) {
            $inserted++;
        } else {
            $skipped++;
            error_log("Insert error: " . $insertStmt->error);
        }
        $insertStmt->close();
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => "Berhasil assign {$inserted} tugas recount" . ($skipped > 0 ? ", {$skipped} gagal/skip" : ""),
    'inserted' => $inserted,
    'skipped' => $skipped
]);
?>
