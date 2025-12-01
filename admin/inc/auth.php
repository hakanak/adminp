<?php
// Dosya: /admin/inc/auth.php
// Oturum kontrolü ve yetkilendirme

// Config dosyasını yükle
require_once dirname(__DIR__, 2) . '/inc/config.php';

/**
 * Kullanıcı giriş yapmış mı kontrol et
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Giriş yapma zorunluluğu (giriş yapmamışsa login sayfasına yönlendir)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        flash('error', 'Bu sayfaya erişmek için giriş yapmalısınız.', 'error');
        redirect(adminUrl('index.php'));
    }
}

/**
 * Session hijacking koruması
 */
function validateSession() {
    if (!isLoggedIn()) {
        return false;
    }

    // User agent kontrolü
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        destroySession();
        return false;
    }

    // Session timeout kontrolü
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_LIFETIME) {
            destroySession();
            flash('error', 'Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.', 'warning');
            return false;
        }
    }

    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Kullanıcı girişi yap
 * @param int $userId
 * @param string $username
 * @param string $email
 */
function login($userId, $username, $email) {
    // Session regenerate (session fixation koruması)
    session_regenerate_id(true);

    $_SESSION['admin_id'] = $userId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_email'] = $email;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();

    // Son giriş zamanını güncelle
    $db = Database::getInstance();
    $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$userId]);
}

/**
 * Çıkış yap
 */
function logout() {
    destroySession();
    redirect(adminUrl('index.php'));
}

/**
 * Session'ı tamamen yok et
 */
function destroySession() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Giriş yapan admin bilgilerini al
 * @return array|null
 */
function getAdminUser() {
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;

    if ($user === null) {
        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT id, username, email, full_name FROM users WHERE id = ? AND is_active = 1",
            [$_SESSION['admin_id']]
        );
    }

    return $user;
}

/**
 * Login rate limiting (brute force koruması)
 * @param string $username
 * @return bool True ise izin ver, false ise engelle
 */
function checkLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 0,
            'first_attempt' => time()
        ];
    }

    $attempts = $_SESSION[$key];

    // LOGIN_TIMEOUT süresi geçmişse sıfırla
    if (time() - $attempts['first_attempt'] > LOGIN_TIMEOUT) {
        $_SESSION[$key] = [
            'count' => 0,
            'first_attempt' => time()
        ];
        return true;
    }

    // Maksimum deneme sayısı aşıldı mı?
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        $remaining = LOGIN_TIMEOUT - (time() - $attempts['first_attempt']);
        $minutes = ceil($remaining / 60);
        flash('error', "Çok fazla başarısız giriş denemesi. Lütfen {$minutes} dakika sonra tekrar deneyin.", 'error');
        return false;
    }

    return true;
}

/**
 * Başarısız giriş denemesini kaydet
 * @param string $username
 */
function recordFailedLogin($username) {
    $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 0,
            'first_attempt' => time()
        ];
    }

    $_SESSION[$key]['count']++;
}

/**
 * Başarılı giriş sonrası deneme sayacını sıfırla
 * @param string $username
 */
function resetLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
    unset($_SESSION[$key]);
}

// Her istekte session validasyonu yap (login sayfası hariç)
$currentScript = basename($_SERVER['PHP_SELF']);

// Login sayfası dışındaki sayfalarda session validasyonu
if ($currentScript !== 'index.php') {
    if (isLoggedIn() && !validateSession()) {
        redirect(adminUrl('index.php'));
    }

    // Login gerektir
    requireLogin();
}
