<?php
// ajax_edit_history.php - Edit record perhitungan spesifik dengan rujukan detail_id
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once "db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

$sessionId = $_POST['session_id'] ?? null;
$detailId = isset($_POST['detail_id']) ? (int)$_POST['detail_id'] : null;
$newProductId = $_POST['new_id_product'] ?? null;
$qtyKarton = isset($_POST['qty_karton']) ? (int)$_POST['qty_karton'] : 0;
$qtyPcs = isset($_POST['qty_pcs']) ? (int)$_POST['qty_pcs'] : 0;
$finalQty = isset($_POST['final_qty']) ? (int)$_POST['final_qty'] : 0;

if (!$sessionId || !$detailId || !$newProductId) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

// 1. Validasi apakah product ID baru terdaftar di master produk
$checkProd = $conn->prepare("SELECT product_name FROM master_products WHERE id_product = ?");
$checkProd->bind_param("s", $newProductId);
$checkProd->execute();
$prodExists = $checkProd->get_result()->fetch_assoc();
$checkProd->close();

if (!$prodExists) {
    echo json_encode(['success' => false, 'message' => 'Product ID tidak terdaftar di master produk']);
    exit;
}

// 2. Ambil info record lama untuk mengetahui count_id dan old_id_product
$checkOld = $conn->prepare("SELECT count_id, id_product FROM opname_count_details WHERE detail_id = ?");
$checkOld->bind_param("i", $detailId);
$checkOld->execute();
$oldRow = $checkOld->get_result()->fetch_assoc();
$checkOld->close();

if (!$oldRow) {
    echo json_encode(['success' => false, 'message' => 'Record asal tidak ditemukan']);
    exit;
}

$countId = (int)$oldRow['count_id'];
$oldProductId = $oldRow['id_product'];

try {
    // 3. Cek duplikasi jika product ID diganti
    if ($newProductId !== $oldProductId) {
        $checkNew = $conn->prepare("SELECT detail_id, qty_karton, qty_pcs, final_qty FROM opname_count_details WHERE count_id = ? AND id_product = ?");
        $checkNew->bind_param("is", $countId, $newProductId);
        $checkNew->execute();
        $existingNew = $checkNew->get_result()->fetch_assoc();
        $checkNew->close();

        if ($existingNew) {
            // MERGE: Tambahkan qty ke record yang sudah ada
            $mergedKarton = $existingNew['qty_karton'] + $qtyKarton;
            $mergedPcs = $existingNew['qty_pcs'] + $qtyPcs;
            $mergedFinal = $existingNew['final_qty'] + $finalQty;
            $targetDetailId = (int)$existingNew['detail_id'];

            // Update record tujuan
            $updateNew = $conn->prepare("UPDATE opname_count_details SET qty_karton = ?, qty_pcs = ?, final_qty = ? WHERE detail_id = ?");
            $updateNew->bind_param("iiii", $mergedKarton, $mergedPcs, $mergedFinal, $targetDetailId);
            $updateNew->execute();
            $updateNew->close();

            // Hapus record asal yang sedang diedit
            $deleteOld = $conn->prepare("DELETE FROM opname_count_details WHERE detail_id = ?");
            $deleteOld->bind_param("i", $detailId);
            $deleteOld->execute();
            $deleteOld->close();

            echo json_encode(['success' => true, 'message' => 'Product ID digabungkan karena sudah ada record untuk produk ini']);
            exit;
        }
    }

    // Normal Update menggunakan detail_id
    $updateStmt = $conn->prepare("UPDATE opname_count_details SET id_product = ?, qty_karton = ?, qty_pcs = ?, final_qty = ? WHERE detail_id = ?");
    $updateStmt->bind_param("siiii", $newProductId, $qtyKarton, $qtyPcs, $finalQty, $detailId);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate database: ' . $updateStmt->error]);
    }
    $updateStmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
