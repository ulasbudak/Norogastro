<?php
/**
 * Tüm kullanıcıları getir (Admin için)
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

try {
    $db = new Database();
    $users = $db->getAllUsers();
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    error_log("Kullanıcı listesi getirme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Kullanıcılar alınırken bir hata oluştu.']);
}
?>
