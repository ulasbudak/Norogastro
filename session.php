<?php
/**
 * Session yönetimi dosyası
 */

session_start();

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
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'] ?? null;
    $_SESSION['user_plan'] = $user['plan'] ?? 'baslangic';
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



