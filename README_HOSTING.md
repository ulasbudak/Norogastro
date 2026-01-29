# 🚀 Norogastro - Hızlı Hosting Rehberi

## ⚡ Hızlı Başlangıç

### 1. Hosting Seçin
**Önerilen Türk Firmalar:**
- **Turhost** - https://www.turhost.com (Aylık ~30 TL)
- **Natro** - https://www.natro.com (Aylık ~25 TL)

### 2. FTP ile Dosyaları Yükleyin

**Gerekli Program:** FileZilla (Ücretsiz)
- İndir: https://filezilla-project.org

**Adımlar:**
1. FileZilla'yı açın
2. Hosting firmanızdan aldığınız FTP bilgilerini girin:
   - Host: `ftp.yourdomain.com`
   - Kullanıcı adı: Hosting'den aldığınız
   - Şifre: Hosting'den aldığınız
   - Port: `21`
3. Bağlan'a tıklayın
4. Sol taraftan tüm dosyaları seçin
5. Sağ tarafta `public_html` veya `www` klasörüne sürükleyin
6. **ÖNEMLİ:** `database.db` dosyasını yüklemeyin (sunucuda otomatik oluşacak)

### 3. İlk Kurulum

1. Tarayıcıda sitenize gidin: `https://yourdomain.com`
2. Admin oluşturmak için: `https://yourdomain.com/create-admin.php`
3. Erişim şifresi: `Norogastro2024!` (dosyada değiştirebilirsiniz)
4. Admin kullanıcısı oluşturun

### 4. Test Edin

- ✅ Ana sayfa açılıyor mu?
- ✅ Üye kayıt çalışıyor mu?
- ✅ Admin girişi çalışıyor mu?

## 📋 Hosting Gereksinimleri

- PHP 7.4+ (8.x önerilir)
- SQLite desteği
- PDO extension
- En az 100 MB disk
- SSL sertifikası (HTTPS)

## 🔒 Güvenlik

**Production'da yapılacaklar:**
1. `create-admin.php` şifre korumalı (✓ Zaten yapıldı)
2. `view-users.php` silin veya şifreleyin
3. `test.html` silin
4. Güçlü admin şifreleri kullanın
5. SSL aktif edin

## 📞 Destek

Sorun yaşarsanız:
1. Hosting firmanızın destek ekibine başvurun
2. Detaylı rehber: `DEPLOYMENT.md` dosyasına bakın

## 📝 Notlar

- Database dosyası (`database.db`) sunucuda otomatik oluşur
- İlk kullanımda klasör yazma izni gerekebilir (chmod 666)
- Düzenli yedekleme yapın!
