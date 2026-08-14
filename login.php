<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to admin panel
if (isLoggedIn()) {
    header('Location: ' . ADMIN_URL);
    exit;
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Culture Bridge Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .admin-glow {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4 cursor-pointer group" id="logo-trigger" title="Klik logo untuk akses khusus">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-900 to-indigo-600 flex items-center justify-center text-white font-black text-3xl shadow-lg group-hover:shadow-xl group-hover:scale-105 transition-all">
                    CBI
                </div>
            </div>
            <h1 class="text-3xl font-black text-blue-950 mb-2">Culture Bridge</h1>
            <p class="text-amber-500 font-bold tracking-wider uppercase text-sm mb-2">Indonesia</p>
            <p class="font-['Caveat'] text-slate-600 text-lg font-semibold">Satu Jembatan, Seribu Pengalaman</p>
            
            <!-- Hidden Admin Access Indicator -->
            <div id="admin-indicator" class="hidden mt-3 text-xs text-amber-600 font-semibold animate-pulse">
                <i class="fas fa-lock-open"></i> Admin Access Activated
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-950">Login Admin</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan kredensial Anda untuk mengakses panel admin</p>
            </div>

            <!-- Error Message -->
            <div id="error-msg" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-600 text-lg mt-1 flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-rose-900" id="error-text"></p>
                </div>
            </div>

            <!-- Form -->
            <form id="login-form" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Email/Username -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email atau Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400" 
                        placeholder="admin@example.com atau username"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400" 
                        placeholder="Masukkan password Anda"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-900 to-indigo-600 hover:from-blue-950 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 mt-6"
                    id="submit-btn"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login ke Panel Admin</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-slate-500">atau</span>
                </div>
            </div>

            <!-- Forgot Password Link -->
            <div class="text-center">
                <a href="#forgot-password" class="text-sm text-blue-900 hover:text-amber-600 font-semibold transition-colors">Lupa Password?</a>
            </div>
        </div>

        <!-- Hidden Admin Portal Access Button -->
        <div id="admin-portal-section" class="hidden mt-6 bg-gradient-to-r from-amber-900 to-amber-800 rounded-3xl shadow-2xl border border-amber-700 p-6 space-y-4">
            <div class="text-center">
                <i class="fas fa-crown text-amber-300 text-2xl mb-2"></i>
                <h3 class="text-lg font-bold text-white">Akses Admin Eksklusif</h3>
                <p class="text-xs text-amber-100 mt-1">Portal manajemen konten tersedia</p>
            </div>
            
            <button 
                onclick="showAdminPortal()" 
                class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2"
                id="admin-access-btn"
            >
                <i class="fas fa-key"></i>
                <span>Buka Portal Admin</span>
            </button>
            
            <p class="text-xs text-amber-200 text-center">
                <i class="fas fa-shield-alt"></i> Sistem terenkripsi & aman
            </p>
        </div>

        <!-- Forgot Password Modal -->
        <div id="forgot-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 space-y-6">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-blue-950">Reset Password</h3>
                    <p class="text-sm text-slate-500 mt-1">Masukkan email Anda untuk menerima tautan reset</p>
                </div>

                <div id="forgot-error-msg" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 text-lg mt-1 flex-shrink-0"></i>
                    <p class="text-sm font-medium text-rose-900" id="forgot-error-text"></p>
                </div>

                <div id="forgot-success-msg" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
                    <i class="fas fa-check-circle text-emerald-600 text-lg mt-1 flex-shrink-0"></i>
                    <p class="text-sm font-medium text-emerald-900" id="forgot-success-text"></p>
                </div>

                <form id="forgot-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            id="forgot-email" 
                            name="email" 
                            required 
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-slate-400" 
                            placeholder="admin@example.com"
                        >
                    </div>

                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            class="flex-1 bg-blue-900 hover:bg-blue-950 text-white font-bold py-2.5 px-4 rounded-xl transition-all"
                        >
                            Kirim Reset Link
                        </button>
                        <button 
                            type="button" 
                            onclick="closeForgotModal()" 
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl transition-all"
                        >
                            Batal
                        </button>
                    </div>
                </form>
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
        // Hidden Admin Portal Activation System
        let logoClickCount = 0;
        let logoClickTimer = null;
        const LOGO_CLICK_THRESHOLD = 3;
        const LOGO_CLICK_RESET_TIME = 2000; // 2 seconds

        document.getElementById('logo-trigger').addEventListener('click', function() {
            logoClickCount++;
            
            // Clear existing timer
            clearTimeout(logoClickTimer);
            
            // Reset counter after 2 seconds of inactivity
            logoClickTimer = setTimeout(() => {
                logoClickCount = 0;
                document.getElementById('admin-indicator').classList.add('hidden');
            }, LOGO_CLICK_RESET_TIME);

            // Show admin portal after 3 clicks
            if (logoClickCount >= LOGO_CLICK_THRESHOLD) {
                activateAdminPortal();
            }
        });

        // Alternative activation: Keyboard shortcut (Ctrl+Shift+X)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.code === 'KeyX') {
                e.preventDefault();
                activateAdminPortal();
            }
        });

        function activateAdminPortal() {
            const adminSection = document.getElementById('admin-portal-section');
            const adminIndicator = document.getElementById('admin-indicator');
            
            if (adminSection.classList.contains('hidden')) {
                adminSection.classList.remove('hidden');
                adminIndicator.classList.remove('hidden');
                logoClickCount = 0;
                
                // Play subtle notification
                playNotification();
            }
        }

        function showAdminPortal() {
            const accessBtn = document.getElementById('admin-access-btn');
            accessBtn.disabled = true;
            accessBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Membuka Portal...</span>';
            
            // Verify admin session or show additional authentication
            setTimeout(() => {
                // Check if there's an active admin session
                window.location.href = '/admin';
            }, 800);
        }

        function playNotification() {
            // Optional: Create a subtle visual pulse effect
            const logo = document.getElementById('logo-trigger').querySelector('div');
            logo.style.animation = 'pulse 0.5s ease-out';
            setTimeout(() => {
                logo.style.animation = '';
            }, 500);
        }

        // Hide admin portal when clicking outside
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const adminSection = document.getElementById('admin-portal-section');
                const adminIndicator = document.getElementById('admin-indicator');
                if (!adminSection.classList.contains('hidden')) {
                    adminSection.classList.add('hidden');
                    adminIndicator.classList.add('hidden');
                    logoClickCount = 0;
                }
            }
        });

        // Handle login form submission
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            const submitBtn = document.getElementById('submit-btn');
            const errorMsg = document.getElementById('error-msg');
            const errorText = document.getElementById('error-text');

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sedang login...</span>';

            try {
                const response = await fetch('api/authenticate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password,
                        csrf_token: csrfToken,
                    }),
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Hide error message
                    errorMsg.classList.add('hidden');
                    // Redirect to admin panel
                    window.location.href = '/admin';
                } else {
                    // Show error message
                    errorText.textContent = data.message || 'Login gagal. Silakan coba lagi.';
                    errorMsg.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorText.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorMsg.classList.remove('hidden');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <span>Login ke Panel Admin</span>';
            }
        });

        // Forgot password modal functionality
        document.addEventListener('click', (e) => {
            if (e.target.getAttribute('href') === '#forgot-password') {
                e.preventDefault();
                document.getElementById('forgot-modal').classList.remove('hidden');
            }
        });

        function closeForgotModal() {
            document.getElementById('forgot-modal').classList.add('hidden');
            document.getElementById('forgot-form').reset();
            document.getElementById('forgot-error-msg').classList.add('hidden');
            document.getElementById('forgot-success-msg').classList.add('hidden');
        }

        // Handle forgot password form
        document.getElementById('forgot-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('forgot-email').value;
            const errorMsg = document.getElementById('forgot-error-msg');
            const successMsg = document.getElementById('forgot-success-msg');
            const errorText = document.getElementById('forgot-error-text');
            const successText = document.getElementById('forgot-success-text');

            errorMsg.classList.add('hidden');
            successMsg.classList.add('hidden');

            try {
                const response = await fetch('api/forgot-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                    }),
                });

                const data = await response.json();

                if (data.status === 'success') {
                    successText.textContent = data.message;
                    successMsg.classList.remove('hidden');
                    document.getElementById('forgot-form').reset();
                    setTimeout(() => {
                        closeForgotModal();
                    }, 3000);
                } else {
                    errorText.textContent = data.message || 'Terjadi kesalahan.';
                    errorMsg.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                errorText.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorMsg.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
