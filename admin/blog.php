<?php
// Dosya: /admin/blog.php
// Blog yazıları yönetimi - Tam CRUD + SEO

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Blog Yazıları';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$postId = (int)($_GET['id'] ?? 0);

// CRUD İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'save') {
        // Yeni kayıt veya güncelleme
        $id = (int)($_POST['id'] ?? 0);
        $slug = slugify($_POST['slug'] ?? $_POST['title']);

        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'slug' => $slug,
            'title' => trim($_POST['title']),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s'),
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
            'seo_canonical' => trim($_POST['seo_canonical'] ?? ''),
            'seo_robots' => $_POST['seo_robots'] ?? 'index,follow',
            'og_title' => trim($_POST['og_title'] ?? ''),
            'og_description' => trim($_POST['og_description'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Slug benzersizliği kontrolü
        if ($id > 0) {
            $existing = $db->fetchOne("SELECT id FROM posts WHERE slug = ? AND id != ?", [$slug, $id]);
        } else {
            $existing = $db->fetchOne("SELECT id FROM posts WHERE slug = ?", [$slug]);
        }

        if ($existing) {
            $slug .= '-' . time();
            $data['slug'] = $slug;
        }

        // Resim yükleme
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = uploadImage($_FILES['featured_image']);
            if ($upload) {
                // Eski resmi sil
                if ($id > 0) {
                    $oldPost = $db->fetchOne("SELECT featured_image FROM posts WHERE id = ?", [$id]);
                    if ($oldPost && $oldPost['featured_image']) {
                        deleteImage($oldPost['featured_image']);
                    }
                }
                $data['featured_image'] = $upload['original'];
            }
        }

        // OG Image yükleme
        if (!empty($_FILES['og_image']['name'])) {
            $upload = uploadImage($_FILES['og_image']);
            if ($upload) {
                if ($id > 0) {
                    $oldPost = $db->fetchOne("SELECT og_image FROM posts WHERE id = ?", [$id]);
                    if ($oldPost && $oldPost['og_image']) {
                        deleteImage($oldPost['og_image']);
                    }
                }
                $data['og_image'] = $upload['original'];
            }
        }

        if ($id > 0) {
            // Güncelle
            $db->update('posts', $data, 'id = ?', [$id]);
            flash('success', 'Blog yazısı başarıyla güncellendi.', 'success');
        } else {
            // Yeni kayıt
            $id = $db->insert('posts', $data);
            flash('success', 'Blog yazısı başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('blog.php?action=edit&id=' . $id));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Resimleri sil
            $post = $db->fetchOne("SELECT featured_image, og_image FROM posts WHERE id = ?", [$id]);
            if ($post) {
                if ($post['featured_image']) deleteImage($post['featured_image']);
                if ($post['og_image']) deleteImage($post['og_image']);
            }

            $db->delete('posts', 'id = ?', [$id]);
            flash('success', 'Blog yazısı silindi.', 'success');
        }
        redirect(adminUrl('blog.php'));
    }
}

// Liste görünümü
if ($action === 'list') {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;

    $search = $_GET['search'] ?? '';
    $categoryFilter = (int)($_GET['category'] ?? 0);
    $statusFilter = $_GET['status'] ?? '';

    $where = '1=1';
    $params = [];

    if ($search) {
        $where .= ' AND (p.title LIKE ? OR p.slug LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($categoryFilter > 0) {
        $where .= ' AND p.category_id = ?';
        $params[] = $categoryFilter;
    }

    if ($statusFilter === 'active') {
        $where .= ' AND p.is_active = 1';
    } elseif ($statusFilter === 'inactive') {
        $where .= ' AND p.is_active = 0';
    } elseif ($statusFilter === 'featured') {
        $where .= ' AND p.is_featured = 1';
    }

    $totalPosts = $db->count('posts p', $where, $params);
    $totalPages = ceil($totalPosts / $perPage);

    $posts = $db->fetchAll(
        "SELECT p.*, c.name as category_name
         FROM posts p
         LEFT JOIN post_categories c ON p.category_id = c.id
         WHERE $where
         ORDER BY p.published_at DESC, p.created_at DESC
         LIMIT $perPage OFFSET $offset",
        $params
    );

    // Kategorileri getir (filtre için)
    $categories = $db->fetchAll("SELECT * FROM post_categories WHERE is_active = 1 ORDER BY name ASC");

    $headerButtons = '<a href="' . adminUrl('blog.php?action=add') . '" class="btn btn-primary">Yeni Yazı Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Filtreler -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Yazı ara..." value="<?= e($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        <option value="featured" <?= $statusFilter === 'featured' ? 'selected' : '' ?>>Öne Çıkan</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrele</button>
                    <?php if ($search || $categoryFilter || $statusFilter): ?>
                        <a href="<?= adminUrl('blog.php') ?>" class="btn btn-link">Temizle</a>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <a href="<?= adminUrl('blog-categories.php') ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-category me-2"></i>Kategoriler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Blog Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Resim</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Yayın Tarihi</th>
                        <th>Görüntülenme</th>
                        <th>Durum</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Blog yazısı bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['featured_image']): ?>
                                        <img src="<?= siteUrl('uploads/thumbnails/' . basename($p['featured_image'])) ?>"
                                             class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 60px; height: 60px;">
                                            <i class="ti ti-article text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($p['title']) ?></div>
                                    <?php if ($p['is_featured']): ?>
                                        <span class="badge bg-yellow text-yellow-fg">Öne Çıkan</span>
                                    <?php endif; ?>
                                    <div class="text-muted small">
                                        <code><?= e($p['slug']) ?></code>
                                    </div>
                                </td>
                                <td>
                                    <?= e($p['category_name'] ?? '-') ?>
                                </td>
                                <td>
                                    <div class="small"><?= formatDate($p['published_at'] ?? $p['created_at'], 'd.m.Y') ?></div>
                                    <div class="text-muted small"><?= timeAgo($p['published_at'] ?? $p['created_at']) ?></div>
                                </td>
                                <td>
                                    <span class="text-muted"><?= number_format($p['view_count'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="<?= adminUrl('blog.php?action=edit&id=' . $p['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <a href="<?= siteUrl('blog/' . $p['slug']) ?>" class="btn btn-sm btn-ghost-secondary" target="_blank">
                                            Görüntüle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePost(<?= $p['id'] ?>, '<?= e($p['title']) ?>')">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <?= pagination($page, $totalPages, adminUrl('blog.php' . ($search ? '?search=' . urlencode($search) : '') . ($categoryFilter ? ($search ? '&' : '?') . 'category=' . $categoryFilter : '') . ($statusFilter ? (($search || $categoryFilter) ? '&' : '?') . 'status=' . $statusFilter : ''))) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Silme formu (gizli) -->
    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
    function deletePost(id, title) {
        if (confirm('"{title}" yazısını silmek istediğinize emin misiniz?'.replace('{title}', title))) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>

    <?php
    include __DIR__ . '/inc/footer.php';
}
// Ekleme / Düzenleme formu
elseif ($action === 'add' || $action === 'edit') {
    $post = null;

    if ($action === 'edit' && $postId > 0) {
        $post = $db->fetchOne("SELECT * FROM posts WHERE id = ?", [$postId]);
        if (!$post) {
            flash('error', 'Blog yazısı bulunamadı.', 'error');
            redirect(adminUrl('blog.php'));
        }
        $pageTitle = 'Blog Yazısı Düzenle';
    } else {
        $pageTitle = 'Yeni Blog Yazısı';
        $post = [
            'id' => 0,
            'category_id' => null,
            'slug' => '',
            'title' => '',
            'excerpt' => '',
            'content' => '',
            'featured_image' => '',
            'published_at' => date('Y-m-d H:i:s'),
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'seo_canonical' => '',
            'seo_robots' => 'index,follow',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'is_featured' => 0,
            'is_active' => 1
        ];
    }

    // Kategorileri getir
    $categories = $db->fetchAll("SELECT * FROM post_categories WHERE is_active = 1 ORDER BY name ASC");

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">

        <div class="row">
            <div class="col-lg-8">
                <!-- Ana İçerik -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Yazı Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Başlık</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= e($post['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($post['slug']) ?>" required>
                            <small class="form-hint">URL'de görünecek kısım (örn: yazi-basligi)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Özet</label>
                            <textarea name="excerpt" class="form-control" rows="3" maxlength="500"><?= e($post['excerpt']) ?></textarea>
                            <small class="form-hint">Listelerde görünecek özet (maks 500 karakter)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea name="content" id="content" class="form-control" rows="20"><?= e($post['content']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO Ayarları -->
                <?php include __DIR__ . '/inc/seo-box.php'; ?>
            </div>

            <div class="col-lg-4">
                <!-- Yayınlama -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Yayınlama</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Kategorisiz</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yayın Tarihi</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                   value="<?= date('Y-m-d\TH:i', strtotime($post['published_at'])) ?>">
                            <small class="form-hint">Gelecek tarih için planlama yapabilirsiniz</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $post['is_active'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" value="1" <?= $post['is_featured'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Öne Çıkan</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
                    </div>
                </div>

                <!-- Öne Çıkan Resim -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Öne Çıkan Resim</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($post['featured_image']): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $post['featured_image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                        <small class="form-hint">JPG, PNG, GIF - Maks 5MB</small>
                    </div>
                </div>

                <!-- Bilgiler -->
                <?php if ($post['id'] > 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Oluşturma</div>
                                    <div class="datagrid-content"><?= formatDate($post['created_at'] ?? '') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Güncelleme</div>
                                    <div class="datagrid-content"><?= formatDate($post['updated_at'] ?? '') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Görüntülenme</div>
                                    <div class="datagrid-content"><?= number_format($post['view_count'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php
    $customJs = <<<'JS'
    <script>
    // TinyMCE başlat
    tinymce.init({
        selector: '#content',
        height: 600,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; line-height: 1.6; }',
        language: 'tr_TR'
    });
    </script>
    JS;

    include __DIR__ . '/inc/footer.php';
}
?>
