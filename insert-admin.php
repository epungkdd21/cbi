#!/usr/bin/env php
<?php
/**
 * Quick Admin Insert Script
 * Usage: php insert-admin.php <username> <email> <password>
 * 
 * Contoh:
 * php insert-admin.php admin admin@example.com MyPassword123!
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

// Parse command line arguments
if ($argc < 4) {
    echo "\n❌ Usage: php insert-admin.php <username> <email> <password>\n\n";
    echo "Contoh:\n";
    echo "  php insert-admin.php admin admin@example.com MyPassword123!\n";
    echo "  php insert-admin.php superadmin admin@site.com Secure@Pass2024\n\n";
    exit(1);
}

$username = $argv[1];
$email = $argv[2];
$password = $argv[3];

// Validate input
if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username)) {
    echo "❌ Username invalid! Use 3-50 chars (letters, numbers, dash, underscore)\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Email invalid! Use proper format: admin@example.com\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "❌ Password too short! Minimum 8 characters\n";
    exit(1);
}

// Check if username already exists
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "❌ Username '$username' already exists!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if email already exists
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "❌ Email '$email' already registered!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Hash password
$passwordHash = hashPassword($password);

// Insert admin user
try {
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (username, email, password_hash)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$username, $email, $passwordHash]);
    $adminId = $pdo->lastInsertId();
    
    // Log the event
    logSecurityEvent('ADMIN_USER_CREATED_CLI', [
        'admin_id' => $adminId,
        'username' => $username,
        'email' => $email
    ]);
    
    // Display success
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                    ✅ SUCCESS!                                 ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "✓ Admin user created successfully!\n\n";
    echo "📊 Details:\n";
    echo "   • ID: $adminId\n";
    echo "   • Username: $username\n";
    echo "   • Email: $email\n";
    echo "   • Password Strength: " . getPasswordStrengthText($password) . "\n\n";
    echo "🔗 Login URL: " . BASE_URL . "/login\n";
    echo "\n";
    echo "⚠️  NEXT STEPS:\n";
    echo "   1. Delete create-admin.php and insert-admin.php from server\n";
    echo "   2. Login with username: $username\n";
    echo "   3. Review security logs\n\n";
    
    exit(0);
    
} catch (PDOException $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Get password strength text
 */
function getPasswordStrengthText($password) {
    $strength = getPasswordStrength($password);
    $strengthTexts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    return $strengthTexts[$strength] ?? 'Unknown';
}

?>
