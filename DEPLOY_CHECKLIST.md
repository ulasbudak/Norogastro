# Deployment Kontrol Listesi

## Öncesi

### Dosya Kontrolü
- [ ] `create-admin.php` şifre korumalı (✓ Yapıldı)
- [ ] `view-users.php` silindi veya şifre korumalı
- [ ] `test.html` silindi
- [ ] `database.db` `.gitignore`'da (✓ Zaten var)
- [ ] Gereksiz dosyalar temizlendi

### Kod Kontrolü
- [ ] Tüm PHP dosyaları test edildi
- [ ] Form validasyonları çalışıyor
- [ ] Session yönetimi çalışıyor
- [ ] Database bağlantısı çalışıyor

### Güvenlik
- [ ] Admin şifreleri güçlü
- [ ] `create-admin.php` erişim şifresi güçlü
- [ ] `.htaccess` dosyası hazır (✓ Oluşturuldu)
- [ ] Database dosyası korunuyor

## Hosting Seçimi

- [ ] Hosting firması seçildi
- [ ] Domain satın alındı/ayarlandı
- [ ] FTP bilgileri alındı
- [ ] SSL sertifikası aktif edildi

## Yükleme

- [ ] FTP istemcisi kuruldu
- [ ] Tüm dosyalar yüklendi
- [ ] Dosya izinleri ayarlandı (644/755)
- [ ] `database.db` klasörü yazılabilir (666 veya 777)

## Test

- [ ] Ana sayfa açılıyor
- [ ] Üye kayıt çalışıyor
- [ ] Üye girişi çalışıyor
- [ ] Admin girişi çalışıyor
- [ ] Admin paneli çalışıyor
- [ ] Sipariş sistemi çalışıyor
- [ ] SSL aktif (HTTPS)

## Sonrası

- [ ] İlk admin kullanıcısı oluşturuldu
- [ ] Test kullanıcısı oluşturuldu
- [ ] Yedekleme planı yapıldı
- [ ] Monitoring kuruldu (isteğe bağlı)

## Notlar

- Database yedekleme: Haftalık
- Log kontrolü: Aylık
- Güvenlik güncellemeleri: Düzenli
