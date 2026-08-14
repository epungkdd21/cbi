<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || empty($input['email'])) {
    jsonResponse('error', 'Email harus diisi');
}

$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Format email tidak valid');
}

try {
    // Check if email exists
    $stmt = $pdo->prepare('SELECT id, username FROM admin_users WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Don't reveal if email exists (security best practice)
        jsonResponse('success', 'Jika email terdaftar, tautan reset akan dikirim');
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + PASSWORD_RESET_TOKEN_EXPIRY);

    // Delete old reset tokens for this admin
    $stmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE admin_id = ?');
    $stmt->execute([$admin['id']]);

    // Save new reset token
    $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (admin_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$admin['id'], $token, $expires_at]);

    // Build reset link
    $reset_link = RESET_URL . '?token=' . urlencode($token);

    // Send email with reset link
    $to = $email;
    $subject = 'Reset Password - Culture Bridge Indonesia';
    $message = "
    <html>
        <head>
            <style>
                body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); color: white; padding: 20px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f8fafc; margin: 20px 0; border-radius: 10px; }
                .button { display: inline-block; background: #f59e0b; color: #1e3a8a; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                .footer { font-size: 12px; color: #64748b; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Reset Password</h2>
                </div>
                <div class='content'>
                    <p>Halo {$admin['username']},</p>
                    <p>Kami menerima permintaan untuk mereset password akun admin Anda. Klik tombol di bawah untuk melanjutkan:</p>
                    <center>
                        <a href='{$reset_link}' class='button'>Reset Password</a>
                    </center>
                    <p>Atau salin link ini ke browser Anda:</p>
                    <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>{$reset_link}</p>
                    <p style='color: #e11d48;'><strong>Perhatian:</strong> Tautan ini berlaku selama 24 jam. Jika Anda tidak meminta reset password, abaikan email ini.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 Culture Bridge Indonesia. All rights reserved.</p>
                </div>
            </div>
        </body>
    </html>
    ";

    // Send email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";

    if (!mail($to, $subject, $message, $headers)) {
        error_log("Failed to send reset email to: {$to}");
        jsonResponse('success', 'Jika email terdaftar, tautan reset akan dikirim');
    }

    error_log("Password reset email sent to: {$email}");
    jsonResponse('success', 'Jika email terdaftar, tautan reset akan dikirim');

} catch (Exception $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    jsonResponse('error', 'Terjadi kesalahan pada sistem');
}
?>
