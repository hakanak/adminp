<?php
// Dosya: /admin/product-categories.php
// Ürün kategorileri yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Ürün Kategorileri';
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
            'parent_id' => (int)($_POST['parent_id'] ?? 0) ?: null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Slug benzersizliği kontrolü
        if ($id > 0) {
            $existing = $db->fetchOne("SELECT id FROM product_categories WHERE slug = ? AND id != ?", [$slug, $id]);
        } else {
            $existing = $db->fetchOne("SELECT id FROM product_categories WHERE slug = ?", [$slug]);
        }

        if ($existing) {
            $slug .= '-' . time();
            $data['slug'] = $slug;
        }

        // Resim yükleme
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image']);
            if ($upload) {
                // Eski resmi sil
                if ($id > 0) {
                    $oldCategory = $db->fetchOne("SELECT image FROM product_categories WHERE id = ?", [$id]);
                    if ($oldCategory && $oldCategory['image']) {
                        deleteImage($oldCategory['image']);
                    }
                }
                $data['image'] = $upload['original'];
            }
        }

        if ($id > 0) {
            // Güncelle
            $db->update('product_categories', $data, 'id = ?', [$id]);
            flash('success', 'Kategori başarıyla güncellendi.', 'success');
        } else {
            // Yeni kayıt
            $id = $db->insert('product_categories', $data);
            flash('success', 'Kategori başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('product-categories.php'));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Bu kategoriye ait ürün var mı kontrol et
            $productCount = $db->count('products', 'category_id = ?', [$id]);

            if ($productCount > 0) {
                flash('error', "Bu kategoriye ait {$productCount} ürün var. Önce ürünleri silin veya başka kategoriye taşıyın.", 'error');
            } else {
                // Resmi sil
                $category = $db->fetchOne("SELECT image FROM product_categories WHERE id = ?", [$id]);
                if ($category && $category['image']) {
                    deleteImage($category['image']);
                }

                $db->delete('product_categories', 'id = ?', [$id]);
                flash('success', 'Kategori silindi.', 'success');
            }
        }
        redirect(adminUrl('product-categories.php'));
    }
}

// Liste görünümü
if ($action === 'list') {
    // Tüm kategorileri getir (parent-child ilişkisi ile)
    $categories = $db->fetchAll(
        "SELECT c.*, COUNT(p.id) as product_count,
         parent.name as parent_name
         FROM product_categories c
         LEFT JOIN products p ON c.id = p.category_id
         LEFT JOIN product_categories parent ON c.parent_id = parent.id
         GROUP BY c.id
         ORDER BY c.sort_order ASC, c.name ASC"
    );

    $headerButtons = '<a href="' . adminUrl('product-categories.php?action=add') . '" class="btn btn-primary">Yeni Kategori Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <div class="row mb-3">
        <div class="col">
            <a href="<?= adminUrl('products.php') ?>" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-2"></i>Ürünlere Dön
            </a>
        </div>
    </div>

    <!-- Kategoriler Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Resim</th>
                        <th>Kategori Adı</th>
                        <th>Slug</th>
                        <th>Üst Kategori</th>
                        <th>Ürün Sayısı</th>
                        <th>Durum</th>
                        <th>Sıra</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Kategori bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <?php if ($cat['image']): ?>
                                        <img src="<?= siteUrl('uploads/thumbnails/' . basename($cat['image'])) ?>"
                                             class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 60px; height: 60px;">
                                            <i class="ti ti-category text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($cat['name']) ?></div>
                                    <?php if ($cat['description']): ?>
                                        <div class="text-muted small"><?= e(truncate($cat['description'], 60)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?= e($cat['slug']) ?></code>
                                </td>
                                <td>
                                    <?= e($cat['parent_name'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if ($cat['product_count'] > 0): ?>
                                        <a href="<?= adminUrl('products.php?category=' . $cat['id']) ?>" class="badge bg-primary">
                                            <?= $cat['product_count'] ?> ürün
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">0 ürün</span>
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
                                        <a href="<?= adminUrl('product-categories.php?action=edit&id=' . $cat['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $cat['id'] ?>, '<?= e($cat['name']) ?>', <?= $cat['product_count'] ?>)">
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
    function deleteCategory(id, name, productCount) {
        if (productCount > 0) {
            alert('Bu kategoriye ait ' + productCount + ' ürün var. Önce ürünleri silin veya başka kategoriye taşıyın.');
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
        $category = $db->fetchOne("SELECT * FROM product_categories WHERE id = ?", [$categoryId]);
        if (!$category) {
            flash('error', 'Kategori bulunamadı.', 'error');
            redirect(adminUrl('product-categories.php'));
        }
        $pageTitle = 'Kategori Düzenle';
    } else {
        $pageTitle = 'Yeni Kategori';
        $category = [
            'id' => 0,
            'slug' => '',
            'name' => '',
            'description' => '',
            'image' => '',
            'parent_id' => null,
            'sort_order' => 0,
            'is_active' => 1
        ];
    }

    // Üst kategorileri getir (kendisi hariç)
    $parentCategories = $db->fetchAll(
        "SELECT * FROM product_categories WHERE id != ? ORDER BY name ASC",
        [$category['id']]
    );

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $category['id'] ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
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
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Üst Kategori</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Üst Kategori Yok</option>
                                <?php foreach ($parentCategories as $parent): ?>
                                    <option value="<?= $parent['id'] ?>" <?= $category['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                                        <?= e($parent['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-hint">Alt kategori olarak eklemek için üst kategori seçin</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Yayınlama -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Ayarlar</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Sıralama</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>" min="0">
                            <small class="form-hint">Küçük numara önce görünür</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
                        <a href="<?= adminUrl('product-categories.php') ?>" class="btn btn-link w-100">İptal</a>
                    </div>
                </div>

                <!-- Kategori Resmi -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Kategori Resmi</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($category['image']): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $category['image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="form-hint">JPG, PNG, GIF - Maks 5MB</small>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php
    include __DIR__ . '/inc/footer.php';
}
?>
