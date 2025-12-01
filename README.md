# PHP Kurumsal CMS Paneli

Küçük ve orta ölçekli işletmeler için hafif, hızlı ve SEO dostu içerik yönetim sistemi. WordPress alternatifi olarak tasarlanmış, minimal ve performanslı bir CMS.

## Özellikler

### Genel
- ✅ Hafif ve hızlı (Framework yok, vanilla PHP)
- ✅ Tam SEO odaklı
- ✅ Responsive tasarım (Bootstrap 5)
- ✅ Cpanel shared hosting uyumlu
- ✅ Güvenli (PDO prepared statements, CSRF koruması, XSS koruması)

### Admin Paneli
- 📊 Dashboard (istatistikler ve son aktiviteler)
- 📄 Sayfa yönetimi (sınırsız sayfa)
- 🛍️ Ürün/Proje yönetimi (kategoriler, resim galerisi)
- 📝 Blog sistemi (kategoriler, öne çıkan yazılar)
- 📧 İletişim mesajları yönetimi
- ⚙️ Site ayarları (logo, favicon, sosyal medya, iletişim bilgileri)
- 🔍 SEO araçları (sitemap, robots.txt otomatik oluşturma)

### SEO Özellikleri
- Meta title, description, keywords
- Open Graph (Facebook, Twitter)
- Canonical URL
- Robots meta tag
- Otomatik sitemap.xml
- Otomatik robots.txt
- Schema.org JSON-LD markup
- URL slug özelleştirme (Türkçe karakter desteği)
- **SEO analiz aracı** (başlık/açıklama uzunluğu, kelime yoğunluğu)
- **Google önizleme** (admin panelinde canlı önizleme)

### Frontend
- SEO dostu URL yapısı
- Hızlı sayfa yükleme
- Mobil uyumlu
- WhatsApp float button
- İletişim formu
- Blog ve ürün listeleme

## Sistem Gereksinimleri

- PHP 8.1 veya üzeri
- MySQL 8.0 / MariaDB 10.6 veya üzeri
- Apache (mod_rewrite etkin)
- GD Library (resim işleme için)

## Kurulum

### 1. Dosyaları Yükleyin

Tüm dosyaları web sunucunuzun `public_html` klasörüne yükleyin.

### 2. Veritabanını Oluşturun

1. cPanel'den veya phpMyAdmin'den yeni bir veritabanı oluşturun:
   - Veritabanı adı: `websitedb`
   - Kullanıcı adı: `websitesbuser`
   - Şifre: `websitespass!!`

2. `install.sql` dosyasını veritabanınıza import edin:
   ```bash
   mysql -u websitesbuser -p websitedb < install.sql
   ```

   Veya phpMyAdmin'den:
   - Veritabanınızı seçin
   - "Import" sekmesine gidin
   - `install.sql` dosyasını seçin ve "Go" tıklayın

### 3. Yapılandırma

`inc/config.php` dosyası zaten kurulu gelir, ancak gerekirse veritabanı bilgilerini ve site URL'nizi güncelleyin:

```php
// Veritabanı
define('DB_HOST', 'localhost');
define('DB_NAME', 'websitedb');
define('DB_USER', 'websitesbuser');
define('DB_PASS', 'Sakarya5454!!');

// Site URL (trailing slash olmadan)
define('SITE_URL', 'http://yoursite.com');
```

### 4. Dosya İzinleri

Uploads klasörüne yazma izni verin:

```bash
chmod -R 755 uploads/
```

### 5. .htaccess Ayarları

`.htaccess` dosyasındaki `RewriteBase` satırını sitenize göre ayarlayın:

```apache
RewriteBase /
```

Eğer alt dizindeyseniz:
```apache
RewriteBase /website/adminp/
```

### 6. Admin Paneline Giriş

Admin paneline giriş yapın:
- URL: `http://yoursite.com/admin`
- Kullanıcı adı: `admin`
- Şifre: `admin123`

**ÖNEMLİ:** İlk girişten sonra şifrenizi değiştirin!

## Kullanım

### Sayfa Ekle

1. Admin panelinden **Sayfalar** > **Yeni Sayfa Ekle**
2. Başlık ve içerik girin
3. SEO ayarlarını yapın (başlık, açıklama, anahtar kelimeler)
4. "Menüde Göster" seçeneğini işaretleyin (isteğe bağlı)
5. Kaydet

### Ürün Ekle

1. **Ürünler** > **Yeni Ürün Ekle**
2. Ürün bilgilerini girin
3. Kategori seçin
4. Resim yükleyin
5. Fiyat belirleyin (opsiyonel)
6. SEO ayarlarını yapın
7. Kaydet

### Blog Yazısı Ekle

1. **Blog** > **Yeni Yazı Ekle**
2. Başlık, özet ve içerik girin
3. Kategori seçin
4. Öne çıkan resim yükleyin
5. Yayın tarihi belirleyin
6. SEO ayarlarını yapın
7. Kaydet

### SEO Araçları

1. **SEO Araçları** sayfasına gidin
2. **Sitemap Oluştur** butonuna tıklayın
   - Oluşturulan sitemap: `http://yoursite.com/sitemap.xml`
3. **robots.txt Oluştur** butonuna tıklayın
   - Oluşturulan robots.txt: `http://yoursite.com/robots.txt`

### Google Search Console'a Sitemap Ekle

1. [Google Search Console](https://search.google.com/search-console) açın
2. Sitenizi ekleyin/doğrulayın
3. **Sitemaps** bölümüne gidin
4. Sitemap URL'nizi ekleyin: `https://yoursite.com/sitemap.xml`

## Site Ayarları

**Ayarlar** sayfasından aşağıdakileri yapılandırabilirsiniz:

- Site başlığı ve slogan
- Logo ve favicon
- İletişim bilgileri (telefon, email, adres)
- Sosyal medya hesapları
- Google Analytics
- Google Search Console doğrulama kodu
- Özel CSS/JavaScript kodları

## Güvenlik

### Varsayılan Güvenlik Özellikleri

- ✅ PDO Prepared Statements (SQL injection koruması)
- ✅ CSRF token kontrolü
- ✅ XSS koruması (htmlspecialchars)
- ✅ Session güvenliği
- ✅ Brute force koruması (login rate limiting)
- ✅ Dosya tipi ve boyut kontrolü
- ✅ .htaccess ile hassas dosya koruması

### Önerilen Güvenlik Adımları

1. **Admin şifrenizi değiştirin** (ilk giriş sonrası)
2. **Veritabanı bilgilerini güvenli tutun**
3. **HTTPS kullanın** (SSL sertifikası edinin)
4. **Düzenli yedekleme yapın**
5. **PHP ve MySQL güncel tutun**

## Performans İpuçları

1. **Browser caching** (`.htaccess`'te mevcut)
2. **Gzip compression** (`.htaccess`'te mevcut)
3. **Resim optimizasyonu** (yüklemeden önce küçültün)
4. **CDN kullanın** (statik dosyalar için)
5. **Opcache etkinleştirin** (php.ini)

## Özelleştirme

### Tema Değiştirme

Frontend tema dosyaları:
- `/inc/header.php` - Üst menü ve SEO tagları
- `/inc/footer.php` - Alt bilgi ve scriptler
- `/assets/css/style.css` - CSS stilleri
- `/assets/js/main.js` - JavaScript kodları

### Admin Tema

Admin panel Tabler.io teması kullanır:
- `/admin/inc/header.php`
- `/admin/inc/sidebar.php`
- `/admin/inc/footer.php`
- `/admin/assets/css/admin.css`
- `/admin/assets/js/admin.js`

## Sorun Giderme

### "500 Internal Server Error"

1. `.htaccess` dosyasını kontrol edin
2. PHP hata loglarını inceleyin
3. Dosya izinlerini kontrol edin

### Resimler Yüklenmiyor

1. `uploads/` klasörüne yazma izni verin: `chmod -R 755 uploads/`
2. PHP `upload_max_filesize` ayarını kontrol edin
3. GD Library yüklü mü kontrol edin: `php -m | grep gd`

### URL Rewrite Çalışmıyor

1. Apache `mod_rewrite` modülü etkin mi kontrol edin
2. `.htaccess` `RewriteBase` ayarını kontrol edin
3. `AllowOverride All` direktifi var mı kontrol edin

### SEO Araçları Çalışmıyor

1. Root klasörüne yazma izni var mı kontrol edin
2. `sitemap.xml` ve `robots.txt` dosyaları oluşturuldu mu kontrol edin
3. Sunucu hataları için PHP loglarını inceleyin

## Teknik Detaylar

### Dosya Yapısı

```
/
├── admin/              # Admin panel
│   ├── inc/           # Header, sidebar, footer, auth
│   ├── assets/        # Admin CSS/JS
│   ├── ajax/          # AJAX handlers
│   └── *.php          # Admin sayfaları
├── inc/                # Ortak dosyalar
│   ├── config.php     # Yapılandırma
│   ├── db.php         # Veritabanı sınıfı
│   ├── functions.php  # Yardımcı fonksiyonlar
│   ├── seo.php        # SEO fonksiyonları
│   ├── header.php     # Frontend header
│   └── footer.php     # Frontend footer
├── assets/             # Frontend assets
│   ├── css/
│   └── js/
├── uploads/            # Yüklenen dosyalar
│   ├── images/
│   └── thumbnails/
├── index.php           # Ana sayfa
├── page.php            # Dinamik sayfalar
├── contact.php         # İletişim
├── .htaccess          # URL rewrite
└── install.sql        # Veritabanı
```

### Veritabanı Tabloları

- `settings` - Site ayarları
- `users` - Admin kullanıcılar
- `pages` - Sayfalar
- `products` - Ürünler
- `product_categories` - Ürün kategorileri
- `posts` - Blog yazıları
- `post_categories` - Blog kategorileri
- `contacts` - İletişim mesajları
- `sliders` - Slider (opsiyonel)

## Lisans

Bu proje özel kullanım içindir.

## Destek

Sorunlarınız için:
- Email: admin@example.com
- GitHub: (proje URL'si)

## Güncellemeler

### v1.0 (2024)
- İlk sürüm
- Sayfa, ürün, blog yönetimi
- SEO araçları
- İletişim formu
