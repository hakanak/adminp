<?php
// Dosya: /galeri.php
// Galeri sayfası

require_once __DIR__ . '/inc/config.php';

$pageTitle = 'Galeri';
$db = Database::getInstance();

// Aktif galeri öğelerini getir
$gallery = $db->fetchAll(
    "SELECT * FROM gallery WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC"
);

// SEO meta bilgileri
$seoTitle = 'Galeri - ' . $settings['site_title'];
$seoDescription = 'Foto galeri, projelerimiz ve çalışmalarımızdan görseller';

include __DIR__ . '/inc/header.php';
?>

<!-- Sayfa Başlığı -->
<section class="page-header bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">Galeri</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Galeri</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Galeri Grid -->
<section class="gallery py-5">
    <div class="container">
        <?php if (empty($gallery)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ti ti-info-circle me-2"></i>
                        Henüz galeri görseli eklenmemiş.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4" id="galleryGrid">
                <?php foreach ($gallery as $item): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3 gallery-item">
                        <div class="card h-100 shadow-sm">
                            <a href="<?= siteUrl('uploads/' . $item['image']) ?>"
                               data-lightbox="gallery"
                               data-title="<?= e($item['title']) ?><?= $item['description'] ? ' - ' . e($item['description']) : '' ?>">
                                <div class="card-img-top position-relative overflow-hidden">
                                    <img src="<?= siteUrl('uploads/thumbnails/' . basename($item['image'])) ?>"
                                         class="w-100 gallery-image"
                                         alt="<?= e($item['title']) ?>"
                                         style="height: 250px; object-fit: cover; transition: transform 0.3s;">
                                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                         style="background: rgba(0,0,0,0.5); opacity: 0; transition: opacity 0.3s;">
                                        <i class="ti ti-zoom-in text-white" style="font-size: 48px;"></i>
                                    </div>
                                </div>
                            </a>
                            <div class="card-body">
                                <h5 class="card-title mb-1"><?= e($item['title']) ?></h5>
                                <?php if ($item['description']): ?>
                                    <p class="card-text text-muted small mb-0"><?= e($item['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css">

<!-- Custom Gallery Styles -->
<style>
.gallery-item .card {
    transition: transform 0.3s, box-shadow 0.3s;
}

.gallery-item .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.gallery-item a:hover .gallery-image {
    transform: scale(1.1);
}

.gallery-item a:hover .gallery-overlay {
    opacity: 1 !important;
}

.gallery-item .card-body {
    min-height: 80px;
}
</style>

<!-- Lightbox2 JS -->
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<script>
// Lightbox ayarları
lightbox.option({
    'resizeDuration': 200,
    'wrapAround': true,
    'albumLabel': 'Resim %1 / %2',
    'fadeDuration': 300,
    'imageFadeDuration': 300
});
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>
