# ⚡ Hızlı Kurulum - Paylaşımlı Hosting

## 🎯 Senaryo
Mevcut siteniz `public_html` klasöründe çalışıyor. Norogastro'yu alt klasörde host edeceksiniz.

## 📝 5 Adımda Kurulum

### 1️⃣ FTP ile Bağlanın
- FileZilla indirin: https://filezilla-project.org
- Hosting bilgilerinizle bağlanın

### 2️⃣ Klasör Oluşturun
```
public_html/
  └── norogastro/  ← Bu klasörü oluşturun
```

### 3️⃣ Dosyaları Yükleyin
- Tüm Norogastro dosyalarını `public_html/norogastro/` klasörüne yükleyin
- **ÖNEMLİ:** `database.db` dosyasını yüklemeyin (otomatik oluşacak)

### 4️⃣ Config Dosyasını Düzenleyin
`config.php` dosyasını açın ve şunu değiştirin:

```php
// ÖNCE (development):
define('SITE_URL', 'https://yourdomain.com');
define('ENVIRONMENT', 'development');

// SONRA (production - alt klasör):
define('SITE_URL', 'https://yourdomain.com/norogastro'); // ⬅️ /norogastro ekleyin
define('ENVIRONMENT', 'production'); // ⬅️ production yapın
```

### 5️⃣ .htaccess Düzenleyin (Alt Klasör İçin)
`.htaccess` dosyasını açın ve şu satırı bulun:

```apache
# RewriteBase /
```

Şunu yapın:

```apache
# RewriteBase /norogastro/  ← Yorumu kaldırın ve /norogastro/ yazın
```

## ✅ Test Edin

Site şu adresten erişilebilir olmalı:
```
https://yourdomain.com/norogastro/
```

## 🔧 Alt Domain Kullanmak İsterseniz

### cPanel'den:
1. "Subdomains" bölümüne gidin
2. `norogastro` alt domain'i oluşturun
3. Klasör: `public_html/norogastro` seçin
4. Dosyaları yükleyin
5. `config.php`'de: `https://norogastro.yourdomain.com` yazın

## ⚠️ Önemli Notlar

1. **Database:** İlk kullanımda otomatik oluşur, klasör yazılabilir olmalı
2. **SSL:** HTTPS aktif olmalı
3. **CORS:** `config.php` mutlaka güncellenmeli
4. **Yedekleme:** Düzenli yedekleme yapın

## 🆘 Sorun mu Var?

- **404 Hatası:** `.htaccess` dosyasını kontrol edin
- **Database Hatası:** Klasör izinlerini kontrol edin (chmod 755)
- **CORS Hatası:** `config.php` dosyasını kontrol edin

Detaylı rehber: `SHARED_HOSTING_SETUP.md`
