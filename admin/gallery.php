<?php
// Dosya: /admin/gallery.php
// Galeri yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Galeri Yönetimi';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$galleryId = (int)($_GET['id'] ?? 0);

// CRUD İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'save') {
        // Yeni kayıt veya güncelleme
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Resim yükleme
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image'], 'gallery', 1200);
            if ($upload) {
                // Eski resmi sil
                if ($id > 0) {
                    $oldGallery = $db->fetchOne("SELECT image FROM gallery WHERE id = ?", [$id]);
                    if ($oldGallery && $oldGallery['image']) {
                        deleteImage($oldGallery['image']);
                    }
                }
                $data['image'] = $upload['original'];
            }
        }

        if ($id > 0) {
            // Güncelle
            $db->update('gallery', $data, 'id = ?', [$id]);
            flash('success', 'Galeri öğesi başarıyla güncellendi.', 'success');
        } else {
            // Resim zorunlu
            if (empty($data['image'])) {
                flash('error', 'Galeri resmi zorunludur.', 'error');
                redirect(adminUrl('gallery.php?action=add'));
            }
            // Yeni kayıt
            $id = $db->insert('gallery', $data);
            flash('success', 'Galeri öğesi başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('gallery.php'));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Resmi sil
            $gallery = $db->fetchOne("SELECT image FROM gallery WHERE id = ?", [$id]);
            if ($gallery && $gallery['image']) {
                deleteImage($gallery['image']);
            }

            $db->delete('gallery', 'id = ?', [$id]);
            flash('success', 'Galeri öğesi silindi.', 'success');
        }
        redirect(adminUrl('gallery.php'));
    } elseif ($formAction === 'reorder') {
        // Sıralama güncelleme (AJAX)
        $order = $_POST['order'] ?? [];
        foreach ($order as $index => $id) {
            $db->update('gallery', ['sort_order' => $index], 'id = ?', [(int)$id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

// Liste görünümü
if ($action === 'list') {
    // Tüm galeri öğelerini getir
    $gallery = $db->fetchAll(
        "SELECT * FROM gallery ORDER BY sort_order ASC, id DESC"
    );

    $headerButtons = '<a href="' . adminUrl('gallery.php?action=add') . '" class="btn btn-primary">Yeni Resim Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Galeri Grid -->
    <div class="row row-cards" id="sortable">
        <?php if (empty($gallery)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ti ti-photo-off mb-3" style="font-size: 48px;"></i>
                        <p>Galeri boş. İlk resmi ekleyin.</p>
                        <a href="<?= adminUrl('gallery.php?action=add') ?>" class="btn btn-primary">
                            <i class="ti ti-plus me-2"></i>Resim Ekle
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($gallery as $item): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3" data-id="<?= $item['id'] ?>">
                    <div class="card card-sm">
                        <div class="card-img-top position-relative">
                            <img src="<?= siteUrl('uploads/' . $item['image']) ?>"
                                 class="w-100"
                                 style="height: 200px; object-fit: cover; cursor: move;"
                                 alt="<?= e($item['title']) ?>">
                            <div class="position-absolute top-0 end-0 p-2">
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pasif</span>
                                <?php endif; ?>
                            </div>
                            <div class="position-absolute top-0 start-0 p-2">
                                <span class="badge bg-dark">
                                    <i class="ti ti-grip-vertical"></i> Sıra: <?= $item['sort_order'] ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= e($item['title']) ?></h3>
                            <?php if ($item['description']): ?>
                                <p class="text-muted small mb-2"><?= e(truncate($item['description'], 60)) ?></p>
                            <?php endif; ?>
                            <div class="text-muted small mb-2">
                                <i class="ti ti-calendar me-1"></i>
                                <?= formatDate($item['created_at']) ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list w-100">
                                <a href="<?= adminUrl('gallery.php?action=edit&id=' . $item['id']) ?>"
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="ti ti-pencil me-1"></i>Düzenle
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-danger w-100"
                                        onclick="deleteGallery(<?= $item['id'] ?>, '<?= e($item['title']) ?>')">
                                    <i class="ti ti-trash me-1"></i>Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Silme formu (gizli) -->
    <form id="deleteForm" method="POST" style="display:none;">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    function deleteGallery(id, title) {
        if (confirm('"{title}" resmini silmek istediğinize emin misiniz?'.replace('{title}', title))) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Sürükle-bırak sıralama
    <?php if (!empty($gallery)): ?>
    const sortable = new Sortable(document.getElementById('sortable'), {
        animation: 150,
        onEnd: function(evt) {
            const order = [];
            document.querySelectorAll('#sortable > div[data-id]').forEach(function(card) {
                order.push(card.getAttribute('data-id'));
            });

            // AJAX ile sıralamayı kaydet
            fetch('<?= adminUrl('gallery.php') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'form_action=reorder&<?= csrfField(true) ?>&order=' + JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Sıra numaralarını güncelle
                    document.querySelectorAll('#sortable > div[data-id]').forEach(function(card, index) {
                        card.querySelector('.position-absolute.start-0 .badge').innerHTML = '<i class="ti ti-grip-vertical"></i> Sıra: ' + index;
                    });
                }
            });
        }
    });
    <?php endif; ?>
    </script>

    <?php
    include __DIR__ . '/inc/footer.php';
}
// Ekleme / Düzenleme formu
elseif ($action === 'add' || $action === 'edit') {
    $gallery = null;

    if ($action === 'edit' && $galleryId > 0) {
        $gallery = $db->fetchOne("SELECT * FROM gallery WHERE id = ?", [$galleryId]);
        if (!$gallery) {
            flash('error', 'Galeri öğesi bulunamadı.', 'error');
            redirect(adminUrl('gallery.php'));
        }
        $pageTitle = 'Galeri Düzenle';
    } else {
        $pageTitle = 'Yeni Galeri Resmi';
        $gallery = [
            'id' => 0,
            'title' => '',
            'description' => '',
            'image' => '',
            'sort_order' => 0,
            'is_active' => 1
        ];
    }

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $gallery['id'] ?>">

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Galeri Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Başlık</label>
                            <input type="text" name="title" class="form-control" value="<?= e($gallery['title']) ?>" required placeholder="Resim başlığı">
                            <small class="form-hint">Galeri resminin başlığı</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Resim açıklaması (opsiyonel)"><?= e($gallery['description']) ?></textarea>
                            <small class="form-hint">Resim hakkında kısa açıklama</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sıralama</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= $gallery['sort_order'] ?>" min="0">
                                <small class="form-hint">Küçük numara önce görünür</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durum</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $gallery['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resim -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Galeri Resmi <?= $gallery['id'] == 0 ? '<span class="text-danger">*</span>' : '' ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($gallery['image']): ?>
                            <div class="mb-3">
                                <img src="<?= siteUrl('uploads/' . $gallery['image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*" <?= $gallery['id'] == 0 ? 'required' : '' ?>>
                        <small class="form-hint">JPG, PNG, GIF - Önerilen boyut: 1200x800px - Maks 5MB</small>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                            <a href="<?= adminUrl('gallery.php') ?>" class="btn btn-link">İptal</a>
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
