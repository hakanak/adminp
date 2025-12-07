<?php
// Dosya: /blog.php
// Blog listeleme sayfası

require_once __DIR__ . '/inc/config.php';

$db = Database::getInstance();

// Kategori filtresi
$categorySlug = $_GET['category'] ?? '';
$category = null;

if ($categorySlug) {
    $category = $db->fetchOne(
        "SELECT * FROM post_categories WHERE slug = ? AND is_active = 1",
        [$categorySlug]
    );

    if (!$category) {
        header('Location: ' . siteUrl('blog'));
        exit;
    }
}

// Sayfa bilgileri
$pageTitle = $category ? $category['name'] : 'Blog';
$pageDescription = $category && $category['description']
    ? $category['description']
    : 'Blog yazılarımızı okuyun';

// Blog yazılarını getir
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

if ($category) {
    // Kategoriye göre filtrele
    $posts = $db->fetchAll(
        "SELECT p.*, c.name as category_name, c.slug as category_slug
         FROM posts p
         LEFT JOIN post_categories c ON p.category_id = c.id
         WHERE p.is_active = 1 AND p.category_id = ?
         ORDER BY p.published_at DESC, p.created_at DESC
         LIMIT ? OFFSET ?",
        [$category['id'], $perPage, $offset]
    );

    $totalPosts = $db->count('posts', 'is_active = 1 AND category_id = ?', [$category['id']]);
} else {
    // Tüm yazılar
    $posts = $db->fetchAll(
        "SELECT p.*, c.name as category_name, c.slug as category_slug
         FROM posts p
         LEFT JOIN post_categories c ON p.category_id = c.id
         WHERE p.is_active = 1
         ORDER BY p.published_at DESC, p.created_at DESC
         LIMIT ? OFFSET ?",
        [$perPage, $offset]
    );

    $totalPosts = $db->count('posts', 'is_active = 1');
}

// Tüm kategorileri getir
$categories = $db->fetchAll(
    "SELECT c.*, COUNT(p.id) as post_count
     FROM post_categories c
     LEFT JOIN posts p ON c.id = p.category_id AND p.is_active = 1
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.sort_order ASC, c.name ASC"
);

// Öne çıkan yazılar (sidebar için)
$featuredPosts = $db->fetchAll(
    "SELECT * FROM posts
     WHERE is_active = 1 AND is_featured = 1
     ORDER BY published_at DESC
     LIMIT 5"
);

// Sayfalama
$totalPages = ceil($totalPosts / $perPage);

// SEO
$seoTitle = $category
    ? $category['name'] . ' - Blog | ' . $settings['site_title']
    : 'Blog | ' . $settings['site_title'];
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
                        <li class="breadcrumb-item"><a href="<?= siteUrl('blog') ?>">Blog</a></li>
                        <?php if ($category): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($category['name']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">Tüm Yazılar</li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Blog -->
<section class="blog py-5">
    <div class="container">
        <div class="row">
            <!-- Blog Yazıları -->
            <div class="col-lg-8 mb-4">
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info text-center">
                        <i class="ti ti-info-circle me-2"></i>
                        <?= $category ? 'Bu kategoride henüz yazı bulunmuyor.' : 'Henüz blog yazısı eklenmemiş.' ?>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6">
                                <article class="card h-100 shadow-sm blog-card">
                                    <?php if ($post['featured_image']): ?>
                                        <a href="<?= siteUrl('blog/' . $post['slug']) ?>">
                                            <img src="<?= siteUrl('uploads/' . $post['featured_image']) ?>"
                                                 class="card-img-top"
                                                 alt="<?= e($post['title']) ?>"
                                                 style="height: 250px; object-fit: cover;">
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($post['is_featured']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">Öne Çıkan</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <?php if ($post['category_name']): ?>
                                            <div class="mb-2">
                                                <a href="<?= siteUrl('kategori/' . $post['category_slug']) ?>"
                                                   class="badge bg-primary text-decoration-none">
                                                    <?= e($post['category_name']) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <h5 class="card-title">
                                            <a href="<?= siteUrl('blog/' . $post['slug']) ?>"
                                               class="text-decoration-none text-dark">
                                                <?= e($post['title']) ?>
                                            </a>
                                        </h5>

                                        <?php if ($post['excerpt']): ?>
                                            <p class="card-text text-muted mb-3">
                                                <?= e(truncate($post['excerpt'], 150)) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="ti ti-calendar me-1"></i>
                                                    <?= formatDate($post['published_at'] ?? $post['created_at'], 'd F Y') ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="ti ti-eye me-1"></i>
                                                    <?= number_format($post['view_count']) ?>
                                                </small>
                                            </div>
                                            <a href="<?= siteUrl('blog/' . $post['slug']) ?>"
                                               class="btn btn-outline-primary btn-sm mt-3 w-100">
                                                Devamını Oku
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sayfalama -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Blog sayfalama" class="mt-5">
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

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Kategoriler -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kategoriler</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= siteUrl('blog') ?>"
                           class="list-group-item list-group-item-action <?= !$category ? 'active' : '' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Tüm Yazılar</span>
                                <span class="badge bg-primary rounded-pill"><?= $totalPosts ?></span>
                            </div>
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['post_count'] > 0): ?>
                                <a href="<?= siteUrl('kategori/' . $cat['slug']) ?>"
                                   class="list-group-item list-group-item-action <?= $category && $category['id'] == $cat['id'] ? 'active' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?= e($cat['name']) ?></span>
                                        <span class="badge bg-secondary rounded-pill"><?= $cat['post_count'] ?></span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Öne Çıkan Yazılar -->
                <?php if (!empty($featuredPosts)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Öne Çıkan Yazılar</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($featuredPosts as $featured): ?>
                                    <a href="<?= siteUrl('blog/' . $featured['slug']) ?>"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <?php if ($featured['featured_image']): ?>
                                                <img src="<?= siteUrl('uploads/thumbnails/' . basename($featured['featured_image'])) ?>"
                                                     class="rounded me-3"
                                                     style="width: 60px; height: 60px; object-fit: cover;"
                                                     alt="<?= e($featured['title']) ?>">
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= e(truncate($featured['title'], 50)) ?></h6>
                                                <small class="text-muted">
                                                    <?= formatDate($featured['published_at'] ?? $featured['created_at'], 'd M Y') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Blog Kartları için CSS -->
<style>
.blog-card {
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.blog-card .card-img-top {
    transition: transform 0.3s;
}

.blog-card:hover .card-img-top {
    transform: scale(1.05);
}

.list-group-item.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
