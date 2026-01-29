<?php
/**
 * Admin kullanıcısı oluşturma scripti
 * Şifre korumalı - Sadece yetkili kişiler erişebilir
 */

session_start();
require_once 'database.php';

header('Content-Type: text/html; charset=utf-8');

// Şifre koruması - Bu şifreyi değiştirin!
$accessPassword = 'Norogastro2024!'; // Bu şifreyi güçlü bir şifre ile değiştirin!

// Şifre kontrolü
$isAuthenticated = isset($_SESSION['create_admin_authenticated']) && $_SESSION['create_admin_authenticated'] === true;

// Şifre girişi kontrolü
if (isset($_POST['access_password'])) {
    if ($_POST['access_password'] === $accessPassword) {
        $_SESSION['create_admin_authenticated'] = true;
        $isAuthenticated = true;
    } else {
        $errorMessage = 'Hatalı şifre!';
    }
}

// Çıkış
if (isset($_GET['logout'])) {
    unset($_SESSION['create_admin_authenticated']);
    $isAuthenticated = false;
    header('Location: create-admin.php');
    exit;
}

// Varsayılan admin bilgileri
$adminEmail = 'admin@norogastro.com';
$adminPassword = 'admin123'; // İlk girişten sonra değiştirin!
$adminName = 'Admin';

// Şifre girişi sayfası
if (!$isAuthenticated) {
    echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Erişim Şifresi</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #2c460a, #4A5230); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); max-width: 400px; width: 100%; }
        h1 { color: #2c460a; text-align: center; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .error { background: #ffebee; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f44336; color: #c62828; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 2px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        input:focus { outline: none; border-color: #2c460a; }
        button { width: 100%; background: #2c460a; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #4A5230; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔒 Erişim Şifresi</h1>
        <p class='subtitle'>Bu sayfaya erişmek için şifre girin</p>";
    
    if (isset($errorMessage)) {
        echo "<div class='error'>{$errorMessage}</div>";
    }
    
    echo "<form method='POST'>
        <label>Şifre:</label>
        <input type='password' name='access_password' required autofocus placeholder='Erişim şifresini girin'>
        <button type='submit'>Giriş Yap</button>
    </form>
    </div>
</body>
</html>";
    exit;
}

// Ana sayfa (şifre doğruysa)
echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Admin Oluştur</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; max-width: 600px; margin: 50px auto; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c460a; }
        .header-actions { text-align: right; margin-bottom: 20px; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .logout-btn:hover { background: #c82333; }
        .info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4caf50; }
        .error { background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #f44336; color: #c62828; }
        .success { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4caf50; color: #2e7d32; }
        .credentials { background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ff9800; }
        .credentials strong { color: #e65100; }
        form { margin-top: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #2c460a; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #4A5230; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header-actions'>
            <a href='?logout=1' class='logout-btn'>Çıkış Yap</a>
        </div>
        <h1>Admin Kullanıcısı Oluştur</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Mevcut admin kontrolü
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE is_admin = 1 LIMIT 1");
    $stmt->execute();
    $existingAdmin = $stmt->fetch();
    
    if ($existingAdmin) {
        echo "<div class='info'>
            <strong>Bilgi:</strong> Zaten bir admin kullanıcısı mevcut.<br>
            E-posta: <strong>{$existingAdmin['email']}</strong>
        </div>";
    }
    
    // Form gönderildi mi?
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $name = $_POST['name'] ?: 'Admin';
        
        if (empty($email) || empty($password)) {
            echo "<div class='error'>E-posta ve şifre gereklidir.</div>";
        } elseif (strlen($password) < 6) {
            echo "<div class='error'>Şifre en az 6 karakter olmalıdır.</div>";
        } else {
            // E-posta kontrolü
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // Mevcut kullanıcıyı admin yap
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1, name = ? WHERE email = ?");
                $result = $stmt->execute([$hashedPassword, $name, $email]);
                
                if ($result) {
                    echo "<div class='success'>
                        <strong>Başarılı!</strong> Mevcut kullanıcı admin yapıldı ve şifresi güncellendi.
                    </div>
                    <div class='credentials'>
                        <strong>Admin Giriş Bilgileri:</strong><br>
                        E-posta: <strong>{$email}</strong><br>
                        Şifre: <strong>{$password}</strong>
                    </div>";
                } else {
                    echo "<div class='error'>Kullanıcı güncellenemedi.</div>";
                }
            } else {
                // Yeni admin kullanıcısı oluştur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (email, password, name, is_admin) VALUES (?, ?, ?, 1)");
                $result = $stmt->execute([$email, $hashedPassword, $name]);
                
                if ($result) {
                    echo "<div class='success'>
                        <strong>Başarılı!</strong> Admin kullanıcısı oluşturuldu.
                    </div>
                    <div class='credentials'>
                        <strong>Admin Giriş Bilgileri:</strong><br>
                        E-posta: <strong>{$email}</strong><br>
                        Şifre: <strong>{$password}</strong>
                    </div>";
                } else {
                    echo "<div class='error'>Kullanıcı oluşturulamadı.</div>";
                }
            }
        }
    }
    
    // Form
    echo "<form method='POST'>
        <label>E-posta:</label>
        <input type='email' name='email' value='{$adminEmail}' required placeholder='admin@norogastro.com'>
        
        <label>Şifre:</label>
        <input type='password' name='password' value='{$adminPassword}' required placeholder='En az 6 karakter' minlength='6'>
        
        <label>Ad Soyad:</label>
        <input type='text' name='name' value='{$adminName}' placeholder='Admin'>
        
        <button type='submit' name='create_admin'>Admin Kullanıcısı Oluştur</button>
    </form>
    
    <div class='info' style='margin-top: 30px;'>
        <strong>Güvenlik:</strong> Bu sayfa şifre korumalıdır. İşiniz bittikten sonra çıkış yapmayı unutmayın!
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>
