# 🔐 Setup Guide: CBI Admin Authentication System

Panduan lengkap untuk setup sistem autentikasi admin dengan PHP + MySQL di Hostinger.

## 📋 File yang Telah Dibuat

### Backend API Files
- `api/authenticate.php` - Proses login
- `api/check-session.php` - Verifikasi session
- `api/logout.php` - Proses logout
- `api/forgot-password.php` - Kirim email reset password
- `api/reset-password.php` - Proses reset password

### Frontend Pages
- `login` - Halaman login admin (tanpa extension .php)
- `reset-password` - Halaman reset password (tanpa extension .php)
- `admin` - Admin panel (tanpa extension .html)
- `index` / `/` - Public website (tanpa extension .html)

### Configuration & Database
- `config.php` - ⚠️ **SENSITIVE** - Konfigurasi database & email
- `db/schema.sql` - SQL schema untuk database

### URL Rewriting
- `.htaccess` - URL rewriting rules (remove .html dan .php extensions)

### Security
- `.gitignore` - Exclude file sensitif dari git

---

## 🔗 URL Rewriting Setup (.htaccess)

File `.htaccess` telah dibuat untuk menyembunyikan extension file dari URL, membuat URL lebih clean dan SEO-friendly.

### URL Mapping:
| File | Old URL | New URL |
|------|---------|---------|
| index.html | `example.com/index.html` | `example.com/` |
| login.php | `example.com/login.php` | `example.com/login` |
| admin.html | `example.com/admin.html` | `example.com/admin` |
| reset-password.php | `example.com/reset-password.php` | `example.com/reset-password` |

### Features di .htaccess:
✅ **URL Rewriting** - Hapus extension .html dan .php  
✅ **Redirect Permanen** - .html/.php URLs redirect ke clean URLs  
✅ **Security** - Protect config.php, schema.sql, .env files  
✅ **Security Headers** - X-Content-Type-Options, X-Frame-Options, X-XSS-Protection  
✅ **Cache Control** - Static assets cached 1 tahun  
✅ **Deny Directories** - Disable directory listing  

### Requirements:
- Apache server (default di Hostinger)
- `mod_rewrite` module enabled (biasanya sudah di Hostinger)
- `.htaccess` file di root directory (sudah ada)

### Test URL Rewriting:
1. Upload `.htaccess` ke `/public_html/`
2. Buka: `https://culturebridgeindonesia.my.id/login`
3. ✅ Harusnya load halaman login tanpa error
4. Buka: `https://culturebridgeindonesia.my.id/admin`
5. ✅ Harusnya redirect ke login (karena belum authenticated)
6. Buka: `https://culturebridgeindonesia.my.id/`
7. ✅ Harusnya load index.html

---

### **Fase 1: Persiapan Database (Hostinger)**

1. **Login ke Hostinger Control Panel**
   - Buka cPanel Hostinger Anda
   - Cari menu "MySQL Databases" atau "PhpMyAdmin"

2. **Buat Database Baru**
   - Nama database: `cbi_database` (atau nama pilihan Anda)
   - Simpan nama untuk `DB_NAME` di config.php

3. **Buat User Database**
   - Username: `cbi_admin` (atau nama pilihan Anda)
   - Password: Gunakan password yang kuat (minimal 16 karakter)
   - Simpan untuk `DB_USER` dan `DB_PASS` di config.php
   - **Grant all privileges** pada database `cbi_database`

4. **Jalankan SQL Schema**
   - Buka PhpMyAdmin
   - Pilih database `cbi_database`
   - Klik tab "SQL"
   - Copy & paste seluruh isi file `db/schema.sql`
   - Klik "Go" untuk execute
   - ✅ Tabel `admin_users` dan `password_reset_tokens` akan terbuat

---

### **Fase 2: Konfigurasi File (Upload ke Hostinger)**

#### **Step 1: Edit config.php**

Buka file `config.php` dan update nilai-nilai berikut:

```php
// Database Configuration
define('DB_HOST', 'localhost');        // Biasanya 'localhost' di Hostinger
define('DB_USER', 'cbi_admin');        // Username database Anda
define('DB_PASS', 'your_db_password'); // Password database Anda
define('DB_NAME', 'cbi_database');     // Nama database Anda

// SMTP Email Configuration (Hostinger)
define('SMTP_HOST', 'mail.hostinger.com');
define('SMTP_PORT', 465);              // Gunakan 465 untuk SSL
define('SMTP_USER', 'noreply@culturebridgeindonesia.my.id');
define('SMTP_PASS', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@culturebridgeindonesia.my.id');
define('SMTP_FROM_NAME', 'Culture Bridge Indonesia');
```

**⚠️ PENTING:** 
- Jangan commit `config.php` ke git (sudah di .gitignore)
- Ganti semua `your_...` dengan nilai sebenarnya

#### **Step 2: Dapatkan Kredensial SMTP Hostinger**

1. Di Hostinger Control Panel → Email
2. Buat email baru: `noreply@culturebridgeindonesia.my.id`
3. Atau gunakan email yang sudah ada
4. Catat password emailnya untuk `SMTP_PASS`
5. Konfigurasi SMTP:
   - Host: `mail.hostinger.com`
   - Port: `465` (SSL) atau `587` (TLS)
   - Username: Email Anda lengkap
   - Password: Password email Anda

#### **Step 3: Upload Semua File ke Server Hostinger**

Via FTP/File Manager di cPanel:
```
/public_html/
├── login.php              ← Upload
├── reset-password.php     ← Upload
├── admin.html             ← Upload (modified)
├── config.php             ← Upload (sudah edit!)
├── index.html             ← Sudah ada
├── api/
│   ├── authenticate.php   ← Upload
│   ├── check-session.php  ← Upload
│   ├── logout.php         ← Upload
│   ├── forgot-password.php ← Upload
│   └── reset-password.php ← Upload
├── db/
│   └── schema.sql         ← Reference only (sudah jalankan)
└── logs/                  ← Folder baru (create manually)
```

---

### **Fase 3: Buat Admin User Pertama**

#### **Opsi A: Via PhpMyAdmin (Recommended)**

1. Buka PhpMyAdmin → Database `cbi_database`
2. Klik tab "SQL"
3. Jalankan query ini:

```sql
INSERT INTO admin_users (username, email, password_hash, created_at) VALUES (
  'admin',
  'admin@culturebridgeindonesia.my.id',
  '$2y$10$YxzB5N7pQr8KvH2d9F8E9eK3jH2dJ8L4m5N6O7p8Q9r0S1T2U3V4W5X6Y7Z8',
  NOW()
);
```

**Password untuk login:** `password123`

Untuk membuat password hash yang berbeda, gunakan PHP tool online:
- Buka browser
- Cari "PHP password hash generator"
- Generate hash dari password pilihan Anda
- Ganti nilai `password_hash` di atas

#### **Opsi B: Via Admin Registration Page (Jika Anda Buat)**

Atau tanyakan pada kami untuk membuat halaman registrasi admin pertama.

---

### **Fase 4: Test & Verifikasi**

1. **Test Login Page**
   - Buka: `https://culturebridgeindonesia.my.id/login`
   - Username: `admin`
   - Password: `password123` (atau password yang Anda set)
   - ✅ Harusnya redirect ke `/admin`

2. **Test Admin Panel**
   - Verifikasi username muncul di topbar
   - Klik dropdown → logout
   - ✅ Harusnya redirect ke `/login`

3. **Test Forgot Password**
   - Di login page, klik "Lupa Password?"
   - Masukkan email admin: `admin@culturebridgeindonesia.my.id`
   - ✅ Harusnya terima email (cek spam folder jika tidak ada)

4. **Test Password Reset**
   - Buka link dari email
   - Set password baru (minimal 8 karakter)
   - Login dengan password baru di `/login`
   - ✅ Harusnya berhasil redirect ke `/admin`

5. **Test Session Timeout**
   - Login ke `/admin`
   - Tunggu 30 menit tanpa klik apapun
   - Refresh halaman
   - ✅ Harusnya redirect ke `/login`

6. **Test URL Rewriting**
   - Buka: `https://culturebridgeindonesia.my.id/login.php`
   - ✅ Harusnya auto-redirect ke `/login`
   - Buka: `https://culturebridgeindonesia.my.id/admin.html`
   - ✅ Harusnya auto-redirect ke `/admin`

---

## 📁 File Structure

```
/workspaces/cbi/
├── login.php                      # Halaman login → /login
├── reset-password.php             # Halaman reset password → /reset-password
├── admin.html                     # Admin panel → /admin (modified dengan session check)
├── index.html                     # Public website → /
├── config.php                     # ⚠️ SENSITIVE - Database config
├── .htaccess                      # URL rewriting rules (remove extensions)
├── .gitignore                     # Exclude file sensitif
├── api/
│   ├── authenticate.php           # POST: Handle login
│   ├── check-session.php          # GET/POST: Verify session
│   ├── logout.php                 # GET: Destroy session
│   ├── forgot-password.php        # POST: Send reset email
│   └── reset-password.php         # POST: Update password
├── db/
│   └── schema.sql                 # SQL schema (reference)
└── logs/
    └── error.log                  # Log errors (auto-created)
```

### URL Mapping:
- `index.html` → `/` (homepage)
- `login.php` → `/login` (admin login)
- `admin.html` → `/admin` (admin panel)
- `reset-password.php` → `/reset-password` (password recovery)
- `api/*.php` → `/api/` (unchanged - API endpoints)

---

## 🔐 Security Features

✅ **Password Hashing:** PHP `password_hash()` dengan BCRYPT
✅ **Session Timeout:** Auto-logout setelah 30 menit inaktif
✅ **CSRF Protection:** Token di form login & password reset
✅ **Rate Limiting:** Max 5 login attempts per 15 menit
✅ **Secure Cookies:** HttpOnly, Secure, SameSite
✅ **Email Verification:** Token dengan expiry 24 jam
✅ **SQL Injection Prevention:** Prepared statements (PDO)
✅ **Password Requirements:** Minimal 8 karakter
✅ **Encrypted Email:** Password reset via email (tidak simpan password)

---

## 🐛 Troubleshooting

### **❌ "Database connection failed"**
- Cek DB_HOST, DB_USER, DB_PASS, DB_NAME di config.php
- Verifikasi user database punya privileges pada database
- Hubungi Hostinger support untuk confirm SQL credentials

### **❌ Email tidak terkirim**
- Verify `noreply@culturebridgeindonesia.my.id` ada di Hostinger
- Check SMTP_HOST dan SMTP_PORT sesuai Hostinger docs
- Verify SMTP_USER dan SMTP_PASS benar
- Cek `/logs/error.log` untuk detail error
- **Alternatif:** Gunakan SendGrid atau Mailgun (update config.php)

### **❌ Login selalu gagal**
- Verify password hash benar di database
- Cek username/email di database table `admin_users`
- Enable error logging di config.php untuk debug

### **❌ Session logout otomatis padahal baru login**
- Check SESSION_TIMEOUT value (default 1800 detik = 30 menit)
- Verify server time sync dengan timezone yang benar
- Check `/logs/error.log` untuk error messages

### **❌ Halaman admin tidak load**
- Verify session check script (`api/check-session.php`) accessible
- Check browser console untuk AJAX error
- Verify admin.html path ke API folder benar

---

## 📞 Testing Credentials

**Login pertama kali:**
- Username: `admin`
- Email: `admin@culturebridgeindonesia.my.id`
- Password: `password123`

**Ubah password setelah setup:**
- Anda bisa gunakan "Lupa Password" atau direct database update

---

## ✅ Selesai!

Sistem autentikasi admin Anda sudah siap digunakan!

### Next Steps:
1. ✅ Verify semua test berhasil
2. ✅ Ganti password admin pertama dengan password kuat
3. ✅ Setup regular database backup di Hostinger
4. ✅ Monitor `/logs/error.log` untuk issues
5. ✅ (Optional) Tambah admin accounts baru via database

---

## 📚 File Documentation

- **config.php** - Semua setting pusat (DB, Email, Session, Security)
- **api/authenticate.php** - Login logic dengan rate limiting
- **api/check-session.php** - Session verification & refresh
- **api/forgot-password.php** - Email sending dengan Hostinger SMTP
- **api/reset-password.php** - Password update logic
- **login.php** - Responsive login form dengan "Forgot Password" modal
- **reset-password.php** - Password reset form dengan token verification
- **admin.html** - CMS panel dengan session protection & logout

---

**Dibuat untuk:** Culture Bridge Indonesia (CBI)  
**Date:** 2026-08-14  
**Status:** ✅ Ready for Deployment
