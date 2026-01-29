Peki# Paylaşımlı Hosting Kurulum Rehberi

## 📁 Seçenek 1: Alt Klasörde Host Etmek (Önerilen)

Mevcut siteniz `public_html` klasöründe çalışıyorsa, Norogastro'yu alt klasörde host edebilirsiniz.

### Adımlar:

#### 1. FTP ile Bağlanın
- FileZilla veya başka bir FTP istemcisi kullanın
- Hosting firmanızdan aldığınız FTP bilgileriyle bağlanın

#### 2. Alt Klasör Oluşturun
```
public_html/
  ├── mevcut-siteniz/ (zaten var)
  └── norogastro/ (yeni klasör oluşturun)
```

#### 3. Dosyaları Yükleyin
- Tüm Norogastro dosyalarını `public_html/norogastro/` klasörüne yükleyin
- **ÖNEMLİ:** `database.db` dosyasını yüklemeyin (sunucuda otomatik oluşacak)

#### 4. Erişim URL'i
Site şu adresten erişilebilir olacak:
```
https://yourdomain.com/norogastro/
```

#### 5. Config Dosyasını Güncelleyin
`config.php` dosyasını düzenleyin:
```php
define('SITE_URL', 'https://yourdomain.com/norogastro');
define('ENVIRONMENT', 'production');
```

---

## 🌐 Seçenek 2: Alt Domain (Subdomain) Kullanmak

Daha profesyonel görünüm için alt domain kullanabilirsiniz.

### Adımlar:

#### 1. Hosting Panelinden Alt Domain Oluşturun
- cPanel veya hosting panelinize giriş yapın
- "Subdomains" veya "Alt Domainler" bölümüne gidin
- Yeni alt domain oluşturun: `norogastro.yourdomain.com`
- Klasör olarak: `public_html/norogastro` seçin

#### 2. Dosyaları Yükleyin
- Tüm dosyaları `public_html/norogastro/` klasörüne yükleyin

#### 3. Erişim URL'i
Site şu adresten erişilebilir olacak:
```
https://norogastro.yourdomain.com
```

#### 4. Config Dosyasını Güncelleyin
`config.php` dosyasını düzenleyin:
```php
define('SITE_URL', 'https://norogastro.yourdomain.com');
define('ENVIRONMENT', 'production');
```

---

## 🔧 Alt Klasör için Özel Ayarlar

### .htaccess Dosyası Güncellemesi

Alt klasörde host ediyorsanız, `.htaccess` dosyasına şunu ekleyin:

```apache
# Alt klasör için base path ayarı
RewriteBase /norogastro/

# Eğer index.html'e yönlendirme istiyorsanız
DirectoryIndex index.html index.php
```

### URL Yolları

Alt klasörde host ediyorsanız, HTML dosyalarındaki linklerin doğru çalışması için:

**Mevcut linkler:**
```html
<a href="uyelik.html">Üyelik</a>
```

**Alt klasör için (değiştirmenize gerek yok, otomatik çalışır):**
```html
<a href="uyelik.html">Üyelik</a> <!-- Aynı klasörde olduğu için çalışır -->
```

**Veya mutlak path:**
```html
<a href="/norogastro/uyelik.html">Üyelik</a>
```

---

## 📋 Kurulum Kontrol Listesi

### Alt Klasör İçin:
- [ ] `public_html/norogastro/` klasörü oluşturuldu
- [ ] Tüm dosyalar yüklendi
- [ ] `config.php` dosyasında `SITE_URL` güncellendi
- [ ] `.htaccess` dosyası kontrol edildi
- [ ] `database.db` klasörü yazılabilir (chmod 666 veya 777)
- [ ] SSL sertifikası aktif (HTTPS)
- [ ] Test edildi: `https://yourdomain.com/norogastro/`

### Alt Domain İçin:
- [ ] Alt domain hosting panelinden oluşturuldu
- [ ] DNS ayarları yapıldı (genellikle otomatik)
- [ ] Tüm dosyalar yüklendi
- [ ] `config.php` dosyasında `SITE_URL` güncellendi
- [ ] SSL sertifikası aktif (Let's Encrypt)
- [ ] Test edildi: `https://norogastro.yourdomain.com`

---

## 🔒 Güvenlik Ayarları

### Database Klasörü İzinleri
```bash
# FTP'den veya cPanel File Manager'dan
chmod 666 database.db (dosya oluştuktan sonra)
chmod 755 . (klasör)
```

### .htaccess ile Database Koruması
`.htaccess` dosyasında zaten var:
```apache
<Files "database.db">
    Order allow,deny
    Deny from all
</Files>
```

---

## 🧪 Test Etme

### 1. Ana Sayfa
```
https://yourdomain.com/norogastro/
veya
https://norogastro.yourdomain.com
```

### 2. Üye Kayıt
```
https://yourdomain.com/norogastro/uyelik.html
```

### 3. Admin Girişi
```
https://yourdomain.com/norogastro/admin-giris.html
```

### 4. API Endpoint'leri
```
https://yourdomain.com/norogastro/login.php
https://yourdomain.com/norogastro/register.php
```

---

## ⚠️ Önemli Notlar

1. **Database Dosyası:** `database.db` dosyası ilk kullanımda otomatik oluşur. Klasör yazılabilir olmalı.

2. **Session:** Alt klasörde host ediyorsanız, session'lar ayrı çalışır (sorun yok).

3. **CORS:** `config.php` dosyasındaki `SITE_URL` mutlaka güncellenmeli.

4. **SSL:** Her iki yöntemde de SSL sertifikası aktif olmalı.

5. **Yedekleme:** Düzenli yedekleme yapın (özellikle `database.db`).

---

## 🆘 Sorun Giderme

### 404 Hatası
- Dosya yollarını kontrol edin
- `.htaccess` dosyasını kontrol edin
- Klasör izinlerini kontrol edin (755)

### Database Yazma Hatası
- `database.db` klasörü yazılabilir olmalı (chmod 666 veya 777)
- Klasör izinlerini kontrol edin

### CORS Hatası
- `config.php` dosyasında `SITE_URL` doğru mu?
- `ENVIRONMENT = 'production'` mu?
- Tarayıcı konsolunda hata mesajını kontrol edin

### Session Çalışmıyor
- PHP session ayarlarını kontrol edin
- Klasör izinlerini kontrol edin

---

## 📞 Destek

Sorun yaşarsanız:
1. Hosting firmanızın destek ekibine başvurun
2. PHP hata loglarını kontrol edin
3. Tarayıcı konsolunu kontrol edin (F12)
