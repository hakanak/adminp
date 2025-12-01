<?php
// Dosya: /admin/dashboard.php
// Ana kontrol paneli

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Kontrol Paneli';
$db = Database::getInstance();

// İstatistikler
$stats = [
    'pages' => $db->count('pages'),
    'pages_active' => $db->count('pages', 'is_active = 1'),
    'products' => $db->count('products'),
    'products_active' => $db->count('products', 'is_active = 1'),
    'posts' => $db->count('posts'),
    'posts_active' => $db->count('posts', 'is_active = 1'),
    'contacts' => $db->count('contacts'),
    'contacts_unread' => $db->count('contacts', 'is_read = 0'),
];

// Son eklenen içerikler
$recentPages = $db->fetchAll("SELECT id, title, created_at FROM pages ORDER BY created_at DESC LIMIT 5");
$recentProducts = $db->fetchAll("SELECT id, title, created_at FROM products ORDER BY created_at DESC LIMIT 5");
$recentPosts = $db->fetchAll("SELECT id, title, created_at FROM posts ORDER BY created_at DESC LIMIT 5");
$recentContacts = $db->fetchAll("SELECT id, name, subject, created_at, is_read FROM contacts ORDER BY created_at DESC LIMIT 5");

include __DIR__ . '/inc/header.php';
?>

<!-- İstatistik Kartları -->
<div class="row row-deck row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Sayfalar</div>
                </div>
                <div class="h1 mb-0"><?= $stats['pages'] ?></div>
                <div class="text-muted">
                    <?= $stats['pages_active'] ?> aktif
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Ürünler</div>
                </div>
                <div class="h1 mb-0"><?= $stats['products'] ?></div>
                <div class="text-muted">
                    <?= $stats['products_active'] ?> aktif
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Blog Yazıları</div>
                </div>
                <div class="h1 mb-0"><?= $stats['posts'] ?></div>
                <div class="text-muted">
                    <?= $stats['posts_active'] ?> aktif
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">İletişim Mesajları</div>
                </div>
                <div class="h1 mb-0"><?= $stats['contacts'] ?></div>
                <div class="text-muted">
                    <?php if ($stats['contacts_unread'] > 0): ?>
                        <span class="text-red"><?= $stats['contacts_unread'] ?> okunmamış</span>
                    <?php else: ?>
                        Hepsi okundu
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-deck row-cards">
    <!-- Son Sayfalar -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son Eklenen Sayfalar</h3>
                <div class="card-actions">
                    <a href="<?= adminUrl('pages.php') ?>" class="btn btn-sm btn-primary">
                        Tümünü Gör
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentPages)): ?>
                    <div class="p-3 text-muted">Henüz sayfa eklenmemiş.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentPages as $page): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col text-truncate">
                                        <a href="<?= adminUrl('pages.php?action=edit&id=' . $page['id']) ?>" class="text-reset">
                                            <?= e($page['title']) ?>
                                        </a>
                                        <div class="text-muted small"><?= timeAgo($page['created_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Son Ürünler -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son Eklenen Ürünler</h3>
                <div class="card-actions">
                    <a href="<?= adminUrl('products.php') ?>" class="btn btn-sm btn-primary">
                        Tümünü Gör
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentProducts)): ?>
                    <div class="p-3 text-muted">Henüz ürün eklenmemiş.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentProducts as $product): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col text-truncate">
                                        <a href="<?= adminUrl('products.php?action=edit&id=' . $product['id']) ?>" class="text-reset">
                                            <?= e($product['title']) ?>
                                        </a>
                                        <div class="text-muted small"><?= timeAgo($product['created_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Son Blog Yazıları -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son Blog Yazıları</h3>
                <div class="card-actions">
                    <a href="<?= adminUrl('blog.php') ?>" class="btn btn-sm btn-primary">
                        Tümünü Gör
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentPosts)): ?>
                    <div class="p-3 text-muted">Henüz blog yazısı eklenmemiş.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentPosts as $post): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col text-truncate">
                                        <a href="<?= adminUrl('blog.php?action=edit&id=' . $post['id']) ?>" class="text-reset">
                                            <?= e($post['title']) ?>
                                        </a>
                                        <div class="text-muted small"><?= timeAgo($post['created_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Son Mesajlar -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son İletişim Mesajları</h3>
                <div class="card-actions">
                    <a href="<?= adminUrl('contacts.php') ?>" class="btn btn-sm btn-primary">
                        Tümünü Gör
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentContacts)): ?>
                    <div class="p-3 text-muted">Henüz mesaj yok.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentContacts as $contact): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col text-truncate">
                                        <a href="<?= adminUrl('contacts.php?id=' . $contact['id']) ?>" class="text-reset d-flex align-items-center">
                                            <?php if (!$contact['is_read']): ?>
                                                <span class="badge bg-red me-2"></span>
                                            <?php endif; ?>
                                            <strong><?= e($contact['name']) ?></strong>
                                            <?php if ($contact['subject']): ?>
                                                <span class="text-muted ms-2">- <?= e(truncate($contact['subject'], 30)) ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="text-muted small"><?= timeAgo($contact['created_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı İşlemler -->
<div class="row row-cards mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hızlı İşlemler</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= adminUrl('pages.php?action=add') ?>" class="btn btn-outline-primary w-100">
                            <i class="ti ti-plus me-2"></i> Yeni Sayfa
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= adminUrl('products.php?action=add') ?>" class="btn btn-outline-primary w-100">
                            <i class="ti ti-plus me-2"></i> Yeni Ürün
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= adminUrl('blog.php?action=add') ?>" class="btn btn-outline-primary w-100">
                            <i class="ti ti-plus me-2"></i> Yeni Blog Yazısı
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= adminUrl('seo-tools.php') ?>" class="btn btn-outline-success w-100">
                            <i class="ti ti-search me-2"></i> SEO Araçları
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
