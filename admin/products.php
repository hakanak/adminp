<?php
// Dosya: /admin/products.php
// Ürünler yönetimi - Tam CRUD + SEO

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Ürünler';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$productId = (int)($_GET['id'] ?? 0);

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
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => $_POST['description'] ?? '',
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : null,
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
            'seo_canonical' => trim($_POST['seo_canonical'] ?? ''),
            'seo_robots' => $_POST['seo_robots'] ?? 'index,follow',
            'og_title' => trim($_POST['og_title'] ?? ''),
            'og_description' => trim($_POST['og_description'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0)
        ];

        // Slug benzersizliği kontrolü
        if ($id > 0) {
            $existing = $db->fetchOne("SELECT id FROM products WHERE slug = ? AND id != ?", [$slug, $id]);
        } else {
            $existing = $db->fetchOne("SELECT id FROM products WHERE slug = ?", [$slug]);
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
                    $oldProduct = $db->fetchOne("SELECT featured_image FROM products WHERE id = ?", [$id]);
                    if ($oldProduct && $oldProduct['featured_image']) {
                        deleteImage($oldProduct['featured_image']);
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
                    $oldProduct = $db->fetchOne("SELECT og_image FROM products WHERE id = ?", [$id]);
                    if ($oldProduct && $oldProduct['og_image']) {
                        deleteImage($oldProduct['og_image']);
                    }
                }
                $data['og_image'] = $upload['original'];
            }
        }

        // Galeri resimleri (basit versiyon - çoklu yükleme)
        if (!empty($_FILES['gallery']['name'][0])) {
            $gallery = [];
            $oldGallery = [];

            if ($id > 0) {
                $oldProduct = $db->fetchOne("SELECT gallery FROM products WHERE id = ?", [$id]);
                if ($oldProduct && $oldProduct['gallery']) {
                    $oldGallery = json_decode($oldProduct['gallery'], true) ?? [];
                }
            }

            foreach ($_FILES['gallery']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file = [
                        'name' => $_FILES['gallery']['name'][$key],
                        'type' => $_FILES['gallery']['type'][$key],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                        'error' => $_FILES['gallery']['error'][$key],
                        'size' => $_FILES['gallery']['size'][$key]
                    ];

                    $upload = uploadImage($file);
                    if ($upload) {
                        $gallery[] = $upload['original'];
                    }
                }
            }

            // Eski resimleri koru ve yenileri ekle
            $data['gallery'] = json_encode(array_merge($oldGallery, $gallery));
        }

        if ($id > 0) {
            // Güncelle
            $db->update('products', $data, 'id = ?', [$id]);
            flash('success', 'Ürün başarıyla güncellendi.', 'success');
        } else {
            // Yeni kayıt
            $id = $db->insert('products', $data);
            flash('success', 'Ürün başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('products.php?action=edit&id=' . $id));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Resimleri sil
            $product = $db->fetchOne("SELECT featured_image, og_image, gallery FROM products WHERE id = ?", [$id]);
            if ($product) {
                if ($product['featured_image']) deleteImage($product['featured_image']);
                if ($product['og_image']) deleteImage($product['og_image']);

                if ($product['gallery']) {
                    $gallery = json_decode($product['gallery'], true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $img) {
                            deleteImage($img);
                        }
                    }
                }
            }

            $db->delete('products', 'id = ?', [$id]);
            flash('success', 'Ürün silindi.', 'success');
        }
        redirect(adminUrl('products.php'));
    } elseif ($formAction === 'delete_gallery_image') {
        // Galeri resmini sil
        $id = (int)($_POST['id'] ?? 0);
        $imageIndex = (int)($_POST['image_index'] ?? 0);

        if ($id > 0) {
            $product = $db->fetchOne("SELECT gallery FROM products WHERE id = ?", [$id]);
            if ($product && $product['gallery']) {
                $gallery = json_decode($product['gallery'], true);
                if (isset($gallery[$imageIndex])) {
                    deleteImage($gallery[$imageIndex]);
                    unset($gallery[$imageIndex]);
                    $gallery = array_values($gallery); // Re-index
                    $db->update('products', ['gallery' => json_encode($gallery)], 'id = ?', [$id]);
                }
            }
        }
        redirect(adminUrl('products.php?action=edit&id=' . $id));
    }
}

// Liste görünümü
if ($action === 'list') {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;

    $search = $_GET['search'] ?? '';
    $categoryFilter = (int)($_GET['category'] ?? 0);

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

    $totalProducts = $db->count('products p', $where, $params);
    $totalPages = ceil($totalProducts / $perPage);

    $products = $db->fetchAll(
        "SELECT p.*, c.name as category_name
         FROM products p
         LEFT JOIN product_categories c ON p.category_id = c.id
         WHERE $where
         ORDER BY p.sort_order ASC, p.created_at DESC
         LIMIT $perPage OFFSET $offset",
        $params
    );

    // Kategorileri getir (filtre için)
    $categories = $db->fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name ASC");

    $headerButtons = '<a href="' . adminUrl('products.php?action=add') . '" class="btn btn-primary">Yeni Ürün Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Filtreler -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Ürün ara..." value="<?= e($search) ?>">
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
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrele</button>
                    <?php if ($search || $categoryFilter): ?>
                        <a href="<?= adminUrl('products.php') ?>" class="btn btn-link">Temizle</a>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <a href="<?= adminUrl('product-categories.php') ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-category me-2"></i>Kategoriler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ürünler Tablosu -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Resim</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>Sıra</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Ürün bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['featured_image']): ?>
                                        <img src="<?= siteUrl('uploads/thumbnails/' . basename($p['featured_image'])) ?>"
                                             class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 60px; height: 60px;">
                                            <i class="ti ti-photo text-muted"></i>
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
                                    <?php if ($p['price']): ?>
                                        <span class="fw-bold"><?= number_format($p['price'], 2) ?> TL</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?= $p['sort_order'] ?></span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="<?= adminUrl('products.php?action=edit&id=' . $p['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <a href="<?= siteUrl('urun/' . $p['slug']) ?>" class="btn btn-sm btn-ghost-secondary" target="_blank">
                                            Görüntüle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>, '<?= e($p['title']) ?>')">
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
                <?= pagination($page, $totalPages, adminUrl('products.php' . ($search ? '?search=' . urlencode($search) : '') . ($categoryFilter ? ($search ? '&' : '?') . 'category=' . $categoryFilter : ''))) ?>
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
    function deleteProduct(id, title) {
        if (confirm('"{title}" ürününü silmek istediğinize emin misiniz?'.replace('{title}', title))) {
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
    $product = null;

    if ($action === 'edit' && $productId > 0) {
        $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$productId]);
        if (!$product) {
            flash('error', 'Ürün bulunamadı.', 'error');
            redirect(adminUrl('products.php'));
        }
        $pageTitle = 'Ürün Düzenle';
    } else {
        $pageTitle = 'Yeni Ürün';
        $product = [
            'id' => 0,
            'category_id' => null,
            'slug' => '',
            'title' => '',
            'short_description' => '',
            'description' => '',
            'featured_image' => '',
            'gallery' => null,
            'price' => null,
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'seo_canonical' => '',
            'seo_robots' => 'index,follow',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'is_featured' => 0,
            'is_active' => 1,
            'sort_order' => 0
        ];
    }

    // Kategorileri getir
    $categories = $db->fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name ASC");

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">

        <div class="row">
            <div class="col-lg-8">
                <!-- Ana İçerik -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Ürün Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Ürün Adı</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= e($product['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($product['slug']) ?>" required>
                            <small class="form-hint">URL'de görünecek kısım (örn: urun-adi)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kısa Açıklama</label>
                            <textarea name="short_description" class="form-control" rows="3" maxlength="500"><?= e($product['short_description']) ?></textarea>
                            <small class="form-hint">Listelerde görünecek özet (maks 500 karakter)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detaylı Açıklama</label>
                            <textarea name="description" id="content" class="form-control" rows="15"><?= e($product['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Galeri -->
                <?php if ($product['gallery']):
                    $gallery = json_decode($product['gallery'], true);
                    if (!empty($gallery)):
                ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Mevcut Galeri Resimleri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($gallery as $index => $img): ?>
                                <div class="col-md-3">
                                    <div class="position-relative">
                                        <img src="<?= siteUrl('uploads/thumbnails/' . basename($img)) ?>"
                                             class="img-fluid rounded" alt="">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                onclick="deleteGalleryImage(<?= $product['id'] ?>, <?= $index ?>)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; endif; ?>

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
                                    <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fiyat (TL)</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0"
                                   value="<?= $product['price'] ?>">
                            <small class="form-hint">Fiyat gösterilmesini istemiyorsanız boş bırakın</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sıralama</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= $product['sort_order'] ?>" min="0">
                            <small class="form-hint">Küçük numara önce görünür</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
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
                        <?php if ($product['featured_image']): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $product['featured_image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                        <small class="form-hint">JPG, PNG, GIF - Maks 5MB</small>
                    </div>
                </div>

                <!-- Galeri Resimleri -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Galeri Resimleri Ekle</h3>
                    </div>
                    <div class="card-body">
                        <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                        <small class="form-hint">Birden fazla resim seçebilirsiniz</small>
                    </div>
                </div>

                <!-- Bilgiler -->
                <?php if ($product['id'] > 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Oluşturma</div>
                                    <div class="datagrid-content"><?= formatDate($product['created_at'] ?? '') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Güncelleme</div>
                                    <div class="datagrid-content"><?= formatDate($product['updated_at'] ?? '') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Görüntülenme</div>
                                    <div class="datagrid-content"><?= number_format($product['view_count'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Galeri silme formu -->
    <form id="deleteGalleryForm" method="POST" style="display:none;">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="delete_gallery_image">
        <input type="hidden" name="id" id="galleryProductId">
        <input type="hidden" name="image_index" id="galleryImageIndex">
    </form>

    <script>
    function deleteGalleryImage(productId, imageIndex) {
        if (confirm('Bu resmi silmek istediğinize emin misiniz?')) {
            document.getElementById('galleryProductId').value = productId;
            document.getElementById('galleryImageIndex').value = imageIndex;
            document.getElementById('deleteGalleryForm').submit();
        }
    }
    </script>

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
