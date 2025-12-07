<?php
// Dosya: /product.php
// Ürün detay sayfası

require_once __DIR__ . '/inc/config.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . siteUrl('urunler'));
    exit;
}

// Ürünü getir
$product = $db->fetchOne(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p
     LEFT JOIN product_categories c ON p.category_id = c.id
     WHERE p.slug = ? AND p.is_active = 1",
    [$slug]
);

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

// Görüntülenme sayısını artır
$db->query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$product['id']]);

// Galeri resimleri
$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true);
}

// İlgili ürünler (aynı kategoriden)
$relatedProducts = [];
if ($product['category_id']) {
    $relatedProducts = $db->fetchAll(
        "SELECT * FROM products
         WHERE category_id = ? AND id != ? AND is_active = 1
         ORDER BY RAND()
         LIMIT 4",
        [$product['category_id'], $product['id']]
    );
}

// SEO için pageData hazırla
$pageData = $product;
$pageType = 'product';
$pageTitle = $product['title'];

// Schema.org için ürün verisi
$schemaType = 'product';
$schemaData = $product;

include __DIR__ . '/inc/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="<?= siteUrl('urunler') ?>">Ürünler</a></li>
                <?php if ($product['category_name']): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= siteUrl('urunler/' . $product['category_slug']) ?>">
                            <?= e($product['category_name']) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= e($product['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Ürün Detay -->
<section class="product-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Ürün Görselleri -->
            <div class="col-lg-6 mb-4">
                <?php if ($product['featured_image'] || !empty($gallery)): ?>
                    <div class="product-images">
                        <!-- Ana Resim -->
                        <div class="main-image mb-3">
                            <img id="mainImage"
                                 src="<?= siteUrl('uploads/' . ($product['featured_image'] ?: $gallery[0])) ?>"
                                 class="img-fluid rounded shadow-sm w-100"
                                 alt="<?= e($product['title']) ?>"
                                 style="max-height: 500px; object-fit: contain; background: #f8f9fa;">
                        </div>

                        <!-- Galeri Thumbnail'leri -->
                        <?php if (!empty($gallery) || $product['featured_image']): ?>
                            <div class="gallery-thumbnails">
                                <div class="row g-2">
                                    <?php if ($product['featured_image']): ?>
                                        <div class="col-3">
                                            <img src="<?= siteUrl('uploads/thumbnails/' . basename($product['featured_image'])) ?>"
                                                 class="img-fluid rounded cursor-pointer thumbnail-img active"
                                                 data-full="<?= siteUrl('uploads/' . $product['featured_image']) ?>"
                                                 alt="<?= e($product['title']) ?>"
                                                 style="height: 100px; object-fit: cover; cursor: pointer;">
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($gallery)): ?>
                                        <?php foreach ($gallery as $image): ?>
                                            <div class="col-3">
                                                <img src="<?= siteUrl('uploads/thumbnails/' . basename($image)) ?>"
                                                     class="img-fluid rounded cursor-pointer thumbnail-img"
                                                     data-full="<?= siteUrl('uploads/' . $image) ?>"
                                                     alt="<?= e($product['title']) ?>"
                                                     style="height: 100px; object-fit: cover; cursor: pointer;">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                         style="height: 400px;">
                        <i class="ti ti-photo-off text-muted" style="font-size: 72px;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ürün Bilgileri -->
            <div class="col-lg-6">
                <div class="product-info">
                    <?php if ($product['is_featured']): ?>
                        <span class="badge bg-warning mb-3">Öne Çıkan Ürün</span>
                    <?php endif; ?>

                    <h1 class="display-5 fw-bold mb-3"><?= e($product['title']) ?></h1>

                    <?php if ($product['category_name']): ?>
                        <p class="text-muted mb-3">
                            <strong>Kategori:</strong>
                            <a href="<?= siteUrl('urunler/' . $product['category_slug']) ?>"
                               class="text-decoration-none">
                                <?= e($product['category_name']) ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if ($product['price']): ?>
                        <div class="price mb-4">
                            <h2 class="display-4 text-primary fw-bold">
                                <?= number_format($product['price'], 2, ',', '.') ?> ₺
                            </h2>
                        </div>
                    <?php endif; ?>

                    <?php if ($product['short_description']): ?>
                        <div class="short-description mb-4">
                            <p class="lead"><?= nl2br(e($product['short_description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- İletişim Butonları -->
                    <div class="action-buttons mb-4">
                        <div class="d-grid gap-2">
                            <a href="<?= siteUrl('iletisim') ?>" class="btn btn-primary btn-lg">
                                <i class="ti ti-mail me-2"></i>Ürün Hakkında Bilgi Al
                            </a>
                            <?php if (!empty($settings['whatsapp'])): ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp']) ?>?text=<?= urlencode($product['title'] . ' hakkında bilgi almak istiyorum.') ?>"
                                   class="btn btn-success btn-lg"
                                   target="_blank">
                                    <i class="ti ti-brand-whatsapp me-2"></i>WhatsApp ile İletişime Geç
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Ürün Bilgileri -->
                    <div class="product-meta">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="ti ti-eye me-2 text-muted"></i>
                                <strong><?= number_format($product['view_count']) ?></strong> kez görüntülendi
                            </li>
                            <li class="mb-2">
                                <i class="ti ti-calendar me-2 text-muted"></i>
                                Eklenme: <?= formatDate($product['created_at'], 'd F Y') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ürün Açıklaması -->
        <?php if ($product['description']): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Ürün Detayları</h3>
                        </div>
                        <div class="card-body">
                            <div class="product-description">
                                <?= $product['description'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- İlgili Ürünler -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4">Benzer Ürünler</h3>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm product-card">
                            <?php if ($related['featured_image']): ?>
                                <a href="<?= siteUrl('urun/' . $related['slug']) ?>">
                                    <img src="<?= siteUrl('uploads/thumbnails/' . basename($related['featured_image'])) ?>"
                                         class="card-img-top"
                                         alt="<?= e($related['title']) ?>"
                                         style="height: 200px; object-fit: cover;">
                                </a>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="<?= siteUrl('urun/' . $related['slug']) ?>"
                                       class="text-decoration-none text-dark">
                                        <?= e($related['title']) ?>
                                    </a>
                                </h5>
                                <?php if ($related['price']): ?>
                                    <p class="text-primary fw-bold mb-2">
                                        <?= number_format($related['price'], 2, ',', '.') ?> ₺
                                    </p>
                                <?php endif; ?>
                                <a href="<?= siteUrl('urun/' . $related['slug']) ?>" class="btn btn-sm btn-outline-primary">
                                    Detayları Gör
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Galeri Thumbnail Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail-img');
    const mainImage = document.getElementById('mainImage');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Tüm thumbnail'lerden active class'ı kaldır
            thumbnails.forEach(t => t.classList.remove('active'));

            // Tıklanan thumbnail'e active ekle
            this.classList.add('active');

            // Ana resmi değiştir
            mainImage.src = this.getAttribute('data-full');
        });
    });
});
</script>

<!-- Stil -->
<style>
.thumbnail-img {
    border: 2px solid transparent;
    transition: all 0.3s;
}

.thumbnail-img:hover,
.thumbnail-img.active {
    border-color: var(--bs-primary);
    opacity: 1;
}

.thumbnail-img:not(.active) {
    opacity: 0.6;
}

.product-card {
    transition: transform 0.3s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-description {
    line-height: 1.8;
}

.product-description img {
    max-width: 100%;
    height: auto;
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
