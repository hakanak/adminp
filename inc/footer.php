<?php
// Dosya: /inc/footer.php
// Frontend footer

$settings = $settings ?? getSettings();
?>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?= e($settings['site_title']) ?></h5>
                    <?php if (!empty($settings['site_slogan'])): ?>
                        <p class="text-muted"><?= e($settings['site_slogan']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-4">
                    <h5>İletişim</h5>
                    <?php if (!empty($settings['address'])): ?>
                        <p class="text-muted small">
                            <i class="bi bi-geo-alt"></i>
                            <?= nl2br(e($settings['address'])) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($settings['phone'])): ?>
                        <p class="text-muted small">
                            <i class="bi bi-telephone"></i>
                            <a href="tel:<?= e($settings['phone']) ?>" class="text-muted"><?= e($settings['phone']) ?></a>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($settings['email'])): ?>
                        <p class="text-muted small">
                            <i class="bi bi-envelope"></i>
                            <a href="mailto:<?= e($settings['email']) ?>" class="text-muted"><?= e($settings['email']) ?></a>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-4">
                    <h5>Sosyal Medya</h5>
                    <div class="d-flex gap-3">
                        <?php if (!empty($settings['facebook'])): ?>
                            <a href="<?= e($settings['facebook']) ?>" target="_blank" class="text-white">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['instagram'])): ?>
                            <a href="<?= e($settings['instagram']) ?>" target="_blank" class="text-white">
                                <i class="bi bi-instagram"></i> Instagram
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['twitter'])): ?>
                            <a href="<?= e($settings['twitter']) ?>" target="_blank" class="text-white">
                                <i class="bi bi-twitter"></i> Twitter
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['linkedin'])): ?>
                            <a href="<?= e($settings['linkedin']) ?>" target="_blank" class="text-white">
                                <i class="bi bi-linkedin"></i> LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom bg-darker py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 small text-muted">
                            <?php if (!empty($settings['footer_text'])): ?>
                                <?= e($settings['footer_text']) ?>
                            <?php else: ?>
                                &copy; <?= date('Y') ?> <?= e($settings['site_title']) ?>. Tüm hakları saklıdır.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0 small text-muted">
                            <a href="<?= adminUrl() ?>" class="text-muted">Admin Paneli</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <?php if (!empty($settings['whatsapp'])): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp']) ?>"
           class="whatsapp-float" target="_blank" title="WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom JS -->
    <script src="<?= siteUrl('assets/js/main.js') ?>"></script>
</body>
</html>
