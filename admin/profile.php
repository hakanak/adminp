<?php
// Dosya: /admin/profile.php
// Kullanıcı profili ve şifre değiştirme

require_once __DIR__ . '/inc/auth.php';

$pageTitle = 'Profilim';
$db = Database::getInstance();
$adminUser = getAdminUser();

// POST isteği
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // Profil bilgilerini güncelle
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Geçerli bir email adresi girin.';
        }

        // Email başka kullanıcı tarafından kullanılıyor mu?
        $existingUser = $db->fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, $adminUser['id']]
        );

        if ($existingUser) {
            $errors[] = 'Bu email adresi başka bir kullanıcı tarafından kullanılıyor.';
        }

        if (empty($errors)) {
            $db->update('users', [
                'full_name' => $fullName,
                'email' => $email
            ], 'id = ?', [$adminUser['id']]);

            $_SESSION['admin_email'] = $email;
            flash('success', 'Profil bilgileriniz güncellendi.', 'success');
            redirect(adminUrl('profile.php'));
        } else {
            flash('error', implode('<br>', $errors), 'error');
        }
    } elseif ($action === 'change_password') {
        // Şifre değiştir
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = 'Tüm alanları doldurun.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Yeni şifreler eşleşmiyor.';
        }

        if (strlen($newPassword) < 6) {
            $errors[] = 'Yeni şifre en az 6 karakter olmalı.';
        }

        if (empty($errors)) {
            // Mevcut şifreyi kontrol et
            $user = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$adminUser['id']]);

            if (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = 'Mevcut şifreniz hatalı.';
            } else {
                // Yeni şifreyi kaydet
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $db->update('users', [
                    'password_hash' => $newHash
                ], 'id = ?', [$adminUser['id']]);

                flash('success', 'Şifreniz başarıyla değiştirildi.', 'success');
                redirect(adminUrl('profile.php'));
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'error');
        }
    }
}

include __DIR__ . '/inc/header.php';
?>

<div class="row row-cards">
    <!-- Profil Bilgileri -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profil Bilgileri</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" value="<?= e($adminUser['username']) ?>" disabled>
                        <small class="form-hint">Kullanıcı adı değiştirilemez.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($adminUser['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($adminUser['full_name'] ?? '') ?>">
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Şifre Değiştir -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Şifre Değiştir</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label required">Mevcut Şifre</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Yeni Şifre</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                        <small class="form-hint">En az 6 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Şifreyi Değiştir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Oturum Bilgileri -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Oturum Bilgileri</h3>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Son Giriş</div>
                        <div class="datagrid-content">
                            <?php
                            $user = $db->fetchOne("SELECT last_login FROM users WHERE id = ?", [$adminUser['id']]);
                            echo $user['last_login'] ? formatDate($user['last_login']) : 'Bilgi yok';
                            ?>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">IP Adresi</div>
                        <div class="datagrid-content"><?= e($_SERVER['REMOTE_ADDR']) ?></div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Oturum Süresi</div>
                        <div class="datagrid-content">
                            <?php
                            if (isset($_SESSION['login_time'])) {
                                $elapsed = time() - $_SESSION['login_time'];
                                $hours = floor($elapsed / 3600);
                                $minutes = floor(($elapsed % 3600) / 60);
                                echo "{$hours} saat {$minutes} dakika";
                            } else {
                                echo 'Bilgi yok';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
