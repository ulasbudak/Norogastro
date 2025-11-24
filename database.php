<?php
/**
 * Database bağlantı ve yönetim dosyası
 * SQLite kullanarak basit ve hafif bir database çözümü
 */

class Database {
    private $db;
    private $dbPath = __DIR__ . '/database.db';

    public function __construct() {
        try {
            $this->db = new PDO('sqlite:' . $this->dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->createTables();
        } catch (PDOException $e) {
            error_log("Database bağlantı hatası: " . $e->getMessage());
            die("Database bağlantı hatası oluştu.");
        }
    }

    /**
     * Users tablosunu oluştur
     */
    private function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT,
            phone TEXT,
            company TEXT,
            plan TEXT DEFAULT 'baslangic',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            is_active INTEGER DEFAULT 1
        )";

        $this->db->exec($sql);

        // Index oluştur
        $indexSql = "CREATE INDEX IF NOT EXISTS idx_email ON users(email)";
        $this->db->exec($indexSql);
    }

    /**
     * Kullanıcı kayıt
     */
    public function register($email, $password, $name = null, $phone = null, $company = null, $plan = 'baslangic') {
        try {
            // E-posta kontrolü
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı.'];
            }

            // Şifreyi hash'le
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Kullanıcıyı ekle
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, name, phone, company, plan) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$email, $hashedPassword, $name, $phone, $company, $plan]);

            if ($result) {
                return [
                    'success' => true, 
                    'message' => 'Kayıt başarılı!',
                    'user_id' => $this->db->lastInsertId()
                ];
            }

            return ['success' => false, 'message' => 'Kayıt sırasında bir hata oluştu.'];
        } catch (PDOException $e) {
            error_log("Kayıt hatası: " . $e->getMessage());
            return ['success' => false, 'message' => 'Kayıt sırasında bir hata oluştu.'];
        }
    }

    /**
     * Kullanıcı girişi
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Son giriş zamanını güncelle
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                // Şifreyi döndürmeyelim
                unset($user['password']);
                
                return [
                    'success' => true,
                    'message' => 'Giriş başarılı!',
                    'user' => $user
                ];
            }

            return ['success' => false, 'message' => 'E-posta veya şifre hatalı.'];
        } catch (PDOException $e) {
            error_log("Giriş hatası: " . $e->getMessage());
            return ['success' => false, 'message' => 'Giriş sırasında bir hata oluştu.'];
        }
    }

    /**
     * Kullanıcı bilgilerini getir
     */
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, email, name, phone, company, plan, created_at, last_login FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Kullanıcı getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Database bağlantısını döndür
     */
    public function getConnection() {
        return $this->db;
    }
}

