<?php
/**
 * Kullanıcıları görüntüleme scripti (Sadece geliştirme için)
 * NOT: Production'da bu dosyayı silin veya şifreleyin!
 */

require_once 'database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Kullanıcı Listesi</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #2c460a; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Kullanıcı Listesi</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT id, email, name, phone, company, plan, created_at, last_login FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p>Henüz kullanıcı kaydı yok.</p>";
    } else {
        echo "<table>
            <tr>
                <th>ID</th>
                <th>E-posta</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>Şirket</th>
                <th>Plan</th>
                <th>Kayıt Tarihi</th>
                <th>Son Giriş</th>
            </tr>";
        
        foreach ($users as $user) {
            echo "<tr>
                <td>{$user['id']}</td>
                <td>{$user['email']}</td>
                <td>" . ($user['name'] ?: '-') . "</td>
                <td>" . ($user['phone'] ?: '-') . "</td>
                <td>" . ($user['company'] ?: '-') . "</td>
                <td>{$user['plan']}</td>
                <td>{$user['created_at']}</td>
                <td>" . ($user['last_login'] ?: '-') . "</td>
            </tr>";
        }
        
        echo "</table>";
    }
    
    echo "<hr>
    <h2>Şifre Sıfırlama</h2>
    <form method='POST' style='margin-top: 20px;'>
        <p>
            <label>E-posta:</label><br>
            <input type='email' name='email' required style='padding: 8px; width: 300px;'>
        </p>
        <p>
            <label>Yeni Şifre:</label><br>
            <input type='password' name='new_password' required style='padding: 8px; width: 300px;' minlength='6'>
        </p>
        <p>
            <button type='submit' style='padding: 10px 20px; background: #2c460a; color: white; border: none; cursor: pointer;'>Şifreyi Sıfırla</button>
        </p>
    </form>";
    
    // Şifre sıfırlama işlemi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['new_password'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $newPassword = $_POST['new_password'];
        
        if (strlen($newPassword) < 6) {
            echo "<p style='color: red;'>Şifre en az 6 karakter olmalıdır.</p>";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $result = $stmt->execute([$hashedPassword, $email]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo "<p style='color: green;'>Şifre başarıyla güncellendi!</p>";
            } else {
                echo "<p style='color: red;'>E-posta bulunamadı.</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
