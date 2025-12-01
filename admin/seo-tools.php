<?php
// Dosya: /admin/seo-tools.php
// SEO Araçları - Sitemap ve robots.txt yönetimi

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'SEO Araçları';

// Mevcut dosyaları kontrol et
$sitemapExists = file_exists(ROOT_PATH . '/sitemap.xml');
$robotsExists = file_exists(ROOT_PATH . '/robots.txt');
$sitemapUrl = SITE_URL . '/sitemap.xml';
$robotsUrl = SITE_URL . '/robots.txt';

// Mevcut robots.txt içeriğini oku
$robotsContent = '';
if ($robotsExists) {
    $robotsContent = file_get_contents(ROOT_PATH . '/robots.txt');
}

include __DIR__ . '/inc/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Sitemap Generator -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Sitemap Oluşturucu</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Sitemap, arama motorlarının sitenizi daha iyi taramasına yardımcı olur.
                    Tüm aktif sayfalar, ürünler ve blog yazıları otomatik olarak sitemap'e eklenir.
                </p>

                <?php if ($sitemapExists): ?>
                    <div class="alert alert-success mb-3">
                        <strong>Sitemap mevcut:</strong>
                        <a href="<?= $sitemapUrl ?>" target="_blank"><?= $sitemapUrl ?></a>
                        <div class="small mt-1">
                            Son güncelleme: <?= date('d.m.Y H:i', filemtime(ROOT_PATH . '/sitemap.xml')) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">
                        Henüz sitemap oluşturulmamış.
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-primary" onclick="generateSitemap()">
                    <i class="ti ti-refresh me-2"></i>
                    <?= $sitemapExists ? 'Sitemap\'i Yenile' : 'Sitemap Oluştur' ?>
                </button>

                <div id="sitemapResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Robots.txt Generator -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">robots.txt Oluşturucu</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    robots.txt dosyası, arama motorlarına hangi sayfaların taranacağını söyler.
                </p>

                <?php if ($robotsExists): ?>
                    <div class="alert alert-success mb-3">
                        <strong>robots.txt mevcut:</strong>
                        <a href="<?= $robotsUrl ?>" target="_blank"><?= $robotsUrl ?></a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">
                        Henüz robots.txt oluşturulmamış.
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Özel Kurallar (Opsiyonel)</label>
                    <textarea id="customRules" class="form-control" rows="6" placeholder="Disallow: /private/&#10;Disallow: /temp/"></textarea>
                    <small class="form-hint">
                        Varsayılan kurallar: /admin/, /inc/, /uploads/thumbnails/ otomatik olarak engellenir.
                    </small>
                </div>

                <button type="button" class="btn btn-primary" onclick="generateRobots()">
                    <i class="ti ti-refresh me-2"></i>
                    <?= $robotsExists ? 'robots.txt\'yi Yenile' : 'robots.txt Oluştur' ?>
                </button>

                <div id="robotsResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Her İkisini Birden Oluştur -->
        <div class="card">
            <div class="card-body">
                <h4>Hepsini Birden Oluştur</h4>
                <p class="text-muted">Sitemap ve robots.txt dosyalarını aynı anda oluşturur.</p>
                <button type="button" class="btn btn-success" onclick="generateBoth()">
                    <i class="ti ti-check me-2"></i>
                    Her İkisini de Oluştur
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Google Search Console -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Google Search Console</h3>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Sitemap'inizi Google Search Console'a ekleyin:
                </p>
                <ol class="small">
                    <li>
                        <a href="https://search.google.com/search-console" target="_blank">
                            Google Search Console
                        </a>'a gidin
                    </li>
                    <li>Sitenizi ekleyin</li>
                    <li>Sitemaps bölümüne gidin</li>
                    <li>Sitemap URL'nizi ekleyin:<br>
                        <code class="small"><?= $sitemapUrl ?></code>
                    </li>
                </ol>
            </div>
        </div>

        <!-- SEO Kontrol Araçları -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Harici SEO Araçları</h3>
            </div>
            <div class="list-group list-group-flush">
                <a href="https://search.google.com/test/mobile-friendly?url=<?= urlencode(SITE_URL) ?>" target="_blank" class="list-group-item list-group-item-action">
                    <i class="ti ti-device-mobile me-2"></i>
                    Mobil Uyumluluk Testi
                </a>
                <a href="https://pagespeed.web.dev/report?url=<?= urlencode(SITE_URL) ?>" target="_blank" class="list-group-item list-group-item-action">
                    <i class="ti ti-speedboat me-2"></i>
                    PageSpeed Insights
                </a>
                <a href="https://validator.w3.org/nu/?doc=<?= urlencode(SITE_URL) ?>" target="_blank" class="list-group-item list-group-item-action">
                    <i class="ti ti-code me-2"></i>
                    HTML Validator
                </a>
                <a href="https://www.xml-sitemaps.com/" target="_blank" class="list-group-item list-group-item-action">
                    <i class="ti ti-sitemap me-2"></i>
                    Sitemap Validator
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function showResult(elementId, success, message) {
    const el = document.getElementById(elementId);
    el.innerHTML = `<div class="alert alert-${success ? 'success' : 'danger'} alert-dismissible">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

function generateSitemap() {
    const formData = new FormData();
    formData.append('action', 'sitemap');

    fetch('ajax/generate-sitemap.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult('sitemapResult', true, data.message + '<br><a href="' + data.url + '" target="_blank">Sitemap\'i görüntüle</a>');
            setTimeout(() => location.reload(), 2000);
        } else {
            showResult('sitemapResult', false, data.message);
        }
    })
    .catch(error => {
        showResult('sitemapResult', false, 'Bir hata oluştu: ' + error);
    });
}

function generateRobots() {
    const customRules = document.getElementById('customRules').value;
    const formData = new FormData();
    formData.append('action', 'robots');
    formData.append('custom_rules', customRules);

    fetch('ajax/generate-sitemap.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult('robotsResult', true, data.message + '<br><a href="' + data.url + '" target="_blank">robots.txt\'yi görüntüle</a>');
            setTimeout(() => location.reload(), 2000);
        } else {
            showResult('robotsResult', false, data.message);
        }
    })
    .catch(error => {
        showResult('robotsResult', false, 'Bir hata oluştu: ' + error);
    });
}

function generateBoth() {
    const customRules = document.getElementById('customRules').value;
    const formData = new FormData();
    formData.append('action', 'both');
    formData.append('custom_rules', customRules);

    fetch('ajax/generate-sitemap.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult('sitemapResult', true, 'Her iki dosya da başarıyla oluşturuldu!');
            setTimeout(() => location.reload(), 2000);
        } else {
            showResult('sitemapResult', false, data.message);
        }
    })
    .catch(error => {
        showResult('sitemapResult', false, 'Bir hata oluştu: ' + error);
    });
}
</script>

<?php include __DIR__ . '/inc/footer.php'; ?>
