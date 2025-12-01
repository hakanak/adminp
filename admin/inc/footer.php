<?php
// Dosya: /admin/inc/footer.php
?>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    <a href="<?= SITE_URL ?>" target="_blank" class="link-secondary">Siteyi Görüntüle</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="<?= adminUrl('seo-tools.php') ?>" class="link-secondary">SEO Araçları</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    &copy; <?= date('Y') ?> <?= e($settings['site_title']) ?>
                                </li>
                                <li class="list-inline-item">
                                    PHP CMS v1.0
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Tabler JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>

    <!-- jQuery (TinyMCE için) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/khes9v6v3wwxia7hwudfjapzpx5lyc07b2u65yvfsxwxi6h8/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- Custom Admin JS -->
    <script src="<?= adminUrl('assets/js/admin.js') ?>"></script>

    <?php if (isset($customJs)): ?>
        <?= $customJs ?>
    <?php endif; ?>
</body>
</html>
