<?php
// ajax_delete_history.php - Hapus record perhitungan spesifik dengan rujukan detail_id
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

if (!$sessionId || !$detailId) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM opname_count_details WHERE detail_id = ?");
    $stmt->bind_param("i", $detailId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record perhitungan berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus record: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
