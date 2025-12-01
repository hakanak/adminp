<?php
// Dosya: /admin/blog-categories.php
// Blog kategorileri yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Blog Kategorileri';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$categoryId = (int)($_GET['id'] ?? 0);

// CRUD İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'save') {
        // Yeni kayıt veya güncelleme
        $id = (int)($_POST['id'] ?? 0);
        $slug = slugify($_POST['slug'] ?? $_POST['name']);

        $data = [
            'slug' => $slug,
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Slug benzersizliği kontrolü
        if ($id > 0) {
            $existing = $db->fetchOne("SELECT id FROM post_categories WHERE slug = ? AND id != ?", [$slug, $id]);
        } else {
            $existing = $db->fetchOne("SELECT id FROM post_categories WHERE slug = ?", [$slug]);
        }

        if ($existing) {
            $slug .= '-' . time();
            $data['slug'] = $slug;
        }

        if ($id > 0) {
            // Güncelle
            $db->update('post_categories', $data, 'id = ?', [$id]);
            flash('success', 'Kategori başarıyla güncellendi.', 'success');
        } else {
            // Yeni kayıt
            $id = $db->insert('post_categories', $data);
            flash('success', 'Kategori başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('blog-categories.php'));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Bu kategoriye ait yazı var mı kontrol et
            $postCount = $db->count('posts', 'category_id = ?', [$id]);

            if ($postCount > 0) {
                flash('error', "Bu kategoriye ait {$postCount} yazı var. Önce yazıları silin veya başka kategoriye taşıyın.", 'error');
            } else {
                $db->delete('post_categories', 'id = ?', [$id]);
                flash('success', 'Kategori silindi.', 'success');
            }
        }
        redirect(adminUrl('blog-categories.php'));
    }
}

// Liste görünümü
if ($action === 'list') {
    // Tüm kategorileri getir
    $categories = $db->fetchAll(
        "SELECT c.*, COUNT(p.id) as post_count
         FROM post_categories c
         LEFT JOIN posts p ON c.id = p.category_id
         GROUP BY c.id
         ORDER BY c.sort_order ASC, c.name ASC"
    );

    $headerButtons = '<a href="' . adminUrl('blog-categories.php?action=add') . '" class="btn btn-primary">Yeni Kategori Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <div class="row mb-3">
        <div class="col">
            <a href="<?= adminUrl('blog.php') ?>" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-2"></i>Blog Yazılarına Dön
            </a>
        </div>
    </div>

    <!-- Kategoriler Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>Kategori Adı</th>
                        <th>Slug</th>
                        <th>Yazı Sayısı</th>
                        <th>Durum</th>
                        <th>Sıra</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Kategori bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= e($cat['name']) ?></div>
                                    <?php if ($cat['description']): ?>
                                        <div class="text-muted small"><?= e(truncate($cat['description'], 80)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?= e($cat['slug']) ?></code>
                                </td>
                                <td>
                                    <?php if ($cat['post_count'] > 0): ?>
                                        <a href="<?= adminUrl('blog.php?category=' . $cat['id']) ?>" class="badge bg-primary">
                                            <?= $cat['post_count'] ?> yazı
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">0 yazı</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cat['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?= $cat['sort_order'] ?></span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="<?= adminUrl('blog-categories.php?action=edit&id=' . $cat['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $cat['id'] ?>, '<?= e($cat['name']) ?>', <?= $cat['post_count'] ?>)">
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
    </div>

    <!-- Silme formu (gizli) -->
    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
    function deleteCategory(id, name, postCount) {
        if (postCount > 0) {
            alert('Bu kategoriye ait ' + postCount + ' yazı var. Önce yazıları silin veya başka kategoriye taşıyın.');
            return;
        }

        if (confirm('"{name}" kategorisini silmek istediğinize emin misiniz?'.replace('{name}', name))) {
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
    $category = null;

    if ($action === 'edit' && $categoryId > 0) {
        $category = $db->fetchOne("SELECT * FROM post_categories WHERE id = ?", [$categoryId]);
        if (!$category) {
            flash('error', 'Kategori bulunamadı.', 'error');
            redirect(adminUrl('blog-categories.php'));
        }
        $pageTitle = 'Kategori Düzenle';
    } else {
        $pageTitle = 'Yeni Kategori';
        $category = [
            'id' => 0,
            'slug' => '',
            'name' => '',
            'description' => '',
            'sort_order' => 0,
            'is_active' => 1
        ];
    }

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $category['id'] ?>">

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Kategori Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Kategori Adı</label>
                            <input type="text" name="name" id="title" class="form-control" value="<?= e($category['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($category['slug']) ?>" required>
                            <small class="form-hint">URL'de görünecek kısım (örn: kategori-adi)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="4"><?= e($category['description']) ?></textarea>
                            <small class="form-hint">Kategori sayfasında gösterilecek açıklama</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sıralama</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>" min="0">
                                <small class="form-hint">Küçük numara önce görünür</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durum</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                            <a href="<?= adminUrl('blog-categories.php') ?>" class="btn btn-link">İptal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php
    include __DIR__ . '/inc/footer.php';
}
?>
