<?php
/**
 * Kullanıcı kayıt endpoint'i
 */

require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'database.php';
require_once 'session.php';
require_once 'security.php';

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

// Rate limiting kontrolü
$rateLimitKey = 'register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateLimitKey, 3, 600)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Çok fazla kayıt denemesi yaptınız. Lütfen 10 dakika sonra tekrar deneyin.'
    ]);
    exit;
}

// Veri validasyonu
$email = sanitizeInput($input['email'] ?? '', 'email');
$password = $input['password'] ?? '';
$name = isset($input['name']) ? sanitizeInput($input['name'], 'string') : null;
$phone = isset($input['phone']) ? sanitizeInput($input['phone'], 'string') : null;
$company = isset($input['company']) ? sanitizeInput($input['company'], 'string') : null;
$plan = isset($input['plan']) ? sanitizeInput($input['plan'], 'string') : 'duyusal-baslangic';

// Validasyon kontrolleri
$errors = [];

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
}

if (empty($password)) {
    $errors[] = 'Şifre gereklidir.';
} else {
    $passwordErrors = validatePasswordStrength($password);
    if (!empty($passwordErrors)) {
        $errors = array_merge($errors, $passwordErrors);
    }
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

