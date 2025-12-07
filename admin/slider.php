<?php
// Dosya: /admin/slider.php
// Slider yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Slider Yönetimi';
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$sliderId = (int)($_GET['id'] ?? 0);

// CRUD İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'save') {
        // Yeni kayıt veya güncelleme
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'subtitle' => trim($_POST['subtitle'] ?? ''),
            'button_text' => trim($_POST['button_text'] ?? ''),
            'button_url' => trim($_POST['button_url'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Resim yükleme
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image'], 'slider', 1920);
            if ($upload) {
                // Eski resmi sil
                if ($id > 0) {
                    $oldSlider = $db->fetchOne("SELECT image FROM sliders WHERE id = ?", [$id]);
                    if ($oldSlider && $oldSlider['image']) {
                        deleteImage($oldSlider['image']);
                    }
                }
                $data['image'] = $upload['original'];
            }
        }

        if ($id > 0) {
            // Güncelle
            $db->update('sliders', $data, 'id = ?', [$id]);
            flash('success', 'Slider başarıyla güncellendi.', 'success');
        } else {
            // Resim zorunlu
            if (empty($data['image'])) {
                flash('error', 'Slider resmi zorunludur.', 'error');
                redirect(adminUrl('slider.php?action=add'));
            }
            // Yeni kayıt
            $id = $db->insert('sliders', $data);
            flash('success', 'Slider başarıyla eklendi.', 'success');
        }

        redirect(adminUrl('slider.php'));
    } elseif ($formAction === 'delete') {
        // Silme
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Resmi sil
            $slider = $db->fetchOne("SELECT image FROM sliders WHERE id = ?", [$id]);
            if ($slider && $slider['image']) {
                deleteImage($slider['image']);
            }

            $db->delete('sliders', 'id = ?', [$id]);
            flash('success', 'Slider silindi.', 'success');
        }
        redirect(adminUrl('slider.php'));
    } elseif ($formAction === 'reorder') {
        // Sıralama güncelleme (AJAX)
        $order = $_POST['order'] ?? [];
        foreach ($order as $index => $id) {
            $db->update('sliders', ['sort_order' => $index], 'id = ?', [(int)$id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

// Liste görünümü
if ($action === 'list') {
    // Tüm sliderları getir
    $sliders = $db->fetchAll(
        "SELECT * FROM sliders ORDER BY sort_order ASC, id DESC"
    );

    $headerButtons = '<a href="' . adminUrl('slider.php?action=add') . '" class="btn btn-primary">Yeni Slider Ekle</a>';
    include __DIR__ . '/inc/header.php';
    ?>

    <!-- Sliderlar Tablosu -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tüm Sliderlar</h3>
            <div class="card-actions">
                <span class="text-muted">Sıralamak için satırları sürükleyin</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="sliderTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 120px;">Resim</th>
                        <th>Başlık</th>
                        <th>Alt Başlık</th>
                        <th>Buton</th>
                        <th>Durum</th>
                        <th>Sıra</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody id="sortable">
                    <?php if (empty($sliders)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Slider bulunamadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sliders as $slider): ?>
                            <tr data-id="<?= $slider['id'] ?>">
                                <td class="handle" style="cursor: move;">
                                    <i class="ti ti-grip-vertical text-muted"></i>
                                </td>
                                <td>
                                    <img src="<?= siteUrl('uploads/thumbnails/' . basename($slider['image'])) ?>"
                                         class="rounded" style="width: 100px; height: 60px; object-fit: cover;" alt="">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($slider['title']) ?: '-' ?></div>
                                </td>
                                <td>
                                    <div class="text-muted small"><?= e(truncate($slider['subtitle'], 50)) ?: '-' ?></div>
                                </td>
                                <td>
                                    <?php if ($slider['button_text'] && $slider['button_url']): ?>
                                        <a href="<?= e($slider['button_url']) ?>" target="_blank" class="text-decoration-none">
                                            <?= e($slider['button_text']) ?>
                                            <i class="ti ti-external-link ms-1"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($slider['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?= $slider['sort_order'] ?></span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="<?= adminUrl('slider.php?action=edit&id=' . $slider['id']) ?>" class="btn btn-sm btn-primary">
                                            Düzenle
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSlider(<?= $slider['id'] ?>, '<?= e($slider['title']) ?>')">
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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    function deleteSlider(id, title) {
        if (confirm('"{title}" sliderını silmek istediğinize emin misiniz?'.replace('{title}', title))) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Sürükle-bırak sıralama
    <?php if (!empty($sliders)): ?>
    const sortable = new Sortable(document.getElementById('sortable'), {
        animation: 150,
        handle: '.handle',
        onEnd: function(evt) {
            const order = [];
            document.querySelectorAll('#sortable tr[data-id]').forEach(function(row) {
                order.push(row.getAttribute('data-id'));
            });

            // AJAX ile sıralamayı kaydet
            fetch('<?= adminUrl('slider.php') ?>', {
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
                    document.querySelectorAll('#sortable tr[data-id]').forEach(function(row, index) {
                        row.querySelector('td:nth-child(7) span').textContent = index;
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
    $slider = null;

    if ($action === 'edit' && $sliderId > 0) {
        $slider = $db->fetchOne("SELECT * FROM sliders WHERE id = ?", [$sliderId]);
        if (!$slider) {
            flash('error', 'Slider bulunamadı.', 'error');
            redirect(adminUrl('slider.php'));
        }
        $pageTitle = 'Slider Düzenle';
    } else {
        $pageTitle = 'Yeni Slider';
        $slider = [
            'id' => 0,
            'title' => '',
            'subtitle' => '',
            'image' => '',
            'button_text' => '',
            'button_url' => '',
            'sort_order' => 0,
            'is_active' => 1
        ];
    }

    include __DIR__ . '/inc/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $slider['id'] ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Slider Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" name="title" class="form-control" value="<?= e($slider['title']) ?>" placeholder="Ana başlık metni">
                            <small class="form-hint">Slider üzerinde gösterilecek ana başlık</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alt Başlık</label>
                            <textarea name="subtitle" class="form-control" rows="2" placeholder="Alt başlık metni"><?= e($slider['subtitle']) ?></textarea>
                            <small class="form-hint">Başlık altında gösterilecek açıklama metni</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Buton Metni</label>
                                <input type="text" name="button_text" class="form-control" value="<?= e($slider['button_text']) ?>" placeholder="Detaylı Bilgi">
                                <small class="form-hint">Slider üzerindeki buton yazısı</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Buton URL</label>
                                <input type="url" name="button_url" class="form-control" value="<?= e($slider['button_url']) ?>" placeholder="https://...">
                                <small class="form-hint">Butona tıklandığında gidilecek adres</small>
                            </div>
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
                            <input type="number" name="sort_order" class="form-control" value="<?= $slider['sort_order'] ?>" min="0">
                            <small class="form-hint">Küçük numara önce görünür</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= $slider['is_active'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
                        <a href="<?= adminUrl('slider.php') ?>" class="btn btn-link w-100">İptal</a>
                    </div>
                </div>

                <!-- Slider Resmi -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Slider Resmi <?= $slider['id'] == 0 ? '<span class="text-danger">*</span>' : '' ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($slider['image']): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $slider['image']) ?>" class="img-fluid rounded" alt="">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*" <?= $slider['id'] == 0 ? 'required' : '' ?>>
                        <small class="form-hint">JPG, PNG - Önerilen boyut: 1920x800px - Maks 5MB</small>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php
    include __DIR__ . '/inc/footer.php';
}
?>
