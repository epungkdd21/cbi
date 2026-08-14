<?php
header('Content-Type: application/json');

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    jsonResponse('error', 'Username dan password harus diisi');
}

$username = trim($input['username']);
$password = $input['password'];

// Validate input
if (empty($username) || empty($password)) {
    jsonResponse('error', 'Username dan password tidak boleh kosong');
}

// Rate limiting check
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = 'login_attempts_' . $ip;

if (isset($_SESSION[$rate_limit_key])) {
    $attempts = $_SESSION[$rate_limit_key];
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $attempts['time'] < LOGIN_ATTEMPT_WINDOW) {
            jsonResponse('error', 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.');
        } else {
            unset($_SESSION[$rate_limit_key]);
        }
    }
}

try {
    // Query admin user
    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM admin_users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        // Log failed attempt
        if (!isset($_SESSION[$rate_limit_key])) {
            $_SESSION[$rate_limit_key] = [
                'count' => 1,
                'time' => time(),
            ];
        } else {
            $_SESSION[$rate_limit_key]['count']++;
        }

        jsonResponse('error', 'Username atau password salah');
    }

    // Clear rate limit on successful login
    if (isset($_SESSION[$rate_limit_key])) {
        unset($_SESSION[$rate_limit_key]);
    }

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Set session data
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['username'] = $admin['username'];
    $_SESSION['email'] = $admin['email'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Log successful login
    error_log("Admin login successful: {$admin['username']} from {$ip}");

    jsonResponse('success', 'Login berhasil', [
        'username' => $admin['username'],
        'redirect' => 'admin.html',
    ]);

} catch (Exception $e) {
    error_log('Authentication error: ' . $e->getMessage());
    jsonResponse('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.');
}
?>
