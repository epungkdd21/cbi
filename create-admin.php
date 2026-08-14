<?php
/**
 * Create Admin User Script
 * Run from CLI: php create-admin.php
 * Or from browser: https://yoursite.com/create-admin.php
 * 
 * ⚠️ SECURITY WARNING: Delete or protect this file after creating first admin!
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

// Initialize session for CSRF if in web mode
if (php_sapi_name() !== 'cli') {
    initializeSecureSession();
    setSecurityHeaders();
}

$mode = php_sapi_name() === 'cli' ? 'cli' : 'web';
$error = null;
$success = false;

/**
 * CLI Mode
 */
if ($mode === 'cli') {
    cliMode();
} else {
    /**
     * Web Mode - HTML Form
     */
    webMode();
}

/**
 * CLI Mode - Interactive Command Line
 */
function cliMode() {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║     🔑 CBI Admin System - Create First Admin User              ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    
    // Check if admin already exists
    if (adminExists()) {
        echo "⚠️  WARNING: Admin user(s) already exist in the database!\n";
        echo "Do you want to create another admin account? (yes/no): ";
        $response = trim(fgets(STDIN));
        if (strtolower($response) !== 'yes') {
            echo "Operation cancelled.\n\n";
            exit(0);
        }
        echo "\n";
    }
    
    // Get username
    echo "📝 Enter admin username (3-50 characters): ";
    $username = trim(fgets(STDIN));
    
    if (!validateUsername($username)) {
        echo "❌ Invalid username! Use 3-50 alphanumeric characters (a-z, 0-9, _, -)\n\n";
        exit(1);
    }
    
    // Check if username exists
    if (userExists('username', $username)) {
        echo "❌ Username already exists! Please choose another.\n\n";
        exit(1);
    }
    
    // Get email
    echo "📧 Enter admin email: ";
    $email = trim(fgets(STDIN));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Invalid email format!\n\n";
        exit(1);
    }
    
    // Check if email exists
    if (userExists('email', $email)) {
        echo "❌ Email already registered! Please use another.\n\n";
        exit(1);
    }
    
    // Get password
    echo "🔐 Enter admin password (minimum 8 characters): ";
    system('stty -echo'); // Hide input
    $password = trim(fgets(STDIN));
    system('stty echo'); // Show input again
    echo "\n";
    
    if (strlen($password) < 8) {
        echo "❌ Password must be at least 8 characters!\n\n";
        exit(1);
    }
    
    // Confirm password
    echo "🔐 Confirm password: ";
    system('stty -echo');
    $passwordConfirm = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    
    if ($password !== $passwordConfirm) {
        echo "❌ Passwords do not match!\n\n";
        exit(1);
    }
    
    // Check password strength
    $strength = getPasswordStrength($password);
    $strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    echo "\n📊 Password Strength: " . $strengthText[$strength] . "\n";
    
    if ($strength < 2) {
        echo "⚠️  Weak password! Recommended: Use uppercase, lowercase, numbers, special chars.\n";
        echo "Continue anyway? (yes/no): ";
        $response = trim(fgets(STDIN));
        if (strtolower($response) !== 'yes') {
            echo "Operation cancelled.\n\n";
            exit(0);
        }
    }
    
    echo "\n";
    echo "📋 Verifying information:\n";
    echo "   • Username: " . escapeHTML($username) . "\n";
    echo "   • Email: " . escapeHTML($email) . "\n";
    echo "   • Password Strength: " . $strengthText[$strength] . "\n";
    echo "\n";
    echo "✓ Create this admin account? (yes/no): ";
    $response = trim(fgets(STDIN));
    
    if (strtolower($response) !== 'yes') {
        echo "Operation cancelled.\n\n";
        exit(0);
    }
    
    // Create admin user
    try {
        $result = createAdminUser($username, $email, $password);
        
        if ($result) {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════════════╗\n";
            echo "║                    ✅ SUCCESS!                                 ║\n";
            echo "╚════════════════════════════════════════════════════════════════╝\n";
            echo "\n";
            echo "✓ Admin user created successfully!\n\n";
            echo "📧 Username: " . escapeHTML($username) . "\n";
            echo "📧 Email: " . escapeHTML($email) . "\n";
            echo "\n";
            echo "🔗 You can now login at: " . BASE_URL . "/login\n";
            echo "\n";
            echo "⚠️  SECURITY REMINDER:\n";
            echo "   • Delete this script (create-admin.php) from the server!\n";
            echo "   • Use a strong, unique password\n";
            echo "   • Enable HTTPS on production\n";
            echo "   • Monitor security logs regularly\n";
            echo "\n";
            exit(0);
        } else {
            echo "❌ Failed to create admin user!\n\n";
            exit(1);
        }
    } catch (Exception $e) {
        echo "❌ Error: " . escapeHTML($e->getMessage()) . "\n\n";
        exit(1);
    }
}

/**
 * Web Mode - HTML Form
 */
function webMode() {
    global $error, $success;
    
    // Check if form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!verifyCsrfToken($csrfToken)) {
                $error = "Invalid security token. Please try again.";
            } else {
                $username = sanitizeInput($_POST['username'] ?? '');
                $email = sanitizeEmail($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';
                
                // Validate
                if (!validateUsername($username)) {
                    $error = "Invalid username! Use 3-50 alphanumeric characters.";
                } elseif (!$email) {
                    $error = "Invalid email format!";
                } elseif (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters!";
                } elseif ($password !== $passwordConfirm) {
                    $error = "Passwords do not match!";
                } elseif (userExists('username', $username)) {
                    $error = "Username already exists!";
                } elseif (userExists('email', $email)) {
                    $error = "Email already registered!";
                } else {
                    // Create admin
                    if (createAdminUser($username, $email, $password)) {
                        $success = true;
                    } else {
                        $error = "Failed to create admin user. Please try again.";
                    }
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    $csrfToken = generateCsrfToken();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - CBI Admin System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-900 to-indigo-600 flex items-center justify-center text-white font-black text-3xl shadow-lg">
                    CBI
                </div>
            </div>
            <h1 class="text-3xl font-black text-blue-950 mb-2">Create Admin User</h1>
            <p class="text-slate-600 text-sm">Set up your first administrator account</p>
        </div>

        <?php if ($success): ?>
        <!-- Success Message -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 space-y-6 mb-6">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
                    <i class="fas fa-check text-emerald-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-emerald-600 mb-2">Success!</h2>
                <p class="text-slate-600 mb-4">Admin user created successfully</p>
                
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-left mb-4">
                    <p class="text-sm font-semibold text-emerald-900 mb-2">Admin Details:</p>
                    <p class="text-sm text-emerald-800 mb-1">
                        <i class="fas fa-user w-4"></i> Username: <strong><?php echo escapeHTML($_POST['username']); ?></strong>
                    </p>
                    <p class="text-sm text-emerald-800">
                        <i class="fas fa-envelope w-4"></i> Email: <strong><?php echo escapeHTML($_POST['email']); ?></strong>
                    </p>
                </div>
                
                <a href="<?php echo BASE_URL; ?>/login" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 mb-3">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Go to Login</span>
                </a>
                
                <p class="text-xs text-slate-500 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-left">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    <strong>Security Reminder:</strong> Delete this file (create-admin.php) from your server immediately!
                </p>
            </div>
        </div>
        <?php else: ?>
        <!-- Form -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 space-y-6">
            <?php if ($error): ?>
            <!-- Error Message -->
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-600 text-lg mt-1 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-rose-900"><?php echo escapeHTML($error); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        required 
                        pattern="^[a-zA-Z0-9_-]{3,50}$"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400"
                        placeholder="admin"
                        value="<?php echo escapeHTML($_POST['username'] ?? ''); ?>"
                    >
                    <p class="text-xs text-slate-500 mt-1">3-50 characters (letters, numbers, dash, underscore)</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400"
                        placeholder="admin@example.com"
                        value="<?php echo escapeHTML($_POST['email'] ?? ''); ?>"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required 
                        minlength="8"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400"
                        placeholder="Minimum 8 characters"
                        onchange="checkPasswordStrength()"
                    >
                    <div class="mt-2">
                        <div class="flex gap-1 mb-1">
                            <div id="strength-0" class="flex-1 h-1 bg-slate-200 rounded"></div>
                            <div id="strength-1" class="flex-1 h-1 bg-slate-200 rounded"></div>
                            <div id="strength-2" class="flex-1 h-1 bg-slate-200 rounded"></div>
                            <div id="strength-3" class="flex-1 h-1 bg-slate-200 rounded"></div>
                            <div id="strength-4" class="flex-1 h-1 bg-slate-200 rounded"></div>
                        </div>
                        <p class="text-xs text-slate-500">Password Strength: <span id="strength-text">Very Weak</span></p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
                    <input 
                        type="password" 
                        name="password_confirm" 
                        required 
                        minlength="8"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400"
                        placeholder="Re-enter password"
                    >
                </div>

                <!-- Password Requirements -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-blue-900 mb-2">Password Requirements:</p>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>✓ At least 8 characters</li>
                        <li>✓ Mix of uppercase and lowercase letters</li>
                        <li>✓ Include numbers (recommended)</li>
                        <li>✓ Include special characters (recommended)</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-900 to-indigo-600 hover:from-blue-950 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 mt-6"
                >
                    <i class="fas fa-user-plus"></i>
                    <span>Create Admin User</span>
                </button>
            </form>

            <!-- Security Notice -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-xs text-yellow-800">
                    <i class="fas fa-shield-alt text-yellow-600 mr-2"></i>
                    <strong>Security:</strong> This file should be deleted after creating the first admin. Do not leave it on your server!
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500', 'bg-green-600'];
            
            let strength = 0;
            
            // Length check
            if (password.length >= 12) strength += 2;
            else if (password.length >= 8) strength += 1;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 1;
            
            // Numbers check
            if (/[0-9]/.test(password)) strength += 1;
            
            // Special characters check
            if (/[^a-zA-Z0-9]/.test(password)) strength += 2;
            
            strength = Math.min(5, strength);
            
            // Update strength bars
            for (let i = 0; i < 5; i++) {
                const bar = document.getElementById('strength-' + i);
                bar.className = 'flex-1 h-1 rounded ' + (i < strength ? colors[strength] : 'bg-slate-200');
            }
            
            document.getElementById('strength-text').textContent = strengthLevels[strength];
        }
    </script>
</body>
</html>
    <?php
}

/**
 * Helper Functions
 */

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username) === 1;
}

function adminExists() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

function userExists($field, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE {$field} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

function createAdminUser($username, $email, $password) {
    global $pdo;
    
    try {
        // Hash password
        $passwordHash = hashPassword($password);
        
        // Insert admin user
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (username, email, password_hash)
            VALUES (?, ?, ?)
        ");
        
        $result = $stmt->execute([$username, $email, $passwordHash]);
        
        if ($result) {
            // Log the event
            $userId = $pdo->lastInsertId();
            logSecurityEvent('ADMIN_USER_CREATED', [
                'admin_id' => $userId,
                'username' => $username,
                'email' => $email
            ]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        if (mode === 'cli') {
            echo "Database error: " . $e->getMessage() . "\n";
        }
        error_log("Error creating admin user: " . $e->getMessage());
        return false;
    }
}

?>
