-- Dosya: update-seo-canonical.sql
-- SEO Canonical URL sütunlarını ekler
-- Kullanım: Bu dosyayı phpMyAdmin'den import edin veya mysql -u root -p websitedb < update-seo-canonical.sql

-- Pages tablosuna seo_canonical ekle (zaten var, kontrol için)
ALTER TABLE `pages`
ADD COLUMN IF NOT EXISTS `seo_canonical` varchar(255) DEFAULT NULL AFTER `seo_keywords`;

-- Products tablosuna seo_canonical ekle
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `seo_canonical` varchar(255) DEFAULT NULL AFTER `seo_keywords`;

-- Posts tablosuna seo_canonical ekle
ALTER TABLE `posts`
ADD COLUMN IF NOT EXISTS `seo_canonical` varchar(255) DEFAULT NULL AFTER `seo_keywords`;

-- Başarılı mesajı
SELECT 'SEO Canonical sütunları başarıyla eklendi!' AS Message;
