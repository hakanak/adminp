<?php
// Dosya: /products.php
// Ürünler listeleme sayfası

require_once __DIR__ . '/inc/config.php';

$db = Database::getInstance();

// Kategori filtresi
$categorySlug = $_GET['category'] ?? '';
$category = null;

if ($categorySlug) {
    $category = $db->fetchOne(
        "SELECT * FROM product_categories WHERE slug = ? AND is_active = 1",
        [$categorySlug]
    );

    if (!$category) {
        header('Location: ' . siteUrl('urunler'));
        exit;
    }
}

// Sayfa bilgileri
$pageTitle = $category ? $category['name'] : 'Ürünler';
$pageDescription = $category && $category['description']
    ? $category['description']
    : 'Ürünlerimizi keşfedin';

// Ürünleri getir
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

if ($category) {
    // Kategoriye göre filtrele
    $products = $db->fetchAll(
        "SELECT * FROM products
         WHERE is_active = 1 AND category_id = ?
         ORDER BY sort_order ASC, created_at DESC
         LIMIT ? OFFSET ?",
        [$category['id'], $perPage, $offset]
    );

    $totalProducts = $db->count('products', 'is_active = 1 AND category_id = ?', [$category['id']]);
} else {
    // Tüm ürünler
    $products = $db->fetchAll(
        "SELECT * FROM products
         WHERE is_active = 1
         ORDER BY sort_order ASC, created_at DESC
         LIMIT ? OFFSET ?",
        [$perPage, $offset]
    );

    $totalProducts = $db->count('products', 'is_active = 1');
}

// Tüm kategorileri getir
$categories = $db->fetchAll(
    "SELECT c.*, COUNT(p.id) as product_count
     FROM product_categories c
     LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.sort_order ASC, c.name ASC"
);

// Sayfalama
$totalPages = ceil($totalProducts / $perPage);

// SEO
$seoTitle = $category
    ? $category['name'] . ' - Ürünler | ' . $settings['site_title']
    : 'Ürünler | ' . $settings['site_title'];
$seoDescription = $pageDescription;

include __DIR__ . '/inc/header.php';
?>

<!-- Sayfa Başlığı -->
<section class="page-header bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2"><?= e($pageTitle) ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><a href="<?= siteUrl('urunler') ?>">Ürünler</a></li>
                        <?php if ($category): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($category['name']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">Tüm Ürünler</li>
                        <?php endif; ?>
                    </ol>
                </nav>
                <?php if ($category && $category['description']): ?>
                    <p class="lead text-muted"><?= e($category['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Ürünler -->
<section class="products py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar - Kategoriler -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kategoriler</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= siteUrl('urunler') ?>"
                           class="list-group-item list-group-item-action <?= !$category ? 'active' : '' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Tüm Ürünler</span>
                                <span class="badge bg-primary rounded-pill"><?= $totalProducts ?></span>
                            </div>
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['product_count'] > 0): ?>
                                <a href="<?= siteUrl('urunler/' . $cat['slug']) ?>"
                                   class="list-group-item list-group-item-action <?= $category && $category['id'] == $cat['id'] ? 'active' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?= e($cat['name']) ?></span>
                                        <span class="badge bg-secondary rounded-pill"><?= $cat['product_count'] ?></span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Ürün Listesi -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info text-center">
                        <i class="ti ti-info-circle me-2"></i>
                        <?= $category ? 'Bu kategoride henüz ürün bulunmuyor.' : 'Henüz ürün eklenmemiş.' ?>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="card h-100 shadow-sm product-card">
                                    <?php if ($product['featured_image']): ?>
                                        <a href="<?= siteUrl('urun/' . $product['slug']) ?>">
                                            <img src="<?= siteUrl('uploads/' . $product['featured_image']) ?>"
                                                 class="card-img-top"
                                                 alt="<?= e($product['title']) ?>"
                                                 style="height: 250px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                             style="height: 250px;">
                                            <i class="ti ti-photo-off text-muted" style="font-size: 48px;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($product['is_featured']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">Öne Çıkan</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">
                                            <a href="<?= siteUrl('urun/' . $product['slug']) ?>"
                                               class="text-decoration-none text-dark">
                                                <?= e($product['title']) ?>
                                            </a>
                                        </h5>

                                        <?php if ($product['short_description']): ?>
                                            <p class="card-text text-muted small mb-3">
                                                <?= e(truncate($product['short_description'], 100)) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="mt-auto">
                                            <?php if ($product['price']): ?>
                                                <div class="h5 text-primary mb-3">
                                                    <?= number_format($product['price'], 2, ',', '.') ?> ₺
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?= siteUrl('urun/' . $product['slug']) ?>"
                                               class="btn btn-primary w-100">
                                                Detayları Gör
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sayfalama -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Ürün sayfalama" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="?<?= $category ? 'category=' . $category['slug'] . '&' : '' ?>page=<?= $page - 1 ?>">
                                            Önceki
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link"
                                               href="?<?= $category ? 'category=' . $category['slug'] . '&' : '' ?>page=<?= $i ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php elseif (abs($i - $page) == 3): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                           href="?<?= $category ? 'category=' . $category['slug'] . '&' : '' ?>page=<?= $page + 1 ?>">
                                            Sonraki
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Ürün Kartları için CSS -->
<style>
.product-card {
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.product-card .card-img-top {
    transition: transform 0.3s;
}

.product-card:hover .card-img-top {
    transform: scale(1.05);
}

.product-card img {
    overflow: hidden;
}

.list-group-item.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
