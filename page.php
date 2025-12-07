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

<!-- Breadcrumb -->
<section class="breadcrumb-section bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($page['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Sayfa İçeriği -->
<section class="page-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <article class="page-content">
                    <?php if ($page['featured_image']): ?>
                        <div class="featured-image mb-4">
                            <img src="<?= siteUrl('uploads/' . $page['featured_image']) ?>"
                                 class="img-fluid rounded shadow-sm"
                                 alt="<?= e($page['title']) ?>"
                                 style="width: 100%; max-height: 500px; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <h1 class="display-4 fw-bold mb-4"><?= e($page['title']) ?></h1>

                    <div class="content">
                        <?= $page['content'] ?>
                    </div>

                    <div class="page-meta mt-5 pt-3 border-top text-muted small">
                        <p class="mb-0">
                            <i class="ti ti-calendar me-2"></i>
                            Son güncelleme: <?= formatDate($page['updated_at'], 'd F Y') ?>
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Stil -->
<style>
.content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.content h1,
.content h2,
.content h3,
.content h4,
.content h5,
.content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.content h2 {
    font-size: 2rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.content h3 {
    font-size: 1.5rem;
}

.content p {
    margin-bottom: 1.5rem;
}

.content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.content ul,
.content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.content ul li,
.content ol li {
    margin-bottom: 0.5rem;
}

.content blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.content a {
    color: var(--bs-primary);
    text-decoration: underline;
}

.content a:hover {
    color: var(--bs-primary);
    opacity: 0.8;
}

.content table {
    width: 100%;
    margin-bottom: 1.5rem;
    border-collapse: collapse;
}

.content table th,
.content table td {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
}

.content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: monospace;
}

.content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
}

.content pre code {
    background-color: transparent;
    padding: 0;
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
