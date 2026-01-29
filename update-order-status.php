<?php
/**
 * Sipariş durumunu güncelle (Admin için)
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

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !isset($input['order_status'])) {
    echo json_encode(['success' => false, 'message' => 'Sipariş ID ve durum gereklidir.']);
    exit;
}

$orderId = (int) $input['order_id'];
$orderStatus = $input['order_status']; // 'active' veya 'inactive'

try {
    $db = new Database();
    $result = $db->toggleOrderStatus($orderId, $orderStatus);
    
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Sipariş durumu güncelleme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sipariş durumu güncellenirken bir hata oluştu.']);
}
?>
