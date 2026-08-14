# 🔑 Create Admin User - Step by Step Instructions

## 📍 Quick Navigation

| Method | Time | Complexity | Best For |
|--------|------|-----------|----------|
| **CLI (Terminal)** | 2 min | Easy | Developers |
| **Web Browser** | 2 min | Very Easy | Everyone |

---

## 🎯 Step-by-Step Guide

### Prerequisites ✅

Sebelum membuat admin, pastikan:

```
✓ Database sudah di-import
  → Run: mysql -u root -p < db/schema.sql

✓ config.php sudah ter-konfigurasi
  → DB_HOST, DB_USER, DB_PASS, DB_NAME diisi

✓ File create-admin.php tersedia di root directory
  → Harus berada di: /workspaces/cbi/create-admin.php

✓ security.php tersedia di root
  → Harus berada di: /workspaces/cbi/security.php
```

---

## 🚀 CARA 1: CLI (Terminal) - RECOMMENDED

### Langkah 1: Buka Terminal
```bash
# Navigate ke project folder
cd /workspaces/cbi

# Atau jika menggunakan Hostinger:
ssh user@yoursite.com
cd public_html
```

### Langkah 2: Jalankan Script
```bash
php create-admin.php
```

### Langkah 3: Input Username
```
📝 Enter admin username (3-50 characters): admin
```

Kriteria username:
- ✓ Minimum 3 karakter, maksimum 50
- ✓ Hanya alphanumeric, dash (-), underscore (_)
- ✗ Jangan gunakan spasi atau special chars

Contoh valid: `admin`, `superadmin`, `cbi_admin`, `admin-user`

### Langkah 4: Input Email
```
📧 Enter admin email: admin@culturebridgeindonesia.my.id
```

**Format harus valid email!**

### Langkah 5: Input Password
```
🔐 Enter admin password (minimum 8 characters): 
```

**Saat mengetik, password tidak ditampilkan (hidden). Itu normal!**

Syarat password:
- Minimum 8 karakter
- Recommended: Mix uppercase, lowercase, numbers, special chars
- Contoh kuat: `MyAdm!n@2024`

### Langkah 6: Confirm Password
```
🔐 Confirm password: 
```

Ketik ulang password yang sama.

### Langkah 7: Review Password Strength
```
📊 Password Strength: Strong
```

Script menampilkan kekuatan password:
- ❌ Very Weak
- ⚠️ Weak
- 🟡 Fair
- ✅ Good
- ✅✅ Strong
- ✅✅✅ Very Strong

### Langkah 8: Verifikasi Informasi
```
📋 Verifying information:
   • Username: admin
   • Email: admin@culturebridgeindonesia.my.id
   • Password Strength: Strong

✓ Create this admin account? (yes/no): yes
```

Ketik `yes` untuk lanjut atau pilihan lain untuk cancel.

### Langkah 9: Success! 🎉
```
╔════════════════════════════════════════════════════════════════╗
║                    ✅ SUCCESS!                                 ║
╚════════════════════════════════════════════════════════════════╝

✓ Admin user created successfully!

📧 Username: admin
📧 Email: admin@culturebridgeindonesia.my.id

🔗 You can now login at: https://yoursite.com/login
```

---

## 🌐 CARA 2: Web Browser (GUI)

### Langkah 1: Buka Browser
Ketik URL di address bar:
```
https://yoursite.com/create-admin.php
```

Atau jika development:
```
http://localhost/create-admin.php
```

### Langkah 2: Lihat Form

Halaman akan menampilkan:
- ✓ Logo CBI
- ✓ Title "Create Admin User"
- ✓ Form dengan fields

### Langkah 3: Isi Username
Klik field "Username" dan ketik:
```
admin
```

Validasi real-time akan mengecek:
- Length (3-50 chars)
- Valid characters

### Langkah 4: Isi Email
Klik field "Email" dan ketik:
```
admin@culturebridgeindonesia.my.id
```

### Langkah 5: Isi Password
Klik field "Password" dan ketik password:
```
MyStrongPassword123!
```

Saat Anda mengetik, akan muncul:
- ▯▯▯▯▯ Strength meter (strength bars)
- Text: "Password Strength: [level]"

### Langkah 6: Confirm Password
Klik field "Confirm Password" dan ketik ulang:
```
MyStrongPassword123!
```

### Langkah 7: Review Form

Halaman akan menampilkan:
- Username validation ✓
- Email validation ✓
- Password strength indicator
- Password requirements checklist

### Langkah 8: Klik Button
Klik tombol biru: "Create Admin User"

### Langkah 9: Success Page 🎉

Akan menampilkan:
- ✅ Success checkmark
- Admin details (username & email)
- Link "Go to Login"
- Security reminder

---

## ⚠️ KEAMANAN - PENTING!

### 🔴 DELETE SCRIPT SETELAH SELESAI

**JANGAN LUPA menghapus file `create-admin.php` setelah membuat admin!**

#### Via Terminal:
```bash
rm create-admin.php
```

#### Via File Manager:
1. Login ke Hostinger cPanel
2. Buka File Manager
3. Navigate ke root directory
4. Cari file `create-admin.php`
5. Klik kanan → Delete
6. Confirm

#### Kenapa Harus Dihapus?
- 🚨 Siapa pun bisa akses file ini dan membuat admin baru
- 🚨 Security risk jika dibiarkan di server
- 🚨 Best practice adalah hapus setup scripts setelah digunakan

---

## 🔐 Password Requirements

### Apa itu Password Kuat?

❌ LEMAH:
- password (dictionary word)
- 12345678 (sequential numbers)
- admin123 (predictable)
- MyPassword (no numbers/special)

✅ KUAT:
- MyAdm!n@2024 (12 chars, mixed, special)
- Secure#Pass123! (12+ chars, all types)
- CultureBridge$2024 (relevant, secure)

### Panduan Password Strength:

```
LENGTH:
  8 chars   = 1 point
  12+ chars = 2 points

UPPERCASE (A-Z):        1 point
LOWERCASE (a-z):        1 point
NUMBERS (0-9):          1 point
SPECIAL (!@#$%^&*):     2 points

SCORE:
  0-1 = Very Weak ❌
  2   = Weak ⚠️
  3   = Fair 🟡
  4   = Good ✅
  5+  = Strong/Very Strong ✅✅
```

### Contoh Breakdown:

```
Password: MyAdm!n@2024
  • Length: 12 chars      → 2 points
  • Uppercase (My, Adm)   → 1 point
  • Lowercase (dm, n)     → 1 point
  • Numbers (2024)        → 1 point
  • Special (!@)          → 2 points
  ────────────────────────
  Total: 7 points → VERY STRONG ✅✅
```

---

## ✅ Verifikasi Berhasil

Cara tahu admin berhasil dibuat:

### 1. Check Database
```bash
mysql -u cbi_admin -p cbi_database
mysql> SELECT username, email FROM admin_users;
```

Output harus menampilkan username & email yang baru dibuat.

### 2. Try Login
Buka: https://yoursite.com/login

Masukkan:
- Username: `admin` (atau username yang Anda buat)
- Password: password yang Anda input

Jika berhasil → redirect ke admin panel ✅

### 3. Check Logs
```bash
cat logs/security.log
```

Harus ada entry:
```json
{"event_type":"ADMIN_USER_CREATED",...}
```

---

## 🐛 Troubleshooting

### ❌ Error: "Database connection failed"

**Penyebab:**
- Database credentials salah
- Database belum dibuat
- Database server tidak running

**Solusi:**

1. Check config.php:
```php
DB_HOST = localhost      ✓
DB_USER = cbi_admin      ✓
DB_PASS = password       ✓
DB_NAME = cbi_database   ✓
```

2. Verify database exists:
```bash
mysql -u root -p -e "SHOW DATABASES;"
```

3. Import schema jika belum:
```bash
mysql -u root -p cbi_database < db/schema.sql
```

4. Verify user permissions:
```bash
mysql -u root -p -e "SHOW GRANTS FOR 'cbi_admin'@'localhost';"
```

---

### ❌ Error: "Username already exists"

**Penyebab:**
- Username sudah terdaftar

**Solusi:**

1. Gunakan username berbeda, atau
2. Delete user lama dari database:
```bash
mysql -u cbi_admin -p cbi_database
mysql> DELETE FROM admin_users WHERE username='admin';
mysql> exit
```

---

### ❌ Error: "Email already registered"

**Penyebab:**
- Email sudah digunakan

**Solusi:**

1. Gunakan email berbeda, atau
2. Delete user lama:
```bash
mysql> DELETE FROM admin_users WHERE email='old@example.com';
```

---

### ❌ Form tidak load (Web Mode)

**Penyebab:**
- File permissions wrong
- PHP error

**Solusi:**

1. Set permissions:
```bash
chmod 644 create-admin.php
```

2. Check error log:
```bash
tail -f logs/error.log
```

3. Check browser console (F12)

---

### ❌ Script tidak berjalan (CLI Mode)

**Penyebab:**
- PHP tidak terinstall
- File permissions wrong

**Solusi:**

1. Verify PHP:
```bash
php --version
```

2. Fix permissions:
```bash
chmod +x create-admin.php
chmod 755 create-admin.php
```

3. Run dengan full path:
```bash
/usr/bin/php create-admin.php
```

---

## 📋 Next Steps Setelah Membuat Admin

### Immediate (Wajib):
```
[ ] 1. Delete create-admin.php:
       rm create-admin.php

[ ] 2. Login ke admin panel:
       https://yoursite.com/login

[ ] 3. Verify dashboard works
```

### Important (Penting):
```
[ ] 4. Review security settings:
       - Check SECURITY.md
       - Enable HTTPS
       - Configure firewall

[ ] 5. Create additional admins (optional):
       - Via admin panel
       - Or run script again

[ ] 6. Configure email settings:
       - Edit config.php SMTP settings
       - Test send email
```

### Recommended (Direkomendasikan):
```
[ ] 7. Set up backups:
       - Database backups
       - File backups

[ ] 8. Monitor security:
       - Check logs weekly
       - Review access logs
       - Enable alerting

[ ] 9. Update documentation:
       - Document admin accounts
       - Keep credentials safe
       - Store in password manager
```

---

## 🔒 Security Reminders

✅ **DO:**
- ✓ Use strong, unique passwords
- ✓ Delete create-admin.php after use
- ✓ Enable HTTPS in production
- ✓ Monitor security logs regularly
- ✓ Update admin password periodically
- ✓ Use password manager
- ✓ Enable 2FA (when available)

❌ **DON'T:**
- ✗ Leave create-admin.php on server
- ✗ Share admin credentials
- ✗ Use simple passwords (password123)
- ✗ Reuse passwords from other sites
- ✗ Tell anyone your admin password
- ✗ Store password in plain text
- ✗ Use HTTP in production

---

## 📞 Need Help?

### Helpful Resources:
- [CREATE_ADMIN_GUIDE.md](CREATE_ADMIN_GUIDE.md) - Detailed guide
- [SECURITY.md](SECURITY.md) - Security guide
- [SETUP.md](SETUP.md) - Setup guide

### Common Issues:
- Database errors → Check config.php
- Username exists → Use different username
- Script not found → Check file location
- Form not loading → Check permissions

---

**Status:** ✅ Ready to Create Admin
**Version:** 1.0
**Last Updated:** August 2026

Good luck! 🚀
