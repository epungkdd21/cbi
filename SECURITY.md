# 🔐 Security Documentation - CBI Admin System

## Overview

Sistem CBI Admin telah dilengkapi dengan keamanan berlapis untuk melindungi dari berbagai jenis serangan cyber. Dokumentasi ini menjelaskan semua fitur keamanan yang telah diimplementasikan.

---

## 🛡️ Fitur Keamanan yang Diimplementasikan

### 1. **Input Validation & Sanitization**

#### SQL Injection Protection
- Menggunakan prepared statements untuk semua query database
- Input divalidasi sebelum digunakan dalam query
- Fungsi: `prepareSQL()`, `sanitizeInput()`

```php
// Aman dari SQL Injection
$stmt = prepareSQL("SELECT * FROM admin_users WHERE email = ?", [$email]);
```

#### XSS (Cross-Site Scripting) Protection
- Semua output di-escape menggunakan `htmlspecialchars()`
- Content Security Policy (CSP) headers diterapkan
- Fungsi: `escapeHTML()`, `sanitizeInput()`

```php
// Output yang aman dari XSS
echo escapeHTML($userInput);
```

#### Command Injection Prevention
- Validasi ketat pada parameter yang bisa menyebabkan command injection
- Blocking pola berbahaya seperti `|`, `&`, `;`, etc.

### 2. **CSRF (Cross-Site Request Forgery) Protection**

- Token CSRF dihasilkan untuk setiap session
- Setiap form dan API request harus menyertakan token yang valid
- Token di-validate sebelum operasi dijalankan
- Fungsi: `generateCsrfToken()`, `verifyCsrfToken()`

```php
// Generate token
$token = generateCsrfToken();

// Verify token
if (!verifyCsrfToken($_POST['csrf_token'])) {
    die('CSRF token invalid');
}
```

### 3. **Password Security**

- Menggunakan bcrypt dengan cost 12 untuk hashing password
- Password strength validation (minimum 8 karakter)
- Mendukung kombinasi uppercase, lowercase, numbers, special chars
- Fungsi: `hashPassword()`, `verifyPassword()`, `getPasswordStrength()`

**Requirements Password:**
- Minimal 8 karakter
- Kombinasi uppercase, lowercase, numbers recommended
- Special characters meningkatkan strength

```php
// Hash password aman
$hash = hashPassword($password);

// Verify password
if (verifyPassword($inputPassword, $hash)) {
    // Login success
}
```

### 4. **Rate Limiting**

- Membatasi login attempts: 5 percobaan per 15 menit
- Membatasi API requests: customizable per endpoint
- IP yang violate rate limit akan di-track
- Fungsi: `checkRateLimit()`, `resetRateLimit()`

```php
// Limit 5 requests per 900 seconds (15 minutes)
if (!checkRateLimit($clientIP, 5, 900)) {
    throw new Exception('Too many requests');
}
```

### 5. **Session Security**

- Session cookies bersifat HttpOnly (tidak bisa diakses JavaScript)
- Session secure flag (HTTPS only)
- SameSite attribute untuk CSRF protection
- Session timeout: 30 menit
- Session ID regeneration setelah login
- Fungsi: `initializeSecureSession()`, `regenerateSessionID()`, `destroySessionSecurely()`

**Config:**
```php
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SECURE', true);
```

### 6. **File Upload Security**

- Validasi MIME type (tidak hanya extension)
- Check file size (limit default 5MB)
- Rename file dengan random name untuk mencegah traversal
- Tidak mengexecute PHP files di upload directory
- Allowed types: jpg, jpeg, png, gif, webp, pdf
- Fungsi: `validateFileUpload()`, `generateSecureFileName()`

```php
// Validate before upload
validateFileUpload($file, ['image/jpeg', 'image/png'], 5242880);

// Generate safe filename
$newName = generateSecureFileName($originalName);
```

### 7. **Security Headers**

Berikut security headers yang diterapkan via .htaccess:

| Header | Value | Purpose |
|--------|-------|---------|
| X-Content-Type-Options | nosniff | Prevent MIME type sniffing |
| X-XSS-Protection | 1; mode=block | Enable XSS protection |
| X-Frame-Options | SAMEORIGIN | Prevent clickjacking |
| Referrer-Policy | strict-origin-when-cross-origin | Control referrer info |
| Permissions-Policy | geolocation=(), microphone=(), camera=() | Disable dangerous APIs |
| Content-Security-Policy | Restricts resource loading | Prevent XSS & injection |
| Cache-Control | Proper cache strategies | Security & performance |

### 8. **Authentication & Authorization**

- Secure login process dengan rate limiting
- Session-based authentication
- CSRF token validation pada form
- Logout yang menghapus session completely
- Unauthorized access prevention

### 9. **IP Whitelisting/Blacklisting**

- Fitur untuk blacklist IP address yang suspicious
- Dapat mengatur permanent atau temporary blacklist
- Tracking alasan blacklist
- Removal functionality untuk emergency cases
- Fungsi: `isIPBlacklisted()`, `blacklistIP()`, `removeIPFromBlacklist()`

```php
// Check if IP is blacklisted
if (isIPBlacklisted($ip)) {
    die('Access denied');
}

// Add to blacklist
blacklistIP($ip, 'Multiple failed login attempts');

// Remove from blacklist
removeIPFromBlacklist($ip);
```

### 10. **Logging & Monitoring**

#### Security Event Logging
- Log semua security incidents
- Track login attempts (sukses & gagal)
- Monitor API requests
- Audit trail untuk administrative actions
- File logs: `logs/security.log`, `logs/audit.log`

**Tabel Database untuk Tracking:**
- `security_events` - Semua incident keamanan
- `login_attempts` - Track login activities
- `audit_logs` - Administrative changes
- `api_logs` - API request tracking
- `ip_blacklist` - Blacklisted IPs

```php
// Log security event
logSecurityEvent('LOGIN_FAILED', [
    'username' => $username,
    'ip' => getClientIP()
]);

// Audit log
auditLog('USER_CREATED', [
    'user_id' => $newUser['id'],
    'email' => $newUser['email']
]);
```

### 11. **API Protection (Middleware)**

Semua API endpoints dilindungi dengan:
- Suspicious activity detection
- Content-type validation
- Rate limiting
- Authentication check
- CSRF token validation

### 12. **Database Protection**

- Prepared statements untuk mencegah SQL injection
- Database user dengan limited privileges
- Table-level access control
- Foreign key constraints
- Indexes untuk performance & security

### 13. **Encryption**

- Sensitive data dapat diencrypt menggunakan AES-256-CBC
- Random IV untuk setiap encryption
- Fungsi: `encryptData()`, `decryptData()`

```php
// Encrypt sensitive data
$encrypted = encryptData($sensitiveData);

// Decrypt
$decrypted = decryptData($encryptedData);
```

---

## 📋 Best Practices untuk Development

### 1. **Selalu Validate Input**
```php
// ✓ GOOD
$email = sanitizeEmail($_POST['email']);
if (!validateInput($email, 'email')) {
    throw new Exception('Invalid email');
}

// ✗ BAD
$email = $_POST['email'];
```

### 2. **Selalu Escape Output**
```php
// ✓ GOOD
echo escapeHTML($userInput);

// ✗ BAD
echo $userInput;
```

### 3. **Gunakan Prepared Statements**
```php
// ✓ GOOD
$stmt = prepareSQL("SELECT * FROM users WHERE email = ?", [$email]);

// ✗ BAD
$query = "SELECT * FROM users WHERE email = '" . $email . "'";
```

### 4. **Verifikasi CSRF Token**
```php
// ✓ GOOD
if (!verifyCsrfToken($_POST['csrf_token'])) {
    throw new Exception('Invalid token');
}

// ✗ BAD
// Skip validation
```

### 5. **Hash Password dengan Bcrypt**
```php
// ✓ GOOD
$hash = hashPassword($password);

// ✗ BAD
$hash = md5($password);
$hash = sha1($password);
```

### 6. **Regenerate Session ID**
```php
// ✓ GOOD
regenerateSessionID(); // After login

// ✗ BAD
// Tidak regenerate session
```

### 7. **Set Security Headers**
```php
// ✓ GOOD
setSecurityHeaders();

// ✗ BAD
// No security headers
```

---

## 🚨 Monitoring & Response

### Security Event Monitoring

Admin dapat memonitor security events melalui dashboard:

1. **Security Dashboard**
   - Overview incidents terbaru
   - Failed login attempts
   - Suspicious IP addresses
   - API errors

2. **Audit Trail**
   - Semua admin actions tercatat
   - Timestamp dan IP address
   - Detail perubahan data

3. **Alert System**
   - Automatic alert untuk suspicious activities
   - Configurable severity levels
   - Email notifications

### Incident Response

Jika ada security breach:

1. **Immediate Actions**
   - Blacklist suspicious IPs
   - Reset compromised passwords
   - Revoke active sessions
   - Review audit logs

2. **Investigation**
   - Check security logs
   - Analyze attack patterns
   - Review access logs
   - Identify affected records

3. **Recovery**
   - Restore from backup jika diperlukan
   - Update vulnerable code
   - Apply security patches
   - Re-enable access for users

---

## 🔧 Configuration Guide

### Environment Variables (.env)

```env
# Database
DB_HOST=localhost
DB_USER=cbi_admin
DB_PASS=your_secure_password
DB_NAME=cbi_database

# Security
ENCRYPTION_KEY=your_strong_encryption_key
SESSION_TIMEOUT=1800

# HTTPS
HTTPS_ENABLED=true
SESSION_COOKIE_SECURE=true

# Rate Limiting
MAX_LOGIN_ATTEMPTS=5
LOGIN_ATTEMPT_WINDOW=900
```

### Security Headers Customization

Edit `.htaccess` untuk customize security headers sesuai kebutuhan:

```apache
# Content Security Policy
Header set Content-Security-Policy "default-src 'self'; ..."

# CORS (jika needed)
Header set Access-Control-Allow-Origin "https://trusted-domain.com"
```

---

## 📊 Security Checklist

- [ ] Database credentials di `.env` atau `config.php` (gitignore)
- [ ] HTTPS enabled di production
- [ ] Session timeout configured (1800s recommended)
- [ ] Logs directory protected
- [ ] Upload directory tidak bisa execute PHP
- [ ] SQL queries menggunakan prepared statements
- [ ] Semua user input di-sanitize
- [ ] Output di-escape
- [ ] CSRF tokens divalidasi
- [ ] Rate limiting enabled
- [ ] Security headers set
- [ ] File permissions correct (755 for dirs, 644 for files)
- [ ] Regular backups scheduled
- [ ] Security logs monitored
- [ ] Two-factor auth considered untuk future

---

## 🔒 Production Deployment Checklist

- [ ] Disable debug mode (`display_errors = 0`)
- [ ] Enable error logging (`log_errors = 1`)
- [ ] HTTPS certificate valid
- [ ] `.htaccess` deployed
- [ ] `config.php` permissions 600 (readable by PHP only)
- [ ] Logs directory writable by PHP user
- [ ] Uploads directory writable by PHP user
- [ ] Regular security updates scheduled
- [ ] Backup strategy in place
- [ ] Monitoring & alerting configured
- [ ] Database backed up before deployment
- [ ] Security logs reviewed regularly
- [ ] Firewall rules configured
- [ ] DDoS protection enabled

---

## 📞 Support & Reporting

Jika menemukan security vulnerability:

1. **Jangan** publikasikan secara publik
2. Email ke: `security@culturebridgeindonesia.my.id`
3. Sertakan deskripsi detail & POC (Proof of Concept)
4. Tim akan merespons dalam 24 jam

---

## 🔄 Regular Security Tasks

**Daily:**
- Monitor security dashboard
- Check failed login attempts
- Review alert notifications

**Weekly:**
- Review audit logs
- Check IP blacklist
- Verify backup integrity

**Monthly:**
- Security update check
- Dependency updates
- Access control review
- Log rotation

**Quarterly:**
- Security audit
- Penetration testing
- Policy review
- Staff training

---

**Last Updated:** August 2026
**Version:** 1.0
**Status:** Production Ready
