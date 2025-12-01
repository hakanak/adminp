<?php
// Dosya: /admin/index.php
// Admin giriş sayfası

require_once __DIR__ . '/../inc/config.php';

// Auth fonksiyonlarını yükle (ama requireLogin çağrılmayacak çünkü bu index.php)
require_once __DIR__ . '/inc/auth.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    redirect(adminUrl('dashboard.php'));
}

$settings = getSettings();
$error = '';

// POST isteği gelirse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verifyCsrf()) {
        $error = 'Güvenlik doğrulaması başarısız.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Kullanıcı adı ve şifre gerekli.';
        } else {
            // Rate limiting kontrolü
            if (!checkLoginAttempts($username)) {
                $error = flash('error')['message'] ?? 'Çok fazla başarısız deneme.';
            } else {
                // Kullanıcıyı veritabanından bul
                $db = Database::getInstance();
                $user = $db->fetchOne(
                    "SELECT * FROM users WHERE username = ? AND is_active = 1",
                    [$username]
                );

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Başarılı giriş
                    resetLoginAttempts($username);
                    login($user['id'], $user['username'], $user['email']);
                    redirect(adminUrl('dashboard.php'));
                } else {
                    // Başarısız giriş
                    recordFailedLogin($username);
                    $error = 'Kullanıcı adı veya şifre hatalı.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Giriş Yap | <?= e($settings['site_title']) ?> Admin</title>

    <!-- Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet">

    <?php if (!empty($settings['favicon'])): ?>
    <link rel="icon" href="<?= siteUrl('uploads/' . $settings['favicon']) ?>" type="image/x-icon">
    <?php endif; ?>

    <style>
        .page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>
<body class="d-flex flex-column bg-white">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <?php if (!empty($settings['logo'])): ?>
                    <img src="<?= siteUrl('uploads/' . $settings['logo']) ?>" height="60" alt="<?= e($settings['site_title']) ?>">
                <?php else: ?>
                    <h1 class="h2"><?= e($settings['site_title']) ?></h1>
                <?php endif; ?>
            </div>

            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Admin Paneli Girişi</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z"></path>
                                        <path d="M12 9v4"></path>
                                        <path d="M12 17h.01"></path>
                                    </svg>
                                </div>
                                <div><?= e($error) ?></div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    <?php endif; ?>

                    <?php if ($flash = flash('message')): ?>
                        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible" role="alert">
                            <div><?= e($flash['message']) ?></div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" name="username" class="form-control" placeholder="Kullanıcı adınızı girin"
                                   value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" name="password" class="form-control" placeholder="Şifrenizi girin" required>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted mt-3">
                <small>Varsayılan kullanıcı: <strong>admin</strong> / Şifre: <strong>admin123</strong></small>
            </div>

            <div class="text-center text-muted mt-3">
                <a href="<?= SITE_URL ?>" class="text-muted">Siteye Dön</a>
            </div>
        </div>
    </div>

    <!-- Tabler JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
