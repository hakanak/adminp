<?php
// Dosya: /admin/inc/sidebar.php
// Sol menü

// Okunmamış mesaj sayısı
$db = Database::getInstance();
$unreadMessages = $db->count('contacts', 'is_read = 0');
?>
<!-- Sidebar -->
<div class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar navbar-light">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <li class="nav-item <?= isActivePage('dashboard.php') ?>">
                        <a class="nav-link" href="<?= adminUrl('dashboard.php') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-home"></i>
                            </span>
                            <span class="nav-link-title">Ana Sayfa</span>
                        </a>
                    </li>

                    <!-- Sayfalar -->
                    <li class="nav-item dropdown <?= in_array(basename($_SERVER['PHP_SELF']), ['pages.php']) ? 'active' : '' ?>">
                        <a class="nav-link dropdown-toggle" href="#navbar-pages" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-file-text"></i>
                            </span>
                            <span class="nav-link-title">Sayfalar</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?= adminUrl('pages.php') ?>">
                                Tüm Sayfalar
                            </a>
                            <a class="dropdown-item" href="<?= adminUrl('pages.php?action=add') ?>">
                                Yeni Sayfa Ekle
                            </a>
                        </div>
                    </li>

                    <!-- Ürünler -->
                    <li class="nav-item dropdown <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-categories.php']) ? 'active' : '' ?>">
                        <a class="nav-link dropdown-toggle" href="#navbar-products" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-package"></i>
                            </span>
                            <span class="nav-link-title">Ürünler</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?= adminUrl('products.php') ?>">
                                Tüm Ürünler
                            </a>
                            <a class="dropdown-item" href="<?= adminUrl('products.php?action=add') ?>">
                                Yeni Ürün Ekle
                            </a>
                            <a class="dropdown-item" href="<?= adminUrl('product-categories.php') ?>">
                                Kategoriler
                            </a>
                        </div>
                    </li>

                    <!-- Blog -->
                    <li class="nav-item dropdown <?= in_array(basename($_SERVER['PHP_SELF']), ['blog.php', 'blog-categories.php']) ? 'active' : '' ?>">
                        <a class="nav-link dropdown-toggle" href="#navbar-blog" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-article"></i>
                            </span>
                            <span class="nav-link-title">Blog</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?= adminUrl('blog.php') ?>">
                                Tüm Yazılar
                            </a>
                            <a class="dropdown-item" href="<?= adminUrl('blog.php?action=add') ?>">
                                Yeni Yazı Ekle
                            </a>
                            <a class="dropdown-item" href="<?= adminUrl('blog-categories.php') ?>">
                                Kategoriler
                            </a>
                        </div>
                    </li>

                    <!-- İletişim -->
                    <li class="nav-item <?= isActivePage('contacts.php') ?>">
                        <a class="nav-link" href="<?= adminUrl('contacts.php') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-mail"></i>
                            </span>
                            <span class="nav-link-title">
                                İletişim Mesajları
                                <?php if ($unreadMessages > 0): ?>
                                    <span class="badge badge-sm bg-red"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    </li>

                    <!-- SEO Araçları -->
                    <li class="nav-item <?= isActivePage('seo-tools.php') ?>">
                        <a class="nav-link" href="<?= adminUrl('seo-tools.php') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-search"></i>
                            </span>
                            <span class="nav-link-title">SEO Araçları</span>
                        </a>
                    </li>

                    <!-- Ayarlar -->
                    <li class="nav-item <?= isActivePage('settings.php') ?>">
                        <a class="nav-link" href="<?= adminUrl('settings.php') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-settings"></i>
                            </span>
                            <span class="nav-link-title">Site Ayarları</span>
                        </a>
                    </li>

                    <!-- Siteyi Görüntüle -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>" target="_blank">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-external-link"></i>
                            </span>
                            <span class="nav-link-title">Siteyi Görüntüle</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
