# Slider ve Galeri Modülleri

Bu dokümanda yeni eklenen Slider ve Galeri modüllerinin kullanımı açıklanmaktadır.

## Kurulum

### Yeni Kurulumlar
Eğer sistemi ilk kez kuruyorsanız, `install.sql` dosyasını kullanın. Slider ve Galeri tabloları otomatik olarak oluşturulacaktır.

### Mevcut Kurulumlar
Eğer daha önce kurulum yaptıysanız ve sadece Slider/Galeri özelliklerini eklemek istiyorsanız:

1. `update-slider-gallery.sql` dosyasını phpMyAdmin'den import edin
2. VEYA komut satırından çalıştırın:
```bash
mysql -u root -p websitedb < update-slider-gallery.sql
```

## Slider Yönetimi

### Admin Panel Erişimi
- **URL**: `http://localhost:8080/website/adminp/admin/slider.php`
- **Menü**: Admin panelde "Slider" menüsünden erişilebilir

### Özellikler
- ✅ Sınırsız slider ekleme
- ✅ Başlık ve alt başlık metinleri
- ✅ Buton metni ve URL (tıklanabilir)
- ✅ Sürükle-bırak ile sıralama
- ✅ Aktif/Pasif durumu
- ✅ Otomatik responsive resim boyutlandırma (önerilen: 1920x800px)

### Slider Kullanımı
1. Admin panelde "Slider" menüsüne tıklayın
2. "Yeni Slider Ekle" butonuna basın
3. Formu doldurun:
   - **Başlık**: Slider üzerinde gösterilecek ana başlık
   - **Alt Başlık**: Açıklama metni
   - **Buton Metni**: Örn: "Detaylı Bilgi", "Hemen İncele"
   - **Buton URL**: Butona tıklandığında gidilecek adres
   - **Sıralama**: Küçük numara önce görünür
   - **Resim**: 1920x800px önerilir (JPG, PNG)
4. "Kaydet" butonuna basın

### Sıralama
Liste sayfasında satırları sürükleyerek sıralamayı değiştirebilirsiniz. Sıralama otomatik olarak kaydedilir.

### Frontend Görünümü
Sliderlar ana sayfada (`index.php`) otomatik olarak gösterilir:
- Bootstrap 5 carousel ile
- Fade animasyonu ile
- Göstergeler ve ileri/geri butonları ile (birden fazla slider varsa)
- Responsive tasarım

## Galeri Yönetimi

### Admin Panel Erişimi
- **URL**: `http://localhost:8080/website/adminp/admin/gallery.php`
- **Menü**: Admin panelde "Galeri" menüsünden erişilebilir

### Özellikler
- ✅ Sınırsız galeri resmi ekleme
- ✅ Her resim için başlık ve açıklama
- ✅ Grid görünümü ile yönetim
- ✅ Sürükle-bırak ile sıralama
- ✅ Aktif/Pasif durumu
- ✅ Otomatik thumbnail oluşturma (1200x800px önerilir)

### Galeri Kullanımı
1. Admin panelde "Galeri" menüsüne tıklayın
2. "Yeni Resim Ekle" butonuna basın
3. Formu doldurun:
   - **Başlık**: Resim başlığı (zorunlu)
   - **Açıklama**: Resim hakkında kısa açıklama (opsiyonel)
   - **Sıralama**: Küçük numara önce görünür
   - **Resim**: 1200x800px önerilir (JPG, PNG, GIF)
4. "Kaydet" butonuna basın

### Sıralama
Grid görünümünde kartları sürükleyerek sıralamayı değiştirebilirsiniz. Sıralama otomatik olarak kaydedilir.

### Frontend Görünümü
Galeri sayfası:
- **URL**: `http://localhost:8080/website/adminp/galeri.php`
- **Menü**: Ana menüde "Galeri" linki otomatik eklenir
- **Özellikler**:
  - Responsive grid düzeni (mobilde 1, tablette 2, masaüstünde 4 sütun)
  - Lightbox2 entegrasyonu (resimlere tıklayınca büyük görüntüler)
  - Hover efektleri
  - Zoom ikonu

## Teknik Detaylar

### Veritabanı Tabloları

#### sliders
```sql
- id: int(11) AUTO_INCREMENT
- title: varchar(200) - Başlık
- subtitle: varchar(300) - Alt başlık
- image: varchar(255) - Resim yolu
- button_text: varchar(50) - Buton metni
- button_url: varchar(255) - Buton linki
- sort_order: int(11) - Sıralama
- is_active: tinyint(1) - Aktif/Pasif
- created_at: timestamp - Oluşturulma tarihi
```

#### gallery
```sql
- id: int(11) AUTO_INCREMENT
- title: varchar(200) - Başlık
- description: text - Açıklama
- image: varchar(255) - Resim yolu
- sort_order: int(11) - Sıralama
- is_active: tinyint(1) - Aktif/Pasif
- created_at: timestamp - Oluşturulma tarihi
```

### Kullanılan Kütüphaneler

#### Frontend
- **Bootstrap 5**: Carousel ve grid düzeni için
- **Lightbox2**: Galeri lightbox için (CDN: 2.11.3)
- **SortableJS**: Admin panelde sürükle-bırak sıralama için (CDN: 1.15.0)

#### Backend
- **PHP 8.1+**: PDO ile veritabanı işlemleri
- **GD Library**: Otomatik resim yeniden boyutlandırma

### Dosya Yapısı

```
/admin/slider.php         - Slider CRUD sayfası
/admin/gallery.php        - Galeri CRUD sayfası
/galeri.php               - Frontend galeri sayfası
/inc/header.php           - Galeri menü linki eklendi
/admin/inc/sidebar.php    - Slider ve Galeri menü öğeleri eklendi
/index.php                - Slider carousel eklendi
```

### Resim Boyutları ve Optimizasyon

- **Slider Resimleri**: 1920x800px (16:10 oran)
- **Galeri Resimleri**: 1200x800px (3:2 oran)
- **Maksimum Boyut**: 5MB
- **Desteklenen Formatlar**: JPG, PNG, GIF
- **Otomatik İşlemler**:
  - Thumbnail oluşturma (400px genişlik)
  - Resim sıkıştırma (kalite: 90)
  - Şeffaflık koruması (PNG için)

## Sık Sorulan Sorular

**S: Slider gösterilmiyor?**
C: Slider'ların "Aktif" olduğundan emin olun. Hiç aktif slider yoksa varsayılan hero section gösterilir.

**S: Galeri resimleri büyümüyor?**
C: Lightbox2 JavaScript kütüphanesi yüklendiğinden emin olun. Tarayıcı konsolunda hata var mı kontrol edin.

**S: Sıralama çalışmıyor?**
C: SortableJS kütüphanesi yüklendiğinden ve JavaScript'in aktif olduğundan emin olun.

**S: Resimleri toplu yükleyebilir miyim?**
C: Şu anda tek tek yükleme destekleniyor. Toplu yükleme için gelecekte geliştirme yapılabilir.

**S: Slider otomatik mı geçiyor?**
C: Evet, Bootstrap carousel varsayılan olarak otomatik geçiş yapar (5 saniye). Değiştirmek için `index.php` içindeki carousel ayarlarını düzenleyin.

## Gelecek Geliştirmeler

- [ ] Slider için toplu resim yükleme
- [ ] Galeri için kategoriler
- [ ] Video desteği (YouTube/Vimeo embed)
- [ ] Slider için farklı animasyon tipleri
- [ ] AJAX ile silme/güncelleme (sayfa yenilemeden)
- [ ] Resim kırpma aracı
- [ ] Watermark ekleme özelliği

## Destek

Sorun yaşarsanız veya yeni özellik talepleriniz varsa issue açabilirsiniz.
