<?php
// Dosya: /contact.php
// İletişim sayfası ve form handler

require_once __DIR__ . '/inc/config.php';

$pageTitle = 'İletişim';
$success = false;
$error = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $error = 'Güvenlik doğrulaması başarısız.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validasyon
        if (empty($name) || empty($message)) {
            $error = 'Ad ve mesaj alanları zorunludur.';
        } elseif (!empty($email) && !validateEmail($email)) {
            $error = 'Geçerli bir email adresi girin.';
        } else {
            // Veritabanına kaydet
            $db = Database::getInstance();
            $db->insert('contacts', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);

            $success = true;

            // Email gönderimi (opsiyonel)
            if (!empty($settings['email'])) {
                $to = $settings['email'];
                $emailSubject = "Yeni İletişim Mesajı: " . $subject;
                $emailMessage = "Ad: $name\n";
                $emailMessage .= "Email: $email\n";
                $emailMessage .= "Telefon: $phone\n";
                $emailMessage .= "Konu: $subject\n\n";
                $emailMessage .= "Mesaj:\n$message\n";

                $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
                if ($email) {
                    $headers .= "Reply-To: $email\r\n";
                }

                @mail($to, $emailSubject, $emailMessage, $headers);
            }
        }
    }
}

include __DIR__ . '/inc/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">İletişim</h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h4 class="alert-heading">Mesajınız Gönderildi!</h4>
                    <p>Mesajınız için teşekkür ederiz. En kısa sürede size dönüş yapacağız.</p>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- İletişim Formu -->
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="">
                                    <?= csrfField() ?>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Adınız Soyadınız *</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="<?= e($_POST['name'] ?? '') ?>" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?= e($_POST['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Telefon</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                   value="<?= e($_POST['phone'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Konu</label>
                                        <input type="text" class="form-control" id="subject" name="subject"
                                               value="<?= e($_POST['subject'] ?? '') ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="message" class="form-label">Mesajınız *</label>
                                        <textarea class="form-control" id="message" name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Gönder</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- İletişim Bilgileri -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">İletişim Bilgilerimiz</h5>

                                <?php if (!empty($settings['address'])): ?>
                                    <div class="mb-3">
                                        <h6 class="text-muted small mb-1">Adres</h6>
                                        <p><?= nl2br(e($settings['address'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($settings['phone'])): ?>
                                    <div class="mb-3">
                                        <h6 class="text-muted small mb-1">Telefon</h6>
                                        <p>
                                            <a href="tel:<?= e($settings['phone']) ?>"><?= e($settings['phone']) ?></a>
                                            <?php if (!empty($settings['phone2'])): ?>
                                                <br><a href="tel:<?= e($settings['phone2']) ?>"><?= e($settings['phone2']) ?></a>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($settings['email'])): ?>
                                    <div class="mb-3">
                                        <h6 class="text-muted small mb-1">Email</h6>
                                        <p><a href="mailto:<?= e($settings['email']) ?>"><?= e($settings['email']) ?></a></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($settings['whatsapp'])): ?>
                                    <div class="mb-3">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp']) ?>"
                                           class="btn btn-success w-100" target="_blank">
                                            <i class="bi bi-whatsapp me-2"></i> WhatsApp ile İletişim
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Harita -->
                        <?php if (!empty($settings['maps_embed'])): ?>
                            <div class="card mt-3">
                                <div class="card-body p-0">
                                    <div class="ratio ratio-16x9">
                                        <?= $settings['maps_embed'] ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
