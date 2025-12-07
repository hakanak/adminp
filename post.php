<?php
// Dosya: /post.php
// Blog yazısı detay sayfası

require_once __DIR__ . '/inc/config.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . siteUrl('blog'));
    exit;
}

// Yazıyı getir
$post = $db->fetchOne(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM posts p
     LEFT JOIN post_categories c ON p.category_id = c.id
     WHERE p.slug = ? AND p.is_active = 1",
    [$slug]
);

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

// Görüntülenme sayısını artır
$db->query("UPDATE posts SET view_count = view_count + 1 WHERE id = ?", [$post['id']]);

// İlgili yazılar (aynı kategoriden)
$relatedPosts = [];
if ($post['category_id']) {
    $relatedPosts = $db->fetchAll(
        "SELECT * FROM posts
         WHERE category_id = ? AND id != ? AND is_active = 1
         ORDER BY published_at DESC
         LIMIT 3",
        [$post['category_id'], $post['id']]
    );
}

// SEO için pageData hazırla
$pageData = $post;
$pageType = 'article';
$pageTitle = $post['title'];

// Schema.org için yazı verisi
$schemaType = 'article';
$schemaData = $post;

include __DIR__ . '/inc/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section bg-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="<?= siteUrl('blog') ?>">Blog</a></li>
                <?php if ($post['category_name']): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= siteUrl('kategori/' . $post['category_slug']) ?>">
                            <?= e($post['category_name']) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= e(truncate($post['title'], 50)) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Blog Yazısı -->
<section class="post-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Ana İçerik -->
            <div class="col-lg-8">
                <article class="post-content">
                    <!-- Kategori ve Tarih -->
                    <div class="post-meta mb-3">
                        <?php if ($post['category_name']): ?>
                            <a href="<?= siteUrl('kategori/' . $post['category_slug']) ?>"
                               class="badge bg-primary text-decoration-none me-2">
                                <?= e($post['category_name']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($post['is_featured']): ?>
                            <span class="badge bg-warning me-2">Öne Çıkan</span>
                        <?php endif; ?>
                        <span class="text-muted small">
                            <i class="ti ti-calendar me-1"></i>
                            <?= formatDate($post['published_at'] ?? $post['created_at'], 'd F Y') ?>
                        </span>
                        <span class="text-muted small ms-3">
                            <i class="ti ti-eye me-1"></i>
                            <?= number_format($post['view_count']) ?> görüntülenme
                        </span>
                    </div>

                    <!-- Başlık -->
                    <h1 class="display-5 fw-bold mb-4"><?= e($post['title']) ?></h1>

                    <!-- Öne Çıkan Görsel -->
                    <?php if ($post['featured_image']): ?>
                        <div class="featured-image mb-4">
                            <img src="<?= siteUrl('uploads/' . $post['featured_image']) ?>"
                                 class="img-fluid rounded shadow-sm"
                                 alt="<?= e($post['title']) ?>"
                                 style="width: 100%; max-height: 500px; object-fit: cover;">
                        </div>
                    <?php endif; ?>

                    <!-- Özet -->
                    <?php if ($post['excerpt']): ?>
                        <div class="post-excerpt mb-4 p-3 bg-light rounded">
                            <p class="lead mb-0"><?= nl2br(e($post['excerpt'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- İçerik -->
                    <div class="post-body">
                        <?= $post['content'] ?>
                    </div>

                    <!-- Paylaş -->
                    <div class="post-share mt-5 pt-4 border-top">
                        <h5 class="mb-3">Bu yazıyı paylaş</h5>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(currentUrl()) ?>"
                               target="_blank"
                               class="btn btn-outline-primary">
                                <i class="ti ti-brand-facebook me-2"></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(currentUrl()) ?>&text=<?= urlencode($post['title']) ?>"
                               target="_blank"
                               class="btn btn-outline-info">
                                <i class="ti ti-brand-twitter me-2"></i>Twitter
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(currentUrl()) ?>&title=<?= urlencode($post['title']) ?>"
                               target="_blank"
                               class="btn btn-outline-primary">
                                <i class="ti ti-brand-linkedin me-2"></i>LinkedIn
                            </a>
                            <?php if (!empty($settings['whatsapp'])): ?>
                                <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . currentUrl()) ?>"
                                   target="_blank"
                                   class="btn btn-outline-success">
                                    <i class="ti ti-brand-whatsapp me-2"></i>WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

                <!-- İlgili Yazılar -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="related-posts mt-5 pt-5 border-top">
                        <h4 class="mb-4">İlgili Yazılar</h4>
                        <div class="row g-4">
                            <?php foreach ($relatedPosts as $related): ?>
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm">
                                        <?php if ($related['featured_image']): ?>
                                            <a href="<?= siteUrl('blog/' . $related['slug']) ?>">
                                                <img src="<?= siteUrl('uploads/thumbnails/' . basename($related['featured_image'])) ?>"
                                                     class="card-img-top"
                                                     alt="<?= e($related['title']) ?>"
                                                     style="height: 150px; object-fit: cover;">
                                            </a>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="<?= siteUrl('blog/' . $related['slug']) ?>"
                                                   class="text-decoration-none text-dark">
                                                    <?= e(truncate($related['title'], 60)) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?= formatDate($related['published_at'] ?? $related['created_at'], 'd M Y') ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Son Yazılar -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Son Yazılar</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php
                            $recentPosts = $db->fetchAll(
                                "SELECT * FROM posts WHERE is_active = 1 AND id != ? ORDER BY published_at DESC LIMIT 5",
                                [$post['id']]
                            );
                            foreach ($recentPosts as $recent):
                            ?>
                                <a href="<?= siteUrl('blog/' . $recent['slug']) ?>"
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex">
                                        <?php if ($recent['featured_image']): ?>
                                            <img src="<?= siteUrl('uploads/thumbnails/' . basename($recent['featured_image'])) ?>"
                                                 class="rounded me-3"
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 alt="<?= e($recent['title']) ?>">
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= e(truncate($recent['title'], 50)) ?></h6>
                                            <small class="text-muted">
                                                <?= formatDate($recent['published_at'] ?? $recent['created_at'], 'd M Y') ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Kategoriler -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kategoriler</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php
                        $categories = $db->fetchAll(
                            "SELECT c.*, COUNT(p.id) as post_count
                             FROM post_categories c
                             LEFT JOIN posts p ON c.id = p.category_id AND p.is_active = 1
                             WHERE c.is_active = 1
                             GROUP BY c.id
                             HAVING post_count > 0
                             ORDER BY c.name ASC"
                        );
                        foreach ($categories as $cat):
                        ?>
                            <a href="<?= siteUrl('kategori/' . $cat['slug']) ?>"
                               class="list-group-item list-group-item-action <?= $post['category_id'] == $cat['id'] ? 'active' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><?= e($cat['name']) ?></span>
                                    <span class="badge bg-secondary rounded-pill"><?= $cat['post_count'] ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stil -->
<style>
.post-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.post-body h1,
.post-body h2,
.post-body h3,
.post-body h4,
.post-body h5,
.post-body h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.post-body h2 {
    font-size: 2rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.post-body h3 {
    font-size: 1.5rem;
}

.post-body p {
    margin-bottom: 1.5rem;
}

.post-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.post-body ul,
.post-body ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.post-body ul li,
.post-body ol li {
    margin-bottom: 0.5rem;
}

.post-body blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.post-body a {
    color: var(--bs-primary);
}

.post-body table {
    width: 100%;
    margin-bottom: 1.5rem;
    border-collapse: collapse;
}

.post-body table th,
.post-body table td {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
}

.post-body table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.list-group-item.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
