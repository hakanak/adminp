-- Dosya: update-slider-gallery.sql
-- Mevcut kurulumlar için Slider ve Galeri tabloları güncelleme scripti
-- Kullanım: Bu dosyayı phpMyAdmin'den import edin veya mysql -u root -p websitedb < update-slider-gallery.sql

-- Sliders tablosuna yeni alanlar ekle (eğer yoksa)
-- NOT: created_at alanı zaten varsa hata verecektir, bu normaldir

ALTER TABLE `sliders`
ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `is_active`;

-- Gallery tablosunu oluştur (eğer yoksa)
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Başarılı mesajı
SELECT 'Slider ve Galeri tabloları başarıyla güncellendi!' AS Message;
