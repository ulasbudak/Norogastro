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
     * Users ve Orders tablolarını oluştur
     */
    private function createTables() {
        // Users tablosu
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT,
            phone TEXT,
            company TEXT,
            plan TEXT DEFAULT 'duyusal-baslangic',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            is_active INTEGER DEFAULT 1,
            is_admin INTEGER DEFAULT 0
        )";

        $this->db->exec($sql);
        
        // Mevcut tabloya is_admin kolonunu ekle (eğer yoksa)
        try {
            $this->db->exec("ALTER TABLE users ADD COLUMN is_admin INTEGER DEFAULT 0");
        } catch (PDOException $e) {
            // Kolon zaten varsa hata vermesin
        }

        // Orders tablosu
        $ordersSql = "CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            order_number TEXT UNIQUE NOT NULL,
            items TEXT NOT NULL,
            total_amount REAL NOT NULL,
            payment_method TEXT,
            payment_status TEXT DEFAULT 'pending',
            order_status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";

        $this->db->exec($ordersSql);

        // Index oluştur
        $indexSql = "CREATE INDEX IF NOT EXISTS idx_email ON users(email)";
        $this->db->exec($indexSql);
        
        $orderIndexSql = "CREATE INDEX IF NOT EXISTS idx_user_id ON orders(user_id)";
        $this->db->exec($orderIndexSql);
        
        $orderNumberIndexSql = "CREATE INDEX IF NOT EXISTS idx_order_number ON orders(order_number)";
        $this->db->exec($orderNumberIndexSql);
    }

    /**
     * Kullanıcı kayıt
     */
    public function register($email, $password, $name = null, $phone = null, $company = null, $plan = 'duyusal-baslangic') {
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
            $stmt = $this->db->prepare("SELECT id, email, name, phone, company, plan, created_at, last_login, is_admin FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Kullanıcı getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sipariş oluştur
     */
    public function createOrder($userId, $items, $totalAmount, $paymentMethod = 'iyzico') {
        try {
            // Benzersiz sipariş numarası oluştur
            $orderNumber = 'NORO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
            
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, order_number, items, total_amount, payment_method, payment_status, order_status) 
                VALUES (?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            
            $itemsJson = json_encode($items);
            $result = $stmt->execute([$userId, $orderNumber, $itemsJson, $totalAmount, $paymentMethod]);
            
            if ($result) {
                return [
                    'success' => true,
                    'order_id' => $this->db->lastInsertId(),
                    'order_number' => $orderNumber
                ];
            }
            
            return ['success' => false, 'message' => 'Sipariş oluşturulamadı.'];
        } catch (PDOException $e) {
            error_log("Sipariş oluşturma hatası: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sipariş oluşturulurken bir hata oluştu.'];
        }
    }
    
    /**
     * Sipariş durumunu güncelle
     */
    public function updateOrderStatus($orderId, $paymentStatus, $orderStatus = null) {
        try {
            if ($orderStatus) {
                $stmt = $this->db->prepare("
                    UPDATE orders 
                    SET payment_status = ?, order_status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$paymentStatus, $orderStatus, $orderId]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE orders 
                    SET payment_status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$paymentStatus, $orderId]);
            }
            
            return $result ? ['success' => true] : ['success' => false];
        } catch (PDOException $e) {
            error_log("Sipariş güncelleme hatası: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Kullanıcının siparişlerini getir
     */
    public function getUserOrders($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $orders = $stmt->fetchAll();
            
            // JSON items'ı decode et
            foreach ($orders as &$order) {
                $order['items'] = json_decode($order['items'], true);
            }
            
            return $orders;
        } catch (PDOException $e) {
            error_log("Sipariş getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Sipariş detayını getir
     */
    public function getOrderById($orderId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                $order['items'] = json_decode($order['items'], true);
            }
            
            return $order ?: null;
        } catch (PDOException $e) {
            error_log("Sipariş detay hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tüm kullanıcıları getir (Admin için)
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, phone, company, plan, created_at, last_login, is_active, is_admin 
                FROM users 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Kullanıcı listesi getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Tüm siparişleri getir (Admin için)
     */
    public function getAllOrders($orderStatus = null) {
        try {
            if ($orderStatus) {
                $stmt = $this->db->prepare("
                    SELECT o.*, u.email, u.name, u.phone, u.company 
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.order_status = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$orderStatus]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT o.*, u.email, u.name, u.phone, u.company 
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute();
            }
            
            $orders = $stmt->fetchAll();
            
            // JSON items'ı decode et
            foreach ($orders as &$order) {
                $order['items'] = json_decode($order['items'], true);
            }
            
            return $orders;
        } catch (PDOException $e) {
            error_log("Sipariş listesi getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Sipariş durumunu güncelle (aktif/pasif)
     */
    public function toggleOrderStatus($orderId, $orderStatus) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET order_status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $result = $stmt->execute([$orderStatus, $orderId]);
            
            return $result ? ['success' => true] : ['success' => false, 'message' => 'Sipariş durumu güncellenemedi.'];
        } catch (PDOException $e) {
            error_log("Sipariş durumu güncelleme hatası: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sipariş durumu güncellenirken bir hata oluştu.'];
        }
    }

    /**
     * Database bağlantısını döndür
     */
    public function getConnection() {
        return $this->db;
    }
}

