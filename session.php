<?php
/**
 * Session yönetimi dosyası
 */

// Güvenli session başlat
if (session_status() === PHP_SESSION_NONE) {
    // Session cookie güvenlik ayarları
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Session başlat
    session_start();
    
    // Session hijacking koruması
    if (!isset($_SESSION['ip_address'])) {
        $_SESSION['ip_address'] = getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } else {
        // IP veya User-Agent değiştiyse session'ı temizle
        $currentIP = getClientIP();
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if ($_SESSION['ip_address'] !== $currentIP || $_SESSION['user_agent'] !== $currentUA) {
            session_destroy();
            session_start();
            $_SESSION['ip_address'] = $currentIP;
            $_SESSION['user_agent'] = $currentUA;
        }
    }
}

/**
 * Client IP adresini al (proxy arkasında güvenli)
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
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Giriş yapan kullanıcının ID'sini getir
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Kullanıcı bilgilerini session'a kaydet
 */
function setUserSession($user) {
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
    $_SESSION['user_name'] = isset($user['name']) ? htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') : null;
    $_SESSION['user_plan'] = htmlspecialchars($user['plan'] ?? 'duyusal-baslangic', ENT_QUOTES, 'UTF-8');
    $_SESSION['is_admin'] = isset($user['is_admin']) && $user['is_admin'] == 1;
    $_SESSION['login_time'] = time();
}

/**
 * Kullanıcı oturumunu kapat
 */
function logout() {
    $_SESSION = array();
    
    // Cookie'yi sil
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Login gerektiren sayfalarda kullanıcıyı yönlendir
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: uyelik-giris.html');
        exit;
    }
}

/**
 * Admin kontrolü
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Admin gerektiren sayfalarda kontrol
 */
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: uyelik-giris.html');
        exit;
    }
}



