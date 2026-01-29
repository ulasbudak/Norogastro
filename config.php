<?php
/**
 * Site yapılandırma dosyası
 */

// Site ayarları
define('SITE_URL', 'https://yourdomain.com'); // Ana domain (yönlendirme vb. için)
define('SITE_NAME', 'Norogastro');

// İsteğe bağlı: İkinci domain veya ek domain'ler (CORS için).
// İki domain de aynı siteyi gösterecekse buraya ekleyin. Boş bırakırsanız sadece SITE_URL (+ www) kullanılır.
define('ADDITIONAL_ALLOWED_ORIGINS', [
    // 'https://ikinci-domain.com',
    // 'https://www.ikinci-domain.com',
]);

// Environment (development veya production)
define('ENVIRONMENT', 'development'); // Production'da 'production' yapın

// CORS ayarları
function setCORSHeaders() {
    $allowedOrigins = [];
    
    if (ENVIRONMENT === 'production') {
        // Production: Ana domain + www + ek domain'ler
        $host = parse_url(SITE_URL, PHP_URL_HOST);
        $scheme = parse_url(SITE_URL, PHP_URL_SCHEME);
        $allowedOrigins = [
            SITE_URL,
            $scheme . '://www.' . $host, // www versiyonu
        ];
        if (defined('ADDITIONAL_ALLOWED_ORIGINS') && is_array(ADDITIONAL_ALLOWED_ORIGINS)) {
            $allowedOrigins = array_merge($allowedOrigins, ADDITIONAL_ALLOWED_ORIGINS);
            $allowedOrigins = array_unique(array_filter($allowedOrigins));
        }
    } else {
        // Development: Localhost ve test domain'lerine izin ver
        $allowedOrigins = [
            'http://localhost:8000',
            'http://localhost:8080',
            'http://127.0.0.1:8000',
            'http://127.0.0.1:8080',
            SITE_URL, // Production URL'i de ekle (test için)
        ];
    }
    
    // İstek yapan domain'i kontrol et
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } elseif (ENVIRONMENT === 'development') {
        // Development'ta tüm origin'lere izin ver (güvenlik riski, sadece development için)
        header('Access-Control-Allow-Origin: *');
    } else {
        // Production'da izin verilmeyen origin'den istek gelirse
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CORS policy violation']);
        exit;
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // 24 saat
    
    // OPTIONS isteği için hemen cevap ver (preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Güvenlik ayarları
define('SESSION_LIFETIME', 3600); // 1 saat
define('MAX_LOGIN_ATTEMPTS', 5);
define('MAX_REGISTER_ATTEMPTS', 3);
define('RATE_LIMIT_WINDOW', 300); // 5 dakika

// Database ayarları
define('DB_PATH', __DIR__ . '/database.db');

// Hata raporlama (production'da kapalı olmalı)
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}
