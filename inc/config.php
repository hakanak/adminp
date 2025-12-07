<?php
// Dosya: /inc/config.php
// Veritabanı ve site yapılandırma dosyası

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı Bağlantı Bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'websitedb');
define('DB_USER', 'websitesbuser');
define('DB_PASS', 'Sakarya5454!!');
define('DB_CHARSET', 'utf8mb4');

// Site Yapılandırması
define('SITE_URL', 'http://localhost:8080/website/adminp'); // Trailing slash olmadan
define('ADMIN_URL', SITE_URL . '/admin');

// Dosya Yolları
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('IMAGES_PATH', UPLOAD_PATH . '/images');
define('THUMBNAILS_PATH', UPLOAD_PATH . '/thumbnails');

// URL Yolları
define('UPLOAD_URL', SITE_URL . '/uploads');
define('IMAGES_URL', UPLOAD_URL . '/images');
define('THUMBNAILS_URL', UPLOAD_URL . '/thumbnails');

// Güvenlik
define('SESSION_LIFETIME', 7200); // 2 saat (saniye cinsinden)
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 dakika

// Resim Yükleme Ayarları
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Thumbnail Boyutları
define('THUMBNAIL_WIDTH', 400);
define('THUMBNAIL_HEIGHT', 400);
define('MAX_IMAGE_WIDTH', 1920);
define('MAX_IMAGE_HEIGHT', 1920);

// Sayfalama
define('ITEMS_PER_PAGE', 20);
define('ADMIN_ITEMS_PER_PAGE', 50);

// Tarih ve Saat
date_default_timezone_set('Europe/Istanbul');
define('DATE_FORMAT', 'd.m.Y');
define('DATETIME_FORMAT', 'd.m.Y H:i');

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => SESSION_LIFETIME,
        'cookie_httponly' => true,
        'cookie_secure' => false, // HTTPS kullanıyorsanız true yapın
        'use_strict_mode' => true,
        'use_only_cookies' => true
    ]);
}

// Yükleme klasörlerini kontrol et ve oluştur
$upload_dirs = [
    UPLOAD_PATH,
    IMAGES_PATH,
    THUMBNAILS_PATH
];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Veritabanı ve fonksiyonları yükle
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/seo.php';
