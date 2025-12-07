<?php
// Dosya: /inc/header.php
// Frontend header

// Config dosyası daha önce yüklenmemişse yükle
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}

$settings = getSettings();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    // SEO taglarını render et
    if (isset($pageData) && is_array($pageData)) {
        echo renderSeoTags($pageData, $pageType ?? 'page');
    } else {
        // Varsayılan SEO
        $defaultTitle = $pageTitle ?? $settings['site_title'];
        $defaultDesc = $settings['site_slogan'] ?? '';
        ?>
        <title><?= e($defaultTitle) ?></title>
        <meta name="description" content="<?= e($defaultDesc) ?>">
        <meta property="og:title" content="<?= e($defaultTitle) ?>">
        <meta property="og:description" content="<?= e($defaultDesc) ?>">
        <meta property="og:url" content="<?= currentUrl() ?>">
        <?php if (!empty($settings['default_og_image'])): ?>
            <meta property="og:image" content="<?= siteUrl('uploads/' . $settings['default_og_image']) ?>">
        <?php endif; ?>
        <?php
    }
    ?>

    <?php if (!empty($settings['favicon'])): ?>
    <link rel="icon" href="<?= siteUrl('uploads/' . $settings['favicon']) ?>" type="image/x-icon">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= siteUrl('assets/css/style.css') ?>" rel="stylesheet">

    <!-- Google Analytics -->
    <?php if (!empty($settings['google_analytics'])): ?>
        <!-- Google Analytics kodu buraya -->
    <?php endif; ?>

    <!-- Custom head code -->
    <?php if (!empty($settings['custom_head_code'])): ?>
        <?= $settings['custom_head_code'] ?>
    <?php endif; ?>

    <?php
    // Schema.org markup
    if (isset($schemaType) && isset($schemaData)) {
        echo renderSchema($schemaType, $schemaData);
    } else {
        // Varsayılan organization schema
        echo renderSchema('organization', $settings);
        echo renderSchema('website', $settings);
    }
    ?>
</head>
<body>
    <?php if (!empty($settings['custom_body_code'])): ?>
        <?= $settings['custom_body_code'] ?>
    <?php endif; ?>

    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="<?= siteUrl() ?>">
                    <?php if (!empty($settings['logo'])): ?>
                        <img src="<?= siteUrl('uploads/' . $settings['logo']) ?>" alt="<?= e($settings['site_title']) ?>" height="40">
                    <?php else: ?>
                        <?= e($settings['site_title']) ?>
                    <?php endif; ?>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= siteUrl() ?>">
                                Ana Sayfa
                            </a>
                        </li>
                        <?php
                        // Menüdeki sayfalar
                        $db = Database::getInstance();
                        $menuPages = $db->fetchAll("SELECT slug, title FROM pages WHERE is_active = 1 AND is_in_menu = 1 ORDER BY menu_order ASC, title ASC");
                        foreach ($menuPages as $menuPage):
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage === 'page' && ($_GET['slug'] ?? '') === $menuPage['slug']) ? 'active' : '' ?>"
                                   href="<?= siteUrl($menuPage['slug']) ?>">
                                    <?= e($menuPage['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= in_array($currentPage, ['products', 'product']) ? 'active' : '' ?>" href="<?= siteUrl('urunler') ?>">
                                Ürünler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= in_array($currentPage, ['blog', 'post']) ? 'active' : '' ?>" href="<?= siteUrl('blog') ?>">
                                Blog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'galeri' ? 'active' : '' ?>" href="<?= siteUrl('galeri') ?>">
                                Galeri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= siteUrl('iletisim') ?>">
                                İletişim
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
