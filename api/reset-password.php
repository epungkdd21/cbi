<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['token']) || !isset($input['password']) || !isset($input['confirm_password'])) {
    jsonResponse('error', 'Data tidak lengkap');
}

$token = trim($input['token']);
$password = $input['password'];
$confirm_password = $input['confirm_password'];

// Validate password
if (empty($password) || empty($confirm_password)) {
    jsonResponse('error', 'Password tidak boleh kosong');
}

if (strlen($password) < 8) {
    jsonResponse('error', 'Password minimal 8 karakter');
}

if ($password !== $confirm_password) {
    jsonResponse('error', 'Password tidak cocok');
}

try {
    // Verify token
    $stmt = $pdo->prepare('
        SELECT pt.admin_id, pt.expires_at 
        FROM password_reset_tokens pt 
        WHERE pt.token = ?
    ');
    $stmt->execute([$token]);
    $reset_token = $stmt->fetch();

    if (!$reset_token) {
        jsonResponse('error', 'Token reset tidak valid');
    }

    // Check if token is expired
    if (time() > strtotime($reset_token['expires_at'])) {
        // Delete expired token
        $stmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
        $stmt->execute([$token]);
        jsonResponse('error', 'Tautan reset telah kadaluarsa. Minta reset password baru.');
    }

    // Hash new password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Update admin password
    $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$password_hash, $reset_token['admin_id']]);

    // Delete used token
    $stmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
    $stmt->execute([$token]);

    error_log("Password reset successful for admin_id: {$reset_token['admin_id']}");
    jsonResponse('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');

} catch (Exception $e) {
    error_log('Reset password error: ' . $e->getMessage());
    jsonResponse('error', 'Terjadi kesalahan pada sistem');
}
?>
