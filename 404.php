<?php
// Dosya: /404.php
// 404 Hata sayfası

require_once __DIR__ . '/inc/config.php';

$pageTitle = '404 - Sayfa Bulunamadı';
http_response_code(404);

include __DIR__ . '/inc/header.php';
?>

<section class="error-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-content py-5">
                    <h1 class="display-1 fw-bold text-primary mb-4">404</h1>
                    <h2 class="mb-4">Sayfa Bulunamadı</h2>
                    <p class="lead text-muted mb-4">
                        Aradığınız sayfa taşınmış, silinmiş veya hiç var olmamış olabilir.
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="<?= siteUrl() ?>" class="btn btn-primary btn-lg">
                            <i class="ti ti-home me-2"></i>Ana Sayfaya Dön
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="ti ti-arrow-left me-2"></i>Geri Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
