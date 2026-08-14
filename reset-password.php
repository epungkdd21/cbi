<?php
require_once __DIR__ . '/config.php';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Token Tidak Valid</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
        <div class="text-center max-w-md">
            <div class="inline-block mb-4 w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="text-2xl font-bold text-blue-950 mb-2">Link Tidak Valid</h1>
            <p class="text-slate-600 mb-6">Tautan reset password Anda tidak valid atau telah kadaluarsa. Silakan minta reset password baru.</p>
            <a href="login.php" class="inline-block bg-blue-900 hover:bg-blue-950 text-white font-bold px-6 py-3 rounded-xl">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Login
            </a>
        </div>
    </body>
    </html>
    ');
}

$token = trim($_GET['token']);

// Verify token exists in database
try {
    $stmt = $pdo->prepare('
        SELECT pt.admin_id, pt.expires_at 
        FROM password_reset_tokens pt 
        WHERE pt.token = ? AND pt.expires_at > NOW()
    ');
    $stmt->execute([$token]);
    $reset_token = $stmt->fetch();

    if (!$reset_token) {
        die('
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Token Kadaluarsa</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
            <div class="text-center max-w-md">
                <div class="inline-block mb-4 w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl">
                    <i class="fas fa-clock"></i>
                </div>
                <h1 class="text-2xl font-bold text-blue-950 mb-2">Tautan Kadaluarsa</h1>
                <p class="text-slate-600 mb-6">Tautan reset password ini telah kadaluarsa. Silakan minta reset password baru.</p>
                <a href="login.php" class="inline-block bg-blue-900 hover:bg-blue-950 text-white font-bold px-6 py-3 rounded-xl">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Login
                </a>
            </div>
        </body>
        </html>
        ');
    }
} catch (Exception $e) {
    error_log('Token verification error: ' . $e->getMessage());
    die('Terjadi kesalahan pada sistem.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Culture Bridge Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-900 to-indigo-600 flex items-center justify-center text-white font-black text-3xl shadow-lg">
                    CBI
                </div>
            </div>
            <h1 class="text-3xl font-black text-blue-950 mb-2">Culture Bridge</h1>
            <p class="text-amber-500 font-bold tracking-wider uppercase text-sm mb-2">Indonesia</p>
            <p class="font-['Caveat'] text-slate-600 text-lg font-semibold">Satu Jembatan, Seribu Pengalaman</p>
        </div>

        <!-- Reset Password Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-950">Reset Password</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan password baru Anda untuk mengakses panel admin</p>
            </div>

            <!-- Error Message -->
            <div id="error-msg" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-600 text-lg mt-1 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-rose-900" id="error-text"></p>
                </div>
            </div>

            <!-- Success Message -->
            <div id="success-msg" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-check-circle text-emerald-600 text-lg mt-1 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-emerald-900" id="success-text"></p>
                </div>
            </div>

            <!-- Form -->
            <form id="reset-form" class="space-y-4">
                <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400" 
                        placeholder="Minimal 8 karakter"
                    >
                    <p class="text-xs text-slate-500 mt-2">Password harus minimal 8 karakter</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        id="confirm-password" 
                        name="confirm_password" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400" 
                        placeholder="Masukkan password yang sama"
                    >
                </div>

                <!-- Password Strength Indicator -->
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-xs font-semibold text-slate-600 mb-2">Kekuatan Password:</p>
                    <div class="flex gap-1">
                        <div class="flex-1 h-2 bg-slate-200 rounded-full" id="strength-1"></div>
                        <div class="flex-1 h-2 bg-slate-200 rounded-full" id="strength-2"></div>
                        <div class="flex-1 h-2 bg-slate-200 rounded-full" id="strength-3"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                        Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol
                    </p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-900 to-indigo-600 hover:from-blue-950 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 mt-6"
                    id="submit-btn"
                >
                    <i class="fas fa-lock"></i>
                    <span>Reset Password</span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center">
                <a href="login.php" class="text-sm text-slate-600 hover:text-blue-900 font-semibold transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-xs text-slate-500">
                <i class="fas fa-lock text-slate-400 mr-1"></i>
                Koneksi Anda diamankan dengan enkripsi
            </p>
        </div>
    </div>

    <script>
        // Password strength indicator
        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password) && /[!@#$%^&*]/.test(password)) strength++;

            const indicators = [
                document.getElementById('strength-1'),
                document.getElementById('strength-2'),
                document.getElementById('strength-3'),
            ];

            indicators.forEach((indicator, index) => {
                if (index < strength) {
                    indicator.classList.remove('bg-slate-200');
                    if (strength === 1) {
                        indicator.classList.add('bg-rose-500');
                    } else if (strength === 2) {
                        indicator.classList.add('bg-amber-500');
                    } else {
                        indicator.classList.add('bg-emerald-500');
                    }
                } else {
                    indicator.classList.add('bg-slate-200');
                    indicator.classList.remove('bg-rose-500', 'bg-amber-500', 'bg-emerald-500');
                }
            });
        }

        document.getElementById('password').addEventListener('input', updatePasswordStrength);

        // Handle reset form submission
        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const token = document.getElementById('token').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const submitBtn = document.getElementById('submit-btn');
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const errorText = document.getElementById('error-text');
            const successText = document.getElementById('success-text');

            // Hide previous messages
            errorMsg.classList.add('hidden');
            successMsg.classList.add('hidden');

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sedang memproses...</span>';

            try {
                const response = await fetch('api/reset-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token: token,
                        password: password,
                        confirm_password: confirmPassword,
                    }),
                });

                const data = await response.json();

                if (data.status === 'success') {
                    successText.textContent = data.message;
                    successMsg.classList.remove('hidden');
                    document.getElementById('reset-form').reset();
                    
                    // Redirect to login after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    errorText.textContent = data.message || 'Terjadi kesalahan.';
                    errorMsg.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Reset error:', error);
                errorText.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorMsg.classList.remove('hidden');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> <span>Reset Password</span>';
            }
        });
    </script>
</body>
</html>
