<?php
// Dosya: /inc/functions.php
// Yardımcı Fonksiyonlar

/**
 * Güvenli HTML output (XSS koruması)
 * @param string|null $str
 * @return string
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Türkçe karakterli slug oluştur
 * @param string $text
 * @return string
 */
function slugify($text) {
    $turkce = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'];
    $latin = ['c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'o', 's', 'u'];
    $text = str_replace($turkce, $latin, $text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim(strtolower($text), '-');
    return $text;
}

/**
 * Metin kısalt
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncate($text, $length = 160, $suffix = '...') {
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text); // Çoklu boşlukları tek boşluğa çevir
    $text = trim($text);

    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}

/**
 * Site URL oluştur
 * @param string $path
 * @return string
 */
function siteUrl($path = '') {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Admin URL oluştur
 * @param string $path
 * @return string
 */
function adminUrl($path = '') {
    return rtrim(ADMIN_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Aktif sayfa kontrolü
 * @param string $page
 * @return string
 */
function isActivePage($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}

/**
 * Mevcut URL
 * @return string
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Yönlendirme
 * @param string $url
 * @param int $statusCode
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Flash mesaj sistemi
 * @param string $key
 * @param string|null $message
 * @param string $type (success, error, warning, info)
 * @return string|null
 */
function flash($key, $message = null, $type = 'info') {
    if ($message !== null) {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    } else {
        $data = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $data;
    }
}

/**
 * Flash mesaj göster
 * @param string $key
 * @return string
 */
function showFlash($key = 'message') {
    $flash = flash($key);
    if (!$flash) return '';

    $types = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];

    $class = $types[$flash['type']] ?? 'alert-info';

    return sprintf(
        '<div class="alert %s alert-dismissible fade show" role="alert">
            %s
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>',
        $class,
        e($flash['message'])
    );
}

/**
 * CSRF Token oluştur
 * @return string
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token input field
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * CSRF Token doğrula
 * @return bool
 */
function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Tarihi Türkçe formatla
 * @param string|null $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd F Y, H:i') {
    if (!$date) return '';

    $months = [
        'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
        'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
        'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
        'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
    ];

    $days = [
        'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi',
        'Sunday' => 'Pazar'
    ];

    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $formatted = date($format, $timestamp);

    $formatted = str_replace(array_keys($months), array_values($months), $formatted);
    $formatted = str_replace(array_keys($days), array_values($days), $formatted);

    return $formatted;
}

/**
 * Göreli zaman (2 saat önce, 3 gün önce gibi)
 * @param string|int $date
 * @return string
 */
function timeAgo($date) {
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Az önce';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' dakika önce';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' saat önce';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' gün önce';
    } else {
        return formatDate($date, 'd F Y');
    }
}

/**
 * Resim yükleme ve thumbnail oluşturma
 * @param array $file $_FILES array elemanı
 * @param string $folder 'images' veya başka klasör
 * @param int $maxWidth Maksimum genişlik
 * @param int $thumbWidth Thumbnail genişliği
 * @return array|false ['original' => 'path', 'thumbnail' => 'path'] veya false
 */
function uploadImage($file, $folder = 'images', $maxWidth = MAX_IMAGE_WIDTH, $thumbWidth = THUMBNAIL_WIDTH) {
    // Hata kontrolü
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Boyut kontrolü
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    // MIME type kontrolü
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }

    // Extension kontrolü
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        return false;
    }

    // Benzersiz dosya adı oluştur
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . '/' . $folder;
    $fullPath = $uploadPath . '/' . $filename;

    // Klasör yoksa oluştur
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    // Resmi yükle
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return false;
    }

    // Resmi yeniden boyutlandır
    resizeImage($fullPath, $maxWidth, MAX_IMAGE_HEIGHT);

    // Thumbnail oluştur
    $thumbPath = THUMBNAILS_PATH . '/' . $filename;
    createThumbnail($fullPath, $thumbPath, $thumbWidth, THUMBNAIL_HEIGHT);

    return [
        'original' => $folder . '/' . $filename,
        'thumbnail' => 'thumbnails/' . $filename,
        'filename' => $filename
    ];
}

/**
 * Resmi yeniden boyutlandır
 * @param string $filepath
 * @param int $maxWidth
 * @param int $maxHeight
 * @return bool
 */
function resizeImage($filepath, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($filepath);

    // Boyutlandırma gerekli mi?
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
    }

    // Oranı koru
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    // Kaynak resmi oluştur
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return false;
    }

    // Yeni resim oluştur
    $destination = imagecreatetruecolor($newWidth, $newHeight);

    // PNG ve GIF için şeffaflık koru
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Kaydet
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $filepath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $filepath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $filepath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $filepath, 90);
            break;
    }

    imagedestroy($source);
    imagedestroy($destination);

    return true;
}

/**
 * Thumbnail oluştur
 * @param string $source
 * @param string $destination
 * @param int $width
 * @param int $height
 * @return bool
 */
function createThumbnail($source, $destination, $width, $height) {
    list($srcWidth, $srcHeight, $type) = getimagesize($source);

    // Kaynak resmi oluştur
    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $srcImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $srcImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    // Kırpma hesaplamaları (center crop)
    $srcRatio = $srcWidth / $srcHeight;
    $dstRatio = $width / $height;

    if ($srcRatio > $dstRatio) {
        // Genişlik fazla, yan tarafları kırp
        $newWidth = (int)($srcHeight * $dstRatio);
        $newHeight = $srcHeight;
        $srcX = (int)(($srcWidth - $newWidth) / 2);
        $srcY = 0;
    } else {
        // Yükseklik fazla, üst/alt kırp
        $newWidth = $srcWidth;
        $newHeight = (int)($srcWidth / $dstRatio);
        $srcX = 0;
        $srcY = (int)(($srcHeight - $newHeight) / 2);
    }

    // Yeni resim oluştur
    $dstImage = imagecreatetruecolor($width, $height);

    // Şeffaflık koru
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled($dstImage, $srcImage, 0, 0, $srcX, $srcY, $width, $height, $newWidth, $newHeight);

    // Klasör yoksa oluştur
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Kaydet
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dstImage, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($dstImage, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($dstImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($dstImage, $destination, 85);
            break;
    }

    imagedestroy($srcImage);
    imagedestroy($dstImage);

    return true;
}

/**
 * Resim sil (hem orijinal hem thumbnail)
 * @param string $imagePath
 * @return bool
 */
function deleteImage($imagePath) {
    if (empty($imagePath)) return false;

    $originalPath = UPLOAD_PATH . '/' . $imagePath;
    $filename = basename($imagePath);
    $thumbnailPath = THUMBNAILS_PATH . '/' . $filename;

    $deleted = false;

    if (file_exists($originalPath)) {
        unlink($originalPath);
        $deleted = true;
    }

    if (file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
        $deleted = true;
    }

    return $deleted;
}

/**
 * Site ayarlarını getir
 * @return array
 */
function getSettings() {
    static $settings = null;

    if ($settings === null) {
        $db = Database::getInstance();
        $settings = $db->fetchOne("SELECT * FROM settings WHERE id = 1");
        if (!$settings) {
            $settings = ['site_title' => 'Site'];
        }
    }

    return $settings;
}

/**
 * Sayfalama HTML'i oluştur
 * @param int $currentPage
 * @param int $totalPages
 * @param string $baseUrl
 * @return string
 */
function pagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';

    $html = '<nav aria-label="Sayfa navigasyonu"><ul class="pagination justify-content-center">';

    // Önceki
    if ($currentPage > 1) {
        $html .= sprintf(
            '<li class="page-item"><a class="page-link" href="%s?page=%d">Önceki</a></li>',
            $baseUrl,
            $currentPage - 1
        );
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Önceki</span></li>';
    }

    // Sayfa numaraları
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= sprintf('<li class="page-item active"><span class="page-link">%d</span></li>', $i);
        } else {
            $html .= sprintf(
                '<li class="page-item"><a class="page-link" href="%s?page=%d">%d</a></li>',
                $baseUrl,
                $i,
                $i
            );
        }
    }

    // Sonraki
    if ($currentPage < $totalPages) {
        $html .= sprintf(
            '<li class="page-item"><a class="page-link" href="%s?page=%d">Sonraki</a></li>',
            $baseUrl,
            $currentPage + 1
        );
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Sonraki</span></li>';
    }

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Sanitize input
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return trim(strip_tags($input));
}

/**
 * Validasyon: Email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasyon: URL
 * @param string $url
 * @return bool
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
