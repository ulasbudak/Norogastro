<?php
/**
 * Kullanıcı giriş endpoint'i
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'database.php';
require_once 'session.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST isteği kabul edilir.']);
    exit;
}

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

// Form verisi varsa onu kullan
if (empty($input)) {
    $input = $_POST;
}

// Veri validasyonu
$email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $input['password'] ?? '';

// Validasyon kontrolleri
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir e-posta adresi giriniz.'
    ]);
    exit;
}

if (empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Şifre gereklidir.'
    ]);
    exit;
}

// Database işlemleri
try {
    $db = new Database();
    $result = $db->login($email, $password);

    if ($result['success']) {
        // Kullanıcıyı session'a kaydet
        setUserSession($result['user']);
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Giriş hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Giriş sırasında bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}



