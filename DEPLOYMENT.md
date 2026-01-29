# Norogastro - Hosting Rehberi

## Hosting Seçenekleri

### 1. **Türk Hosting Firmaları (Önerilen)**
- **Turhost** - https://www.turhost.com
- **Natro** - https://www.natro.com
- **Hosting.com.tr** - https://www.hosting.com.tr
- **İsimtescil** - https://www.isimtescil.net

**Avantajları:**
- Türkçe destek
- Türk Lirası ile ödeme
- Yerel sunucular (hızlı)
- PHP + SQLite desteği

**Fiyat:** Aylık 20-50 TL arası

### 2. **Uluslararası Hosting**
- **Hostinger** - https://www.hostinger.com.tr
- **Bluehost** - https://www.bluehost.com
- **SiteGround** - https://www.siteground.com

**Fiyat:** Aylık $3-10 arası

### 3. **Ücretsiz Hosting (Test için)**
- **000webhost** - https://www.000webhost.com
- **InfinityFree** - https://www.infinityfree.net

**Not:** Ücretsiz hosting'lerde bazı kısıtlamalar olabilir.

## Hosting Gereksinimleri

✅ **PHP 7.4 veya üzeri**
✅ **SQLite desteği**
✅ **PDO extension**
✅ **Session desteği**
✅ **En az 100 MB disk alanı**
✅ **SSL sertifikası (HTTPS)**

## Deployment Adımları

### 1. Dosyaları Hazırlama

**Production'da silinmesi gereken dosyalar:**
- `create-admin.php` (veya şifre korumalı bırakın)
- `view-users.php` (geliştirme için)
- `test.html` (test dosyası)
- `database.db` (sunucuda otomatik oluşacak)

**Dosya izinleri:**
- Tüm dosyalar: `644` (okuma/yazma)
- Klasörler: `755` (okuma/yazma/çalıştırma)
- `database.db` dosyası: `666` (okuma/yazma - sunucuda otomatik oluşacak)

### 2. FTP ile Yükleme

1. **FTP Bilgilerini Alın:**
   - FTP Host: `ftp.yourdomain.com` veya IP adresi
   - FTP Kullanıcı Adı: Hosting firmasından aldığınız kullanıcı adı
   - FTP Şifre: Hosting firmasından aldığınız şifre
   - Port: Genellikle `21`

2. **FTP İstemcisi Kullanın:**
   - **FileZilla** (Ücretsiz): https://filezilla-project.org
   - **WinSCP** (Windows): https://winscp.net
   - **Cyberduck** (Mac): https://cyberduck.io

3. **Dosyaları Yükleyin:**
   - Tüm dosyaları `public_html` veya `www` klasörüne yükleyin
   - `database.db` dosyasını yüklemeyin (sunucuda otomatik oluşacak)

### 3. Veritabanı Ayarları

SQLite kullanıldığı için ekstra veritabanı kurulumu gerekmez. `database.db` dosyası ilk kullanımda otomatik oluşacak.

**Önemli:** `database.db` dosyasının bulunduğu klasör yazılabilir olmalı (chmod 666 veya 777).

### 4. Domain Ayarları

1. **Domain'i Hosting'e Bağlayın:**
   - Hosting panelinden domain ekleyin
   - DNS ayarlarını domain sağlayıcınızdan yapın

2. **SSL Sertifikası:**
   - Let's Encrypt (ücretsiz) kullanın
   - Hosting panelinden SSL aktif edin

### 5. İlk Kurulum

1. **Admin Kullanıcısı Oluşturun:**
   - `https://yourdomain.com/create-admin.php` adresine gidin
   - Erişim şifresini girin
   - Admin kullanıcısı oluşturun

2. **Test Edin:**
   - Ana sayfa: `https://yourdomain.com`
   - Üye kayıt: `https://yourdomain.com/uyelik.html`
   - Admin paneli: `https://yourdomain.com/admin-panel.html`

## Güvenlik Kontrol Listesi

- [ ] `create-admin.php` şifre korumalı (✓ Zaten yapıldı)
- [ ] `view-users.php` silindi veya şifre korumalı
- [ ] `database.db` dosyası `.htaccess` ile korunmalı
- [ ] PHP hata mesajları production'da kapalı
- [ ] SSL sertifikası aktif
- [ ] Güçlü admin şifreleri kullanıldı
- [ ] Düzenli yedekleme yapılıyor

## Yedekleme

**Önemli:** Düzenli yedekleme yapın!

1. **FTP ile manuel yedekleme:**
   - `database.db` dosyasını indirin
   - Tüm dosyaları yedekleyin

2. **Otomatik yedekleme:**
   - Hosting panelinden otomatik yedekleme ayarlayın
   - Haftalık veya günlük yedekleme önerilir

## Sorun Giderme

### SQLite Yazma Hatası
**Hata:** "unable to open database file"
**Çözüm:** `database.db` dosyasının bulunduğu klasör yazılabilir olmalı (chmod 666)

### Session Çalışmıyor
**Hata:** Giriş yapılamıyor
**Çözüm:** PHP session ayarlarını kontrol edin, `session.php` dosyasını kontrol edin

### 404 Hatası
**Hata:** Sayfa bulunamadı
**Çözüm:** `.htaccess` dosyasını kontrol edin, dosya yollarını kontrol edin

## Destek

Sorun yaşarsanız:
1. Hosting firmanızın destek ekibine başvurun
2. PHP hata loglarını kontrol edin
3. Tarayıcı konsolunu kontrol edin (F12)
