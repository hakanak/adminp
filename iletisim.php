<?php
// Dosya: /iletisim.php
// İletişim sayfası

require_once __DIR__ . '/inc/config.php';

$pageTitle = 'İletişim';
$db = Database::getInstance();

// Form gönderildi mi?
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validasyon
    if (empty($name)) {
        $error = 'İsim alanı zorunludur.';
    } elseif (empty($message)) {
        $error = 'Mesaj alanı zorunludur.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi giriniz.';
    } else {
        // Veritabanına kaydet
        $data = [
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'subject' => $subject ?: null,
            'message' => $message,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'is_read' => 0
        ];

        try {
            $db->insert('contacts', $data);
            $success = true;

            // Form verilerini temizle
            $_POST = [];
        } catch (Exception $e) {
            $error = 'Mesajınız gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

include __DIR__ . '/inc/header.php';
?>

<!-- Sayfa Başlığı -->
<section class="page-header bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">İletişim</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= siteUrl() ?>">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">İletişim</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- İletişim İçeriği -->
<section class="contact py-5">
    <div class="container">
        <div class="row">
            <!-- İletişim Bilgileri -->
            <div class="col-lg-4 mb-4">
                <div class="contact-info">
                    <h3 class="mb-4">İletişim Bilgileri</h3>

                    <?php if (!empty($settings['address'])): ?>
                        <div class="info-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="ti ti-map-pin text-primary" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Adres</h6>
                                    <p class="text-muted mb-0"><?= nl2br(e($settings['address'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['phone'])): ?>
                        <div class="info-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="ti ti-phone text-primary" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Telefon</h6>
                                    <p class="mb-0">
                                        <a href="tel:<?= e($settings['phone']) ?>" class="text-decoration-none text-muted">
                                            <?= e($settings['phone']) ?>
                                        </a>
                                    </p>
                                    <?php if (!empty($settings['phone2'])): ?>
                                        <p class="mb-0">
                                            <a href="tel:<?= e($settings['phone2']) ?>" class="text-decoration-none text-muted">
                                                <?= e($settings['phone2']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['email'])): ?>
                        <div class="info-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="ti ti-mail text-primary" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">E-posta</h6>
                                    <p class="mb-0">
                                        <a href="mailto:<?= e($settings['email']) ?>" class="text-decoration-none text-muted">
                                            <?= e($settings['email']) ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['whatsapp'])): ?>
                        <div class="info-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="ti ti-brand-whatsapp text-success" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">WhatsApp</h6>
                                    <p class="mb-0">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp']) ?>"
                                           target="_blank"
                                           class="text-decoration-none text-muted">
                                            <?= e($settings['whatsapp']) ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Sosyal Medya -->
                    <?php if (!empty($settings['facebook']) || !empty($settings['instagram']) || !empty($settings['twitter']) || !empty($settings['linkedin']) || !empty($settings['youtube'])): ?>
                        <div class="social-media mt-4">
                            <h6 class="fw-bold mb-3">Sosyal Medya</h6>
                            <div class="d-flex gap-2">
                                <?php if (!empty($settings['facebook'])): ?>
                                    <a href="<?= e($settings['facebook']) ?>" target="_blank" class="btn btn-outline-primary btn-icon">
                                        <i class="ti ti-brand-facebook"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($settings['instagram'])): ?>
                                    <a href="<?= e($settings['instagram']) ?>" target="_blank" class="btn btn-outline-danger btn-icon">
                                        <i class="ti ti-brand-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($settings['twitter'])): ?>
                                    <a href="<?= e($settings['twitter']) ?>" target="_blank" class="btn btn-outline-info btn-icon">
                                        <i class="ti ti-brand-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($settings['linkedin'])): ?>
                                    <a href="<?= e($settings['linkedin']) ?>" target="_blank" class="btn btn-outline-primary btn-icon">
                                        <i class="ti ti-brand-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($settings['youtube'])): ?>
                                    <a href="<?= e($settings['youtube']) ?>" target="_blank" class="btn btn-outline-danger btn-icon">
                                        <i class="ti ti-brand-youtube"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- İletişim Formu -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Bize Ulaşın</h3>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ti ti-check me-2"></i>
                                <strong>Teşekkürler!</strong> Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>
                                <?= e($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="contact-form">
                            <?= csrfField() ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Ad Soyad</label>
                                    <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefon</label>
                                    <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konu</label>
                                    <input type="text" name="subject" class="form-control" value="<?= e($_POST['subject'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Mesajınız</label>
                                <textarea name="message" class="form-control" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ti ti-send me-2"></i>Mesaj Gönder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps -->
        <?php if (!empty($settings['maps_embed'])): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="map-container">
                                <?= $settings['maps_embed'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Stil -->
<style>
.info-item {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s;
}

.info-item:hover {
    background-color: #f8f9fa;
}

.btn-icon {
    width: 40px;
    height: 40px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.map-container {
    position: relative;
    overflow: hidden;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
}

.map-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.contact-form .form-label.required::after {
    content: " *";
    color: #d63939;
}
</style>

<?php include __DIR__ . '/inc/footer.php'; ?>
