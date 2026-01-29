<?php
/**
 * Admin giriş endpoint'i
 */

require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'session.php';
require_once 'database.php';
require_once 'security.php';

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'E-posta ve şifre gereklidir.']);
    exit;
}

// Rate limiting kontrolü (admin için daha sıkı)
$rateLimitKey = 'admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateLimitKey, 3, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Çok fazla deneme yaptınız. Lütfen 10 dakika sonra tekrar deneyin.']);
    exit;
}

$email = sanitizeInput($input['email'], 'email');
$password = $input['password'];

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'E-posta ve şifre boş olamaz.']);
    exit;
}

try {
    $db = new Database();
    $result = $db->login($email, $password);
    
    if ($result['success']) {
        $user = $result['user'];
        
        // Admin kontrolü
        if (!isset($user['is_admin']) || $user['is_admin'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Bu sayfaya erişim yetkiniz yok.']);
            exit;
        }
        
        // Session'a kaydet
        setUserSession($user);
        
        echo json_encode([
            'success' => true,
            'message' => 'Admin girişi başarılı!',
            'user' => $user
        ]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Admin giriş hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Giriş sırasında bir hata oluştu.']);
}
?>
