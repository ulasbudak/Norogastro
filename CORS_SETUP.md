# CORS Ayarları - Kurulum Rehberi

## ✅ Yapılan Değişiklikler

Tüm PHP dosyalarındaki CORS ayarları `config.php` dosyasına taşındı. Artık tek bir yerden yönetebilirsiniz.

## 🔧 Production'da Yapılacaklar

### 1. `config.php` Dosyasını Düzenleyin

`config.php` dosyasını açın ve şu satırları güncelleyin:

```php
// Site ayarları
define('SITE_URL', 'https://yourdomain.com'); // ⬅️ BURAYA KENDİ DOMAIN'İNİZİ YAZIN
define('SITE_NAME', 'Norogastro');

// Environment (development veya production)
define('ENVIRONMENT', 'production'); // ⬅️ 'development' yerine 'production' yapın
```

### 2. Örnek Yapılandırma

**Örnek 1: Tek domain**
```php
define('SITE_URL', 'https://norogastro.com');
define('ENVIRONMENT', 'production');
```

**Örnek 2: www ile birlikte**
```php
define('SITE_URL', 'https://www.norogastro.com');
define('ENVIRONMENT', 'production');
```

**Örnek 3: Alt domain**
```php
define('SITE_URL', 'https://app.norogastro.com');
define('ENVIRONMENT', 'production');
```

**Örnek 4: İki (veya daha fazla) domain**
Aynı siteyi iki farklı domain’den açmak istiyorsanız (örn. norogastro.com + norogastro.net), ikinci domain’i CORS izin listesine ekleyin:

```php
define('SITE_URL', 'https://norogastro.com');
define('ENVIRONMENT', 'production');

define('ADDITIONAL_ALLOWED_ORIGINS', [
    'https://norogastro.net',
    'https://www.norogastro.net',
]);
```

Böylece hem ana domain hem ikinci domain’den gelen istekler kabul edilir. Hazır sitenin domain’i olmak zorunda değil; istediğiniz kadar domain ekleyebilirsiniz.

## 📋 Nasıl Çalışıyor?

### Development Modu
- `ENVIRONMENT = 'development'` olduğunda:
  - Localhost isteklerine izin verilir
  - Test domain'lerine izin verilir
  - Tüm origin'lere izin verilir (`*`)

### Production Modu
- `ENVIRONMENT = 'production'` olduğunda:
  - `SITE_URL` ve www versiyonundan gelen isteklere izin verilir
  - `ADDITIONAL_ALLOWED_ORIGINS` listesindeki domain’lere de izin verilir (ikinci domain vb.)
  - Listede olmayan domain’lerden gelen istekler reddedilir (403)

## 🔒 Güvenlik

Production modunda:
- ✅ Sadece kendi domain'inizden isteklere izin verilir
- ✅ İzin verilmeyen origin'den istek gelirse 403 hatası döner
- ✅ CORS preflight (OPTIONS) istekleri desteklenir
- ✅ Credentials (cookies) desteği aktif

## 🧪 Test Etme

### Development'ta Test
```bash
# Localhost'tan test
curl -H "Origin: http://localhost:8000" http://localhost:8000/login.php
```

### Production'da Test
```bash
# Kendi domain'inizden test
curl -H "Origin: https://yourdomain.com" https://yourdomain.com/login.php

# Başka domain'den test (reddedilmeli)
curl -H "Origin: https://evil.com" https://yourdomain.com/login.php
# Beklenen: 403 Forbidden
```

## 📝 Güncellenen Dosyalar

Tüm bu dosyalar artık `config.php` kullanıyor:
- ✅ `login.php`
- ✅ `register.php`
- ✅ `admin-login.php`
- ✅ `odeme.php`
- ✅ `user-info.php`
- ✅ `get-all-users.php`
- ✅ `get-all-orders.php`
- ✅ `update-order-status.php`
- ✅ `get-orders.php`

## ⚠️ Önemli Notlar

1. **Domain'i doğru yazın:** `https://` ile başlamalı
2. **Production'da environment'ı değiştirin:** `'production'` yapın
3. **www versiyonu:** Otomatik olarak eklenir, ayrıca eklemenize gerek yok
4. **SSL zorunlu:** Production'da HTTPS kullanın  
5. **İkinci domain:** Aynı siteyi iki domain’den kullanacaksanız `ADDITIONAL_ALLOWED_ORIGINS` dizisine ikinci domain’i (ve varsa www’sini) ekleyin

## 🆘 Sorun Giderme

### CORS hatası alıyorsam?
1. `config.php` dosyasında `SITE_URL` doğru mu?
2. `ENVIRONMENT` `'production'` mu?
3. Domain'de `https://` var mı?
4. Tarayıcı konsolunda hata mesajını kontrol edin

### Development'ta çalışmıyor?
- `ENVIRONMENT = 'development'` olduğundan emin olun
- Localhost portunu kontrol edin (8000, 8080)

## ✅ Kontrol Listesi

- [ ] `config.php` dosyasında `SITE_URL` güncellendi
- [ ] `ENVIRONMENT = 'production'` yapıldı
- [ ] SSL sertifikası aktif
- [ ] Test edildi (kendi domain'inden)
- [ ] Test edildi (başka domain'den - reddedilmeli)
