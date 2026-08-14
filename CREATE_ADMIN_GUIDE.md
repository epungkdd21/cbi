# 🔑 Create Admin User Guide

Panduan lengkap untuk membuat user admin pertama kali di CBI Admin System.

---

## 📋 Requirements

- ✅ Database sudah di-import (`db/schema.sql`)
- ✅ `config.php` sudah di-konfigurasi dengan database credentials
- ✅ `security.php` dan `create-admin.php` tersedia

---

## 🚀 Method 1: CLI Mode (Recommended)

### Step 1: Jalankan Script dari Terminal
```bash
php create-admin.php
```

### Step 2: Ikuti Prompt Interaktif

Script akan meminta:

1. **Username** (3-50 characters)
   - Contoh: `admin`, `superadmin`, `main_admin`
   - Hanya gunakan: letters, numbers, dash (-), underscore (_)

2. **Email** 
   - Contoh: `admin@culturebridgeindonesia.my.id`

3. **Password** (minimum 8 characters)
   - Script akan menyembunyikan input untuk keamanan
   - Recommended: Gunakan kombinasi uppercase, lowercase, numbers, special chars

4. **Confirm Password**
   - Script akan mengecek kesamaan password

### Step 3: Verifikasi
Script akan menampilkan:
- Username yang akan dibuat
- Email yang akan digunakan  
- Password strength

### Step 4: Konfirmasi
Ketik `yes` untuk melanjutkan atau pilihan lain untuk cancel.

### Contoh Output:
```
╔════════════════════════════════════════════════════════════════╗
║     🔑 CBI Admin System - Create First Admin User              ║
╚════════════════════════════════════════════════════════════════╝

Admin user(s) already exist in the database!
Do you want to create another admin account? (yes/no): yes

📝 Enter admin username (3-50 characters): admin
📧 Enter admin email: admin@example.com
🔐 Enter admin password (minimum 8 characters): ••••••••••••••••

📊 Password Strength: Strong

📋 Verifying information:
   • Username: admin
   • Email: admin@example.com
   • Password Strength: Strong

✓ Create this admin account? (yes/no): yes

╔════════════════════════════════════════════════════════════════╗
║                    ✅ SUCCESS!                                 ║
╚════════════════════════════════════════════════════════════════╝

✓ Admin user created successfully!

📧 Username: admin
📧 Email: admin@example.com

🔗 You can now login at: https://yoursite.com/login

⚠️  SECURITY REMINDER:
   • Delete this script (create-admin.php) from the server!
   • Use a strong, unique password
   • Enable HTTPS on production
   • Monitor security logs regularly
```

---

## 🌐 Method 2: Web Browser Mode

### Step 1: Buka di Browser

Navigasikan ke:
```
https://yoursite.com/create-admin.php
```

### Step 2: Isi Form

Form akan menampilkan:
- **Username** input field
- **Email** input field
- **Password** input field (dengan password strength meter)
- **Confirm Password** input field
- CSRF token (automatic)

### Step 3: Password Strength Indicator

Saat Anda mengetik password, strength indicator akan menampilkan:
- Red (Very Weak)
- Orange (Weak)
- Yellow (Fair)
- Blue (Good)
- Green (Strong)
- Dark Green (Very Strong)

### Step 4: Submit Form

Klik tombol "Create Admin User"

### Step 5: Success Page

Jika berhasil, Anda akan melihat:
- ✅ Success message
- Admin details yang dibuat
- Link ke login page
- Security reminder untuk delete file ini

---

## ⚠️ Security Warnings

### 🔴 CRITICAL: Delete Script After Use

**Setelah membuat admin user pertama, segera delete file `create-admin.php`:**

```bash
# Delete via terminal
rm create-admin.php

# Atau dari web panel:
# 1. Login ke file manager
# 2. Navigate ke root directory
# 3. Delete create-admin.php
# 4. Confirm deletion
```

### Why?
- File ini memungkinkan siapa saja untuk membuat admin user baru
- Bisa menjadi security risk jika dibiarkan di server
- Best practice adalah menghapus setup scripts setelah digunakan

### Protect While Using (Optional)

Jika perlu menjaga file selama proses setup:

**Option 1: Add password protection via .htaccess**
```apache
<Files "create-admin.php">
    AuthType Basic
    AuthName "Admin Only"
    AuthUserFile /path/to/.htpasswd
    Require valid-user
</Files>
```

**Option 2: Limit by IP**
```apache
<Files "create-admin.php">
    Order allow,deny
    Allow from 192.168.1.100
    Deny from all
</Files>
```

**Option 3: Rename file**
```bash
mv create-admin.php setup-admin-xyz123.php
```

---

## 🔐 Password Requirements

### Minimum Requirements
- ✅ Minimum 8 characters
- ✅ At least 1 letter

### Recommended
- 🔒 Mix of uppercase dan lowercase
- 🔒 Include numbers (0-9)
- 🔒 Include special characters (!@#$%^&*)
- 🔒 Avoid common words
- 🔒 Avoid personal information

### Example Strong Passwords
```
Admin@2024#Secure
CultureBridge$2024
Secure!Password#123
MyStr0ng@Password!
```

### Password Strength Scoring
```
Very Weak (0-1 points)
  • Short password
  • Only letters or only numbers

Weak (2 points)
  • 8+ characters
  • One type of character

Fair (3 points)
  • Good length
  • Mixed case

Good (4 points)
  • 12+ characters
  • Mixed case + numbers

Strong (5+ points)
  • Long password
  • All character types
  • Special characters
```

---

## ✅ Validation Rules

### Username
```
✓ Valid:     admin, admin_user, admin-user, admin123
✗ Invalid:   ad (too short), admin@user, admin.user, admin user
```

### Email
```
✓ Valid:     admin@example.com, user@domain.co.id
✗ Invalid:   admin@, @example.com, admin.example.com
```

### Password
```
✓ Valid:     MyP@ss123, Secure!2024, Admin#Password
✗ Invalid:   password, 12345678, abcdefgh
```

---

## 🐛 Troubleshooting

### Error: "Database connection failed"
**Solution:**
1. Check database credentials in `config.php`
2. Verify database is created
3. Verify database user has permissions
4. Check database server is running

```bash
# Test database connection
mysql -h localhost -u cbi_admin -p cbi_database
```

### Error: "Username already exists"
**Solution:**
1. Use different username
2. Or delete existing user from database:
```sql
DELETE FROM admin_users WHERE username = 'admin';
```

### Error: "Email already registered"
**Solution:**
1. Use different email
2. Or delete existing user from database:
```sql
DELETE FROM admin_users WHERE email = 'admin@example.com';
```

### Error: "Database table not found"
**Solution:**
1. Import database schema:
```bash
mysql -u root -p cbi_database < db/schema.sql
```

### Script Not Running (CLI Mode)
**Solution:**
```bash
# Check PHP is installed
php --version

# Check file permissions
chmod +x create-admin.php
chmod 755 create-admin.php

# Run with explicit PHP
/usr/bin/php create-admin.php
```

### Form Not Loading (Web Mode)
**Solution:**
1. Check file permissions:
```bash
chmod 644 create-admin.php
```

2. Check browser console for errors
3. Check server error logs:
```bash
tail -f /var/log/apache2/error.log
```

---

## 📝 Next Steps After Creating Admin

### 1. Login to Admin Panel
```
URL: https://yoursite.com/login
Username: (username you created)
Password: (password you created)
```

### 2. Change Admin Settings
- Update profile
- Change password (optional)
- Review security logs

### 3. Create Additional Admins (Optional)
- Use admin panel to create more admins
- Or run script again: `php create-admin.php`

### 4. Delete Create Script
```bash
rm create-admin.php
```

### 5. Review Security
- Check [SECURITY.md](SECURITY.md)
- Configure email settings
- Set up backups
- Monitor logs

---

## 🆘 Getting Help

### Common Issues

**Can't connect to database:**
- Verify credentials in `config.php`
- Check MySQL is running
- Check user permissions

**Script not found:**
- Make sure file is in root directory
- Check file naming (case-sensitive on Linux)

**Form says "Invalid token":**
- Session issue - try again
- Clear browser cookies
- Check PHP session settings

**Password not accepted at login:**
- Re-read password carefully (case-sensitive)
- Try resetting password via "Lupa Password?" link
- Check caps lock

---

## 📚 Related Documentation

- [SECURITY.md](../SECURITY.md) - Security guide
- [SECURITY_ARCHITECTURE.md](../SECURITY_ARCHITECTURE.md) - Security architecture
- [SETUP.md](../SETUP.md) - Initial setup guide
- [README.md](../README.md) - Project overview

---

**Version:** 1.0  
**Last Updated:** August 2026  
**Status:** Production Ready
