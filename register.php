<?php
/**
 * Kullanıcı kayıt endpoint'i
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
$name = isset($input['name']) ? trim($input['name']) : null;
$phone = isset($input['phone']) ? trim($input['phone']) : null;
$company = isset($input['company']) ? trim($input['company']) : null;
$plan = isset($input['plan']) ? trim($input['plan']) : 'baslangic';

// Validasyon kontrolleri
$errors = [];

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = 'Şifre en az 6 karakter olmalıdır.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Database işlemleri
try {
    $db = new Database();
    $result = $db->register($email, $password, $name, $phone, $company, $plan);

    if ($result['success']) {
        // Kullanıcıyı otomatik giriş yaptır
        $loginResult = $db->login($email, $password);
        if ($loginResult['success']) {
            setUserSession($loginResult['user']);
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Kayıt hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}

