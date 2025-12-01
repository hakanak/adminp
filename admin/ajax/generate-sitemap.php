<?php
// Dosya: /admin/ajax/generate-sitemap.php
// Sitemap ve robots.txt oluşturma AJAX handler

require_once dirname(__DIR__, 2) . '/inc/config.php';
require_once __DIR__ . '/../inc/auth.php';

header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$action = $_POST['action'];
$db = Database::getInstance();

/**
 * Sitemap URL elementi oluştur
 */
function generateUrlElement($loc, $lastmod, $changefreq, $priority) {
    return sprintf(
        "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
        htmlspecialchars($loc),
        $lastmod,
        $changefreq,
        $priority
    );
}

/**
 * Sitemap.xml oluştur
 */
function generateSitemap() {
    global $db;

    $baseUrl = rtrim(SITE_URL, '/');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Ana sayfa
    $xml .= generateUrlElement($baseUrl . '/', date('Y-m-d'), 'daily', '1.0');

    // Sayfalar
    $pages = $db->fetchAll("SELECT slug, updated_at FROM pages WHERE is_active = 1 AND seo_robots != 'noindex,nofollow'");
    foreach ($pages as $page) {
        $xml .= generateUrlElement(
            $baseUrl . '/' . $page['slug'],
            date('Y-m-d', strtotime($page['updated_at'])),
            'weekly',
            '0.8'
        );
    }

    // Ürünler
    $products = $db->fetchAll("SELECT slug, updated_at FROM products WHERE is_active = 1 AND seo_robots != 'noindex,nofollow'");
    foreach ($products as $product) {
        $xml .= generateUrlElement(
            $baseUrl . '/urun/' . $product['slug'],
            date('Y-m-d', strtotime($product['updated_at'])),
            'weekly',
            '0.8'
        );
    }

    // Blog yazıları
    $posts = $db->fetchAll("SELECT slug, updated_at FROM posts WHERE is_active = 1 AND seo_robots != 'noindex,nofollow'");
    foreach ($posts as $post) {
        $xml .= generateUrlElement(
            $baseUrl . '/blog/' . $post['slug'],
            date('Y-m-d', strtotime($post['updated_at'])),
            'monthly',
            '0.6'
        );
    }

    // İletişim sayfası
    $xml .= generateUrlElement($baseUrl . '/iletisim', date('Y-m-d'), 'monthly', '0.5');

    $xml .= '</urlset>';

    // Dosyaya yaz
    $sitemapPath = ROOT_PATH . '/sitemap.xml';
    $result = file_put_contents($sitemapPath, $xml);

    return $result !== false;
}

/**
 * Robots.txt oluştur
 */
function generateRobots($customRules = '') {
    $baseUrl = rtrim(SITE_URL, '/');

    $content = "User-agent: *\n";
    $content .= "Allow: /\n\n";

    // Varsayılan engellemeler
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /inc/\n";
    $content .= "Disallow: /uploads/thumbnails/\n\n";

    // Özel kurallar
    if ($customRules) {
        $content .= "# Özel Kurallar\n";
        $content .= $customRules . "\n\n";
    }

    // Sitemap
    $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

    // Dosyaya yaz
    $robotsPath = ROOT_PATH . '/robots.txt';
    $result = file_put_contents($robotsPath, $content);

    return $result !== false;
}

// İşlemleri yap
try {
    if ($action === 'sitemap') {
        $success = generateSitemap();

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Sitemap başarıyla oluşturuldu.',
                'url' => SITE_URL . '/sitemap.xml'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sitemap oluşturulamadı. Dosya izinlerini kontrol edin.'
            ]);
        }
    } elseif ($action === 'robots') {
        $customRules = $_POST['custom_rules'] ?? '';
        $success = generateRobots($customRules);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'robots.txt başarıyla oluşturuldu.',
                'url' => SITE_URL . '/robots.txt'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'robots.txt oluşturulamadı. Dosya izinlerini kontrol edin.'
            ]);
        }
    } elseif ($action === 'both') {
        $sitemapSuccess = generateSitemap();
        $robotsSuccess = generateRobots($_POST['custom_rules'] ?? '');

        if ($sitemapSuccess && $robotsSuccess) {
            echo json_encode([
                'success' => true,
                'message' => 'Sitemap ve robots.txt başarıyla oluşturuldu.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Bazı dosyalar oluşturulamadı. Dosya izinlerini kontrol edin.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz işlem'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}
