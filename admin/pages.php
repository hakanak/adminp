<?php
// Dosya: /admin/pages.php
// Sayfalar yönetimi - Tam CRUD + SEO

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Sayfalar';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$pageId = (int)($_GET['id'] ?? 0);

// CRUD İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'save') {
        // Yeni kayıt veya güncelleme
        $id = (int)($_POST['id'] ?? 0);
        $slug = slugify($_POST['slug'] ?? $_POST['title']);

        $data = [
            'slug' => $slug,
            'title' => trim($_POST['title']),
            'content' => $_POST['content'] ?? '',
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
            'seo_canonical' => trim($_POST['seo_canonical'] ?? ''),
            'seo_robots' => $_POST['seo_robots'] ?? 'index,follow',
            'og_title' => trim($_POST['og_title'] ?? ''),
            'og_description' => trim($_POST['og_description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_in_menu' => isset($_POST['is_in_menu']) ? 1 : 0,
            'menu_order' => (int)($_POST['menu_order'] ?? 0)
        ];

        // Slug benzersizliği kontrolü
        if ($id > 0) {
            $existing = $db->fetchOne("SELECT id FROM pages WHERE slug = ? AND id != ?", [$slug, $id]);
        } else {
            $existing = $db->fetchOne("SELECT id FROM pages WHERE slug = ?", [$slug]);
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
                    $oldPage = $db->fetchOne("SELECT featured_image FROM pages WHERE id = ?", [$id]);
                    if ($oldPage && $oldPage['featured_image']) {
                        deleteImage($oldPage['featured_image']);
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
                    $oldPage = $db->fetchOne("SELECT og_image FROM pages WHERE id = ?", [$id]);
                    if ($oldPage && $oldPage['og_image']) {
                        deleteImage($oldPage['og_image']);
                    }
                }
                $data['og_image'] = $upload['original'];
            }
        }

        if ($id > 0) {
            // Güncelle
            $db->update('pages', $data, 'id = ?', [$id]);
            flash('success', 'Sayfa başarıyla güncellendi.', 'success');
        } else {
            // Yeni kayıt
            $id = $db->insert('pages', $data);
            flash('success', 'Sayfa başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('pages.php?action=edit&id=' . $id));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Resmi sil
            $page = $db->fetchOne("SELECT featured_image, og_image FROM pages WHERE id = ?", [$id]);
            if ($page) {
                if ($page['featured_image']) deleteImage($page['featured_image']);
                if ($page['og_image']) deleteImage($page['og_image']);
            }

            $db->delete('pages', 'id = ?', [$id]);
            flash('success', 'Sayfa silindi.', 'success');
        }
        redirect(adminUrl('pages.php'));
    }
}

// Liste görünümü
if ($action === 'list') {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;

    $search = $_GET['search'] ?? '';
    $where = '1=1';
    $params = [];

    if ($search) {
        $where .= ' AND (title LIKE ? OR slug LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $totalPages = ceil($db->count('pages', $where, $params) / $perPage);
    $pages = $db->fetchAll(
        "SELECT * FROM pages WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
        $params
    );

    $headerButtons = '<a href="' . adminUrl('pages.php?action=add') . '" class="btn btn-primary">Yeni Sayfa Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Arama -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-auto flex-fill">
                    <input type="text" name="search" class="form-control" placeholder="Sayfa ara..." value="<?= e($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Ara</button>
                    <?php if ($search): ?>
                        <a href="<?= adminUrl('pages.php') ?>" class="btn btn-link">Temizle</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Sayfalar Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>Başlık</th>
                        <th>Slug</th>
                        <th>Durum</th>
                        <th>Menüde</th>
                        <th>Oluşturma</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pages)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sayfa bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= e($p['title']) ?></div>
                                    <?php if ($p['seo_title']): ?>
                                        <div class="text-muted small">SEO: <?= e(truncate($p['seo_title'], 50)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?= e($p['slug']) ?></code>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['is_in_menu']): ?>
                                        <span class="badge bg-info">Evet</span>
                                    <?php else: ?>
                                        <span class="text-muted">Hayır</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small"><?= formatDate($p['created_at'], 'd.m.Y') ?></div>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="<?= adminUrl('pages.php?action=edit&id=' . $p['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <a href="<?= siteUrl($p['slug']) ?>" class="btn btn-sm btn-ghost-secondary" target="_blank">
                                            Görüntüle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePage(<?= $p['id'] ?>, '<?= e($p['title']) ?>')">
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
                <?= pagination($page, $totalPages, adminUrl('pages.php' . ($search ? '?search=' . urlencode($search) : ''))) ?>
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
    function deletePage(id, title) {
        if (confirm('"{title}" sayfasını silmek istediğinize emin misiniz?'.replace('{title}', title))) {
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
    $page = null;

    if ($action === 'edit' && $pageId > 0) {
        $page = $db->fetchOne("SELECT * FROM pages WHERE id = ?", [$pageId]);
        if (!$page) {
            flash('error', 'Sayfa bulunamadı.', 'error');
            redirect(adminUrl('pages.php'));
        }
        $pageTitle = 'Sayfa Düzenle';
    } else {
        $pageTitle = 'Yeni Sayfa';
        $page = [
            'id' => 0,
            'slug' => '',
            'title' => '',
            'content' => '',
            'featured_image' => '',
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'seo_canonical' => '',
            'seo_robots' => 'index,follow',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'is_active' => 1,
            'is_in_menu' => 0,
            'menu_order' => 0
        ];
    }

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $page['id'] ?>">

        <div class="row">
            <div class="col-lg-8">
                <!-- Ana İçerik -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Sayfa Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Başlık</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= e($page['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($page['slug']) ?>" required>
                            <small class="form-hint">URL'de görünecek kısım (örn: hakkimizda)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea name="content" id="content" class="form-control" rows="15"><?= e($page['content']) ?></textarea>
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
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $page['is_active'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_in_menu" class="form-check-input" value="1" <?= $page['is_in_menu'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Menüde Göster</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Menü Sırası</label>
                            <input type="number" name="menu_order" class="form-control" value="<?= $page['menu_order'] ?>" min="0">
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
                        <?php if ($page['featured_image']): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $page['featured_image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                        <small class="form-hint">JPG, PNG, GIF - Maks 5MB</small>
                    </div>
                </div>

                <!-- Bilgiler -->
                <?php if ($page['id'] > 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Oluşturma</div>
                                    <div class="datagrid-content"><?= formatDate($page['created_at'] ?? '') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Güncelleme</div>
                                    <div class="datagrid-content"><?= formatDate($page['updated_at'] ?? '') ?></div>
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
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        language: 'tr_TR'
    });
    </script>
    JS;

    include __DIR__ . '/inc/footer.php';
}
?>
