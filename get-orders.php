<?php
require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'session.php';
require_once 'database.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmanız gerekiyor.']);
    exit;
}

try {
    $db = new Database();
    $orders = $db->getUserOrders(getUserId());
    
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    error_log("Sipariş getirme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Siparişler alınırken bir hata oluştu.']);
}
?>
