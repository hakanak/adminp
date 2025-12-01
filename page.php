<?php
// Dosya: /page.php
// Dinamik sayfalar

require_once __DIR__ . '/inc/config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('HTTP/1.1 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$db = Database::getInstance();
$page = $db->fetchOne(
    "SELECT * FROM pages WHERE slug = ? AND is_active = 1",
    [$slug]
);

if (!$page) {
    header('HTTP/1.1 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $page['title'];
$pageData = $page;
$pageType = 'page';

include __DIR__ . '/inc/header.php';
?>

<div class="container py-5">
    <article class="page-content">
        <?php if ($page['featured_image']): ?>
            <div class="mb-4">
                <img src="<?= siteUrl('uploads/' . $page['featured_image']) ?>"
                     class="img-fluid rounded" alt="<?= e($page['title']) ?>">
            </div>
        <?php endif; ?>

        <h1 class="mb-4"><?= e($page['title']) ?></h1>

        <div class="content">
            <?= $page['content'] ?>
        </div>

        <div class="page-meta mt-5 pt-3 border-top text-muted small">
            <p>Son güncelleme: <?= formatDate($page['updated_at'], 'd F Y') ?></p>
        </div>
    </article>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
