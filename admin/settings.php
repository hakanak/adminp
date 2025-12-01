<?php
// Dosya: /admin/settings.php
// Site ayarları yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Site Ayarları';
$db = Database::getInstance();

// Mevcut ayarları getir
$settings = $db->fetchOne("SELECT * FROM settings WHERE id = 1");

if (!$settings) {
    // Ayarlar yoksa oluştur
    $db->insert('settings', ['id' => 1, 'site_title' => 'Site Başlığı']);
    $settings = $db->fetchOne("SELECT * FROM settings WHERE id = 1");
}

// POST isteği
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $data = [
        'site_title' => trim($_POST['site_title'] ?? ''),
        'site_slogan' => trim($_POST['site_slogan'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'phone2' => trim($_POST['phone2'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'maps_embed' => trim($_POST['maps_embed'] ?? ''),
        'facebook' => trim($_POST['facebook'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? ''),
        'twitter' => trim($_POST['twitter'] ?? ''),
        'youtube' => trim($_POST['youtube'] ?? ''),
        'linkedin' => trim($_POST['linkedin'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'google_analytics' => trim($_POST['google_analytics'] ?? ''),
        'google_search_console' => trim($_POST['google_search_console'] ?? ''),
        'footer_text' => trim($_POST['footer_text'] ?? ''),
        'custom_head_code' => trim($_POST['custom_head_code'] ?? ''),
        'custom_body_code' => trim($_POST['custom_body_code'] ?? '')
    ];

    // Email validasyonu
    if (!empty($data['email']) && !validateEmail($data['email'])) {
        flash('error', 'Geçerli bir email adresi girin.', 'error');
    } else {
        // Logo yükleme
        if (!empty($_FILES['logo']['name'])) {
            $upload = uploadImage($_FILES['logo']);
            if ($upload) {
                // Eski logoyu sil
                if ($settings['logo']) {
                    deleteImage($settings['logo']);
                }
                $data['logo'] = $upload['original'];
            }
        }

        // Favicon yükleme
        if (!empty($_FILES['favicon']['name'])) {
            $upload = uploadImage($_FILES['favicon']);
            if ($upload) {
                // Eski favicon'u sil
                if ($settings['favicon']) {
                    deleteImage($settings['favicon']);
                }
                $data['favicon'] = $upload['original'];
            }
        }

        // Default OG Image yükleme
        if (!empty($_FILES['default_og_image']['name'])) {
            $upload = uploadImage($_FILES['default_og_image']);
            if ($upload) {
                // Eski resmi sil
                if ($settings['default_og_image']) {
                    deleteImage($settings['default_og_image']);
                }
                $data['default_og_image'] = $upload['original'];
            }
        }

        // Ayarları güncelle
        $db->update('settings', $data, 'id = 1');

        flash('success', 'Site ayarları başarıyla güncellendi.', 'success');
        redirect(adminUrl('settings.php'));
    }
}

include __DIR__ . '/inc/header.php';
?>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-lg-8">
            <!-- Genel Bilgiler -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Genel Bilgiler</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Site Başlığı</label>
                            <input type="text" name="site_title" class="form-control"
                                   value="<?= e($settings['site_title']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Sloganı</label>
                            <input type="text" name="site_slogan" class="form-control"
                                   value="<?= e($settings['site_slogan'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo ve Favicon -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Logo ve Favicon</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo</label>
                            <?php if (!empty($settings['logo'])): ?>
                                <div class="mb-2">
                                    <img src="<?= siteUrl('uploads/' . $settings['logo']) ?>"
                                         class="img-thumbnail" style="max-height: 100px;" alt="Logo">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="form-hint">PNG veya JPG, maks 5MB</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Favicon</label>
                            <?php if (!empty($settings['favicon'])): ?>
                                <div class="mb-2">
                                    <img src="<?= siteUrl('uploads/' . $settings['favicon']) ?>"
                                         class="img-thumbnail" style="max-height: 50px;" alt="Favicon">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="favicon" class="form-control" accept="image/*">
                            <small class="form-hint">32x32px veya 64x64px ICO/PNG</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- İletişim Bilgileri -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">İletişim Bilgileri</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefon 1</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= e($settings['phone'] ?? '') ?>" placeholder="+90 555 123 45 67">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefon 2</label>
                            <input type="tel" name="phone2" class="form-control"
                                   value="<?= e($settings['phone2'] ?? '') ?>" placeholder="+90 555 123 45 68">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= e($settings['email'] ?? '') ?>" placeholder="info@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="address" class="form-control" rows="3"><?= e($settings['address'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp Numarası</label>
                        <input type="tel" name="whatsapp" class="form-control"
                               value="<?= e($settings['whatsapp'] ?? '') ?>" placeholder="+905551234567">
                        <small class="form-hint">Ülke kodu ile birlikte, boşluksuz (örn: +905551234567)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Google Maps Embed Kodu</label>
                        <textarea name="maps_embed" class="form-control" rows="3"
                                  placeholder='<iframe src="..." width="600" height="450"></iframe>'><?= e($settings['maps_embed'] ?? '') ?></textarea>
                        <small class="form-hint">Google Maps'ten "Embed a map" kodunu buraya yapıştırın</small>
                    </div>
                </div>
            </div>

            <!-- Sosyal Medya -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Sosyal Medya</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Facebook</label>
                        <input type="url" name="facebook" class="form-control"
                               value="<?= e($settings['facebook'] ?? '') ?>" placeholder="https://facebook.com/username">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instagram</label>
                        <input type="url" name="instagram" class="form-control"
                               value="<?= e($settings['instagram'] ?? '') ?>" placeholder="https://instagram.com/username">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Twitter / X</label>
                        <input type="url" name="twitter" class="form-control"
                               value="<?= e($settings['twitter'] ?? '') ?>" placeholder="https://twitter.com/username">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">YouTube</label>
                        <input type="url" name="youtube" class="form-control"
                               value="<?= e($settings['youtube'] ?? '') ?>" placeholder="https://youtube.com/c/channel">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control"
                               value="<?= e($settings['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/name">
                    </div>
                </div>
            </div>

            <!-- SEO ve Analytics -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">SEO ve Analytics</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Varsayılan OG Image</label>
                        <?php if (!empty($settings['default_og_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= siteUrl('uploads/' . $settings['default_og_image']) ?>"
                                     class="img-thumbnail" style="max-height: 150px;" alt="OG Image">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="default_og_image" class="form-control" accept="image/*">
                        <small class="form-hint">Sosyal medyada paylaşılacak varsayılan resim (1200x630px önerilir)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Google Analytics Measurement ID</label>
                        <input type="text" name="google_analytics" class="form-control"
                               value="<?= e($settings['google_analytics'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                        <small class="form-hint">Google Analytics 4 Measurement ID</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Google Search Console Doğrulama Kodu</label>
                        <input type="text" name="google_search_console" class="form-control"
                               value="<?= e($settings['google_search_console'] ?? '') ?>" placeholder="google1234567890abcdef.html">
                        <small class="form-hint">Meta tag veya HTML dosya adı</small>
                    </div>
                </div>
            </div>

            <!-- Özel Kodlar -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Özel Kodlar</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Özel Head Kodu</label>
                        <textarea name="custom_head_code" class="form-control font-monospace" rows="5"
                                  placeholder="<script>...</script>"><?= e($settings['custom_head_code'] ?? '') ?></textarea>
                        <small class="form-hint">&lt;head&gt; içine eklenecek özel kod (örn: analytics, pixel)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Özel Body Kodu</label>
                        <textarea name="custom_body_code" class="form-control font-monospace" rows="5"
                                  placeholder="<script>...</script>"><?= e($settings['custom_body_code'] ?? '') ?></textarea>
                        <small class="form-hint">&lt;body&gt; açılışından hemen sonra eklenecek kod</small>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Footer</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Footer Metni</label>
                        <textarea name="footer_text" class="form-control" rows="2"
                                  placeholder="© 2024 Tüm Hakları Saklıdır."><?= e($settings['footer_text'] ?? '') ?></textarea>
                        <small class="form-hint">Footer'da görünecek telif hakkı metni</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon - Kaydet ve Önizleme -->
        <div class="col-lg-4">
            <!-- Kaydet -->
            <div class="card mb-3 sticky-top" style="top: 1rem;">
                <div class="card-header">
                    <h3 class="card-title">İşlemler</h3>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="ti ti-check me-2"></i>
                        Ayarları Kaydet
                    </button>
                    <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline-secondary w-100">
                        <i class="ti ti-external-link me-2"></i>
                        Siteyi Önizle
                    </a>
                </div>
            </div>

            <!-- Bilgi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bilgi</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Son Güncelleme</div>
                            <div class="datagrid-content">
                                <?= isset($settings['updated_at']) ? formatDate($settings['updated_at']) : 'Henüz güncellenmedi' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Site URL</div>
                            <div class="datagrid-content">
                                <code class="small"><?= SITE_URL ?></code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/inc/footer.php'; ?>
