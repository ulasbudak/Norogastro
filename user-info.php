<?php
/**
 * Kullanıcı bilgilerini getiren endpoint
 */

require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'database.php';
require_once 'session.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece GET isteği kabul edilir.']);
    exit;
}

// Kullanıcı giriş kontrolü
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Giriş yapmanız gerekiyor.'
    ]);
    exit;
}

// Kullanıcı bilgilerini getir
try {
    $db = new Database();
    $userId = getUserId();
    $user = $db->getUserById($userId);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı bulunamadı.'
        ]);
    }
} catch (Exception $e) {
    error_log("Kullanıcı bilgisi getirme hatası: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}
