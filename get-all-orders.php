<?php
/**
 * Tüm siparişleri getir (Admin için)
 */

require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'session.php';
require_once 'database.php';

// Admin kontrolü
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Yetkiniz yok.']);
    exit;
}

// Sipariş durumu filtresi (aktif/pasif)
$orderStatus = isset($_GET['status']) ? $_GET['status'] : null;

try {
    $db = new Database();
    $orders = $db->getAllOrders($orderStatus);
    
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    error_log("Sipariş listesi getirme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Siparişler alınırken bir hata oluştu.']);
}
?>
