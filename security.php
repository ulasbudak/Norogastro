<?php
/**
 * Güvenlik fonksiyonları
 */

/**
 * XSS koruması - HTML output için
 */
function escapeHtml($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF token oluştur
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting - Brute force koruması
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    $rateLimitFile = sys_get_temp_dir() . '/ratelimit_' . md5($key) . '.txt';
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Süre dolmuşsa sıfırla
        if (time() - $data['time'] > $timeWindow) {
            $data = ['attempts' => 0, 'time' => time()];
        }
        
        // Maksimum deneme sayısını kontrol et
        if ($data['attempts'] >= $maxAttempts) {
            return false; // Çok fazla deneme
        }
        
        $data['attempts']++;
    } else {
        $data = ['attempts' => 1, 'time' => time()];
    }
    
    file_put_contents($rateLimitFile, json_encode($data));
    return true;
}

/**
 * Input sanitization
 */
function sanitizeInput($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
        case 'string':
        default:
            return filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }
}

/**
 * Şifre güçlülük kontrolü
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Şifre en az 8 karakter olmalıdır.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Şifre en az bir büyük harf içermelidir.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Şifre en az bir küçük harf içermelidir.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Şifre en az bir rakam içermelidir.';
    }
    
    return $errors;
}

/**
 * IP adresini al (proxy arkasında güvenli)
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
               'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Güvenli session ayarları
 */
function secureSession() {
    // Session cookie güvenlik ayarları
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Session hijacking koruması
    if (!isset($_SESSION['ip_address'])) {
        $_SESSION['ip_address'] = getClientIP();
    } elseif ($_SESSION['ip_address'] !== getClientIP()) {
        // IP değişti, session'ı temizle
        session_destroy();
        session_start();
        return false;
    }
    
    return true;
}

/**
 * SQL Injection koruması için prepared statement kontrolü
 */
function validatePreparedStatement($stmt) {
    if (!$stmt) {
        throw new Exception('Prepared statement oluşturulamadı.');
    }
    return true;
}

/**
 * Dosya yükleme güvenliği (gelecekte kullanım için)
 */
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
    $errors = [];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Geçersiz dosya yükleme.';
        return $errors;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Dosya yükleme hatası.';
        return $errors;
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = 'Dosya boyutu çok büyük.';
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'İzin verilmeyen dosya türü.';
    }
    
    return $errors;
}
