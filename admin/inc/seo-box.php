<?php
// Dosya: /admin/inc/seo-box.php
// SEO Ayarları Kutusu (Formlar için include edilebilir)

// $page, $product veya $post değişkeni dışarıdan gelmeli
$item = $page ?? $product ?? $post ?? [];
?>

<!-- SEO Ayarları Accordion -->
<div class="card">
    <div class="card-header" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#seoBox">
        <h3 class="card-title mb-0">
            SEO Ayarları
            <span class="badge bg-secondary float-end" id="seo-score">0</span>
        </h3>
    </div>
    <div id="seoBox" class="collapse show">
        <div class="card-body">
            <!-- SEO Başlık -->
            <div class="mb-3">
                <label class="form-label">SEO Başlık</label>
                <input type="text" name="seo_title" id="seo_title" class="form-control" maxlength="70"
                       value="<?= e($item['seo_title'] ?? '') ?>"
                       placeholder="Boş bırakılırsa sayfa başlığı kullanılır">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Google'da görünecek başlık</small>
                    <small><span id="seo_title_count">0/70</span></small>
                </div>
            </div>

            <!-- Meta Açıklama -->
            <div class="mb-3">
                <label class="form-label">Meta Açıklama</label>
                <textarea name="seo_description" id="seo_description" class="form-control"
                          rows="2" maxlength="160"
                          placeholder="Google arama sonuçlarında görünecek açıklama"><?= e($item['seo_description'] ?? '') ?></textarea>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Arama motoru açıklaması</small>
                    <small><span id="seo_desc_count">0/160</span></small>
                </div>
            </div>

            <!-- Google Önizleme -->
            <div class="card bg-light mb-3">
                <div class="card-body py-2">
                    <small class="text-muted d-block mb-2">Google Önizleme</small>
                    <div id="preview-title" class="text-primary fw-bold" style="font-size: 18px;">Site Başlığı</div>
                    <div id="preview-url" class="text-success small">https://example.com/slug</div>
                    <div id="preview-desc" class="small text-muted">Meta açıklama burada görünecek...</div>
                </div>
            </div>

            <!-- SEO İpuçları -->
            <div id="seo-tips" class="mb-3"></div>

            <!-- Gelişmiş Ayarlar -->
            <div class="accordion" id="advancedSeo">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#advSeoContent">
                            Gelişmiş SEO Ayarları
                        </button>
                    </h2>
                    <div id="advSeoContent" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Canonical URL</label>
                                    <input type="url" name="seo_canonical" class="form-control"
                                           value="<?= e($item['seo_canonical'] ?? '') ?>"
                                           placeholder="Boş bırakılırsa mevcut URL">
                                    <small class="form-hint">Tercih edilen sayfa URL'si</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Robots</label>
                                    <select name="seo_robots" class="form-select">
                                        <option value="index,follow" <?= ($item['seo_robots'] ?? '') === 'index,follow' ? 'selected' : '' ?>>
                                            index, follow (Varsayılan)
                                        </option>
                                        <option value="noindex,follow" <?= ($item['seo_robots'] ?? '') === 'noindex,follow' ? 'selected' : '' ?>>
                                            noindex, follow
                                        </option>
                                        <option value="noindex,nofollow" <?= ($item['seo_robots'] ?? '') === 'noindex,nofollow' ? 'selected' : '' ?>>
                                            noindex, nofollow
                                        </option>
                                    </select>
                                    <small class="form-hint">Arama motoru tarama ayarı</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Anahtar Kelimeler</label>
                                <input type="text" name="seo_keywords" class="form-control"
                                       value="<?= e($item['seo_keywords'] ?? '') ?>"
                                       placeholder="kelime1, kelime2, kelime3">
                                <small class="form-hint">Virgülle ayırın</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sosyal Medya (Open Graph) -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#socialContent">
                            Sosyal Medya (Facebook, Twitter)
                        </button>
                    </h2>
                    <div id="socialContent" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <label class="form-label">OG Başlık</label>
                                <input type="text" name="og_title" class="form-control" maxlength="70"
                                       value="<?= e($item['og_title'] ?? '') ?>"
                                       placeholder="Boş bırakılırsa SEO başlığı kullanılır">
                                <small class="form-hint">Facebook/Twitter paylaşım başlığı</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">OG Açıklama</label>
                                <textarea name="og_description" class="form-control" rows="2" maxlength="200"
                                          placeholder="Boş bırakılırsa meta açıklama kullanılır"><?= e($item['og_description'] ?? '') ?></textarea>
                                <small class="form-hint">Sosyal medya paylaşım açıklaması</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">OG Resim</label>
                                <?php if (!empty($item['og_image'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= siteUrl('uploads/' . $item['og_image']) ?>" class="img-fluid rounded" style="max-height: 150px;" alt="OG Image">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="og_image" class="form-control" accept="image/*">
                                <small class="form-hint">Önerilen: 1200x630px (Facebook, Twitter için)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
