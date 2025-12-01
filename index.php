<?php
// Dosya: /index.php
// Ana sayfa

require_once __DIR__ . '/inc/config.php';

$pageTitle = 'Ana Sayfa';
$db = Database::getInstance();

// Son blog yazıları
$recentPosts = $db->fetchAll(
    "SELECT * FROM posts WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6"
);

// Öne çıkan ürünler
$featuredProducts = $db->fetchAll(
    "SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC LIMIT 6"
);

include __DIR__ . '/inc/header.php';
?>

<!-- Hero Section -->
<section class="hero bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4"><?= e($settings['site_title']) ?></h1>
                <?php if (!empty($settings['site_slogan'])): ?>
                    <p class="lead mb-4"><?= e($settings['site_slogan']) ?></p>
                <?php endif; ?>
                <div class="d-flex gap-3">
                    <a href="<?= siteUrl('urunler') ?>" class="btn btn-light btn-lg">Ürünlerimiz</a>
                    <a href="<?= siteUrl('iletisim') ?>" class="btn btn-outline-light btn-lg">İletişim</a>
                </div>
            </div>
            <div class="col-lg-6">
                <?php if (!empty($settings['default_og_image'])): ?>
                    <img src="<?= siteUrl('uploads/' . $settings['default_og_image']) ?>"
                         class="img-fluid rounded" alt="<?= e($settings['site_title']) ?>">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Öne Çıkan Ürünler -->
<?php if (!empty($featuredProducts)): ?>
<section class="featured-products py-5">
    <div class="container">
        <h2 class="text-center mb-5">Öne Çıkan Ürünler</h2>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <?php if ($product['featured_image']): ?>
                            <img src="<?= siteUrl('uploads/' . $product['featured_image']) ?>"
                                 class="card-img-top" alt="<?= e($product['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= e($product['title']) ?></h5>
                            <?php if ($product['short_description']): ?>
                                <p class="card-text text-muted"><?= e(truncate($product['short_description'], 100)) ?></p>
                            <?php endif; ?>
                            <?php if ($product['price']): ?>
                                <p class="h5 text-primary"><?= number_format($product['price'], 2) ?> TL</p>
                            <?php endif; ?>
                            <a href="<?= siteUrl('urun/' . $product['slug']) ?>" class="btn btn-primary">
                                Detayları Gör
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= siteUrl('urunler') ?>" class="btn btn-outline-primary">Tüm Ürünler</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Son Blog Yazıları -->
<?php if (!empty($recentPosts)): ?>
<section class="recent-posts py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Blog Yazıları</h2>
        <div class="row g-4">
            <?php foreach (array_slice($recentPosts, 0, 3) as $post): ?>
                <div class="col-md-4">
                    <article class="card h-100 shadow-sm">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?= siteUrl('uploads/thumbnails/' . basename($post['featured_image'])) ?>"
                                 class="card-img-top" alt="<?= e($post['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= siteUrl('blog/' . $post['slug']) ?>" class="text-decoration-none text-dark">
                                    <?= e($post['title']) ?>
                                </a>
                            </h5>
                            <?php if ($post['excerpt']): ?>
                                <p class="card-text text-muted"><?= e(truncate($post['excerpt'], 120)) ?></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= formatDate($post['published_at'] ?? $post['created_at'], 'd F Y') ?></small>
                                <a href="<?= siteUrl('blog/' . $post['slug']) ?>" class="btn btn-sm btn-outline-primary">
                                    Devamını Oku
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= siteUrl('blog') ?>" class="btn btn-outline-primary">Tüm Yazılar</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/inc/footer.php'; ?>
