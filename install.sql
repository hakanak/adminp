-- Dosya: install.sql
-- PHP Kurumsal CMS Veritabanı Kurulum Dosyası
-- Kurulum: Bu dosyayı phpMyAdmin'den import edin veya mysql -u root -p websitedb < install.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Ayarlar tablosu
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `site_title` varchar(100) NOT NULL,
  `site_slogan` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `maps_embed` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `default_og_image` varchar(255) DEFAULT NULL,
  `google_analytics` varchar(50) DEFAULT NULL,
  `google_search_console` varchar(255) DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `custom_head_code` text DEFAULT NULL,
  `custom_body_code` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_settings_id` CHECK (`id` = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Sayfalar tablosu
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `seo_title` varchar(70) DEFAULT NULL,
  `seo_description` varchar(160) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_canonical` varchar(255) DEFAULT NULL,
  `seo_robots` enum('index,follow','noindex,follow','noindex,nofollow') DEFAULT 'index,follow',
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(200) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_in_menu` tinyint(1) DEFAULT 0,
  `menu_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`),
  KEY `is_in_menu` (`is_in_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Ürün Kategorileri tablosu
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `fk_product_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Ürünler / Projeler tablosu
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  `title` varchar(200) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `seo_title` varchar(70) DEFAULT NULL,
  `seo_description` varchar(160) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_canonical` varchar(255) DEFAULT NULL,
  `seo_robots` enum('index,follow','noindex,follow','noindex,nofollow') DEFAULT 'index,follow',
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(200) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `is_active` (`is_active`),
  KEY `is_featured` (`is_featured`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Blog Kategorileri tablosu
CREATE TABLE IF NOT EXISTS `post_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Blog Yazıları tablosu
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  `title` varchar(200) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `seo_title` varchar(70) DEFAULT NULL,
  `seo_description` varchar(160) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_canonical` varchar(255) DEFAULT NULL,
  `seo_robots` enum('index,follow','noindex,follow','noindex,nofollow') DEFAULT 'index,follow',
  `og_title` varchar(70) DEFAULT NULL,
  `og_description` varchar(200) DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `view_count` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `is_active` (`is_active`),
  KEY `is_featured` (`is_featured`),
  CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `post_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- İletişim Mesajları tablosu
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Slider / Banner tablosu
CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Galeri tablosu
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

-- --------------------------------------------------------

-- Varsayılan verileri ekle

-- Varsayılan site ayarları
INSERT INTO `settings` (`id`, `site_title`, `site_slogan`, `email`, `footer_text`) VALUES
(1, 'Kurumsal Site', 'Profesyonel Web Çözümleri', 'info@example.com', '© 2024 Tüm Hakları Saklıdır.');

-- Varsayılan admin kullanıcısı
-- Kullanıcı Adı: admin
-- Şifre: admin123
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `is_active`) VALUES
('admin', 'admin@example.com', '$2y$10$CNi08pg5rTWLiU6XMRaVVOD7HKOFJ6RtlYtou2b76LpI2h68pPt1C', 'Site Yöneticisi', 1);

-- Örnek sayfa
INSERT INTO `pages` (`slug`, `title`, `content`, `is_active`, `is_in_menu`, `menu_order`) VALUES
('hakkimizda', 'Hakkımızda', '<p>Buraya hakkımızda içeriği gelecek...</p>', 1, 1, 1);

-- Örnek blog kategorisi
INSERT INTO `post_categories` (`slug`, `name`, `description`, `is_active`) VALUES
('genel', 'Genel', 'Genel blog yazıları', 1);

-- Örnek ürün kategorisi
INSERT INTO `product_categories` (`slug`, `name`, `description`, `is_active`) VALUES
('genel', 'Genel', 'Genel ürünler', 1);

-- --------------------------------------------------------

-- İndeksler ve Optimizasyon
ALTER TABLE `pages` ADD FULLTEXT KEY `ft_pages_search` (`title`,`content`);
ALTER TABLE `products` ADD FULLTEXT KEY `ft_products_search` (`title`,`short_description`,`description`);
ALTER TABLE `posts` ADD FULLTEXT KEY `ft_posts_search` (`title`,`excerpt`,`content`);
