<?php
/**
 * Security Module for CBI Admin System
 * Comprehensive protection against common attacks
 */

// Prevent direct access
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * INPUT VALIDATION & SANITIZATION
 */

/**
 * Sanitize string input (XSS protection)
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

/**
 * Sanitize email
 */
function sanitizeEmail($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    return $email;
}

/**
 * Sanitize URL
 */
function sanitizeUrl($url) {
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return $url;
}

/**
 * Validate input against specific patterns
 */
function validateInput($input, $type = 'text') {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
        
        case 'phone':
            return preg_match('/^[0-9\-\+\(\)\s]+$/', $input) && strlen($input) >= 10;
        
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) !== false;
        
        case 'numeric':
            return is_numeric($input);
        
        case 'alphanumeric':
            return preg_match('/^[a-zA-Z0-9_-]+$/', $input);
        
        case 'username':
            return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $input);
        
        default:
            return !empty($input);
    }
}

/**
 * CSRF TOKEN MANAGEMENT
 */

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * PASSWORD SECURITY
 */

/**
 * Hash password with bcrypt
 */
function hashPassword($password) {
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    return password_hash($password, PASSWORD_BCRYPT, [
        'cost' => 12,
        'algorithm' => PASSWORD_BCRYPT
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check password strength
 */
function getPasswordStrength($password) {
    $strength = 0;
    
    // Length
    if (strlen($password) >= 12) $strength += 2;
    elseif (strlen($password) >= 8) $strength += 1;
    
    // Uppercase
    if (preg_match('/[A-Z]/', $password)) $strength += 1;
    
    // Lowercase
    if (preg_match('/[a-z]/', $password)) $strength += 1;
    
    // Numbers
    if (preg_match('/[0-9]/', $password)) $strength += 1;
    
    // Special characters
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength += 2;
    
    return min(5, $strength);
}

/**
 * RATE LIMITING
 */

/**
 * Check rate limit
 */
function checkRateLimit($identifier, $limit = 5, $window = 900) {
    $cacheFile = __DIR__ . '/logs/ratelimit_' . md5($identifier) . '.tmp';
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        $timeDiff = time() - $data['timestamp'];
        
        if ($timeDiff < $window) {
            if ($data['attempts'] >= $limit) {
                return false; // Rate limit exceeded
            }
            $data['attempts']++;
        } else {
            $data['attempts'] = 1;
            $data['timestamp'] = time();
        }
    } else {
        $data = [
            'attempts' => 1,
            'timestamp' => time()
        ];
    }
    
    file_put_contents($cacheFile, json_encode($data));
    return true;
}

/**
 * Reset rate limit
 */
function resetRateLimit($identifier) {
    $cacheFile = __DIR__ . '/logs/ratelimit_' . md5($identifier) . '.tmp';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * SQL INJECTION PROTECTION
 */

/**
 * Prepare statement helper
 */
function prepareSQL($query, $params) {
    global $mysqli;
    
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        logSecurityEvent('SQL_PREPARE_ERROR', [
            'query' => $query,
            'error' => $mysqli->error
        ]);
        throw new Exception('Database error');
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt;
}

/**
 * XSS PROTECTION
 */

/**
 * Escape output for HTML
 */
function escapeHTML($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for JavaScript
 */
function escapeJS($string) {
    return addslashes($string);
}

/**
 * Escape output for URL
 */
function escapeURL($string) {
    return urlencode($string);
}

/**
 * SECURITY LOGGING & MONITORING
 */

/**
 * Log security events
 */
function logSecurityEvent($eventType, $details = []) {
    $logFile = __DIR__ . '/logs/security.log';
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event_type' => $eventType,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'url' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        'details' => $details,
        'user_id' => $_SESSION['admin_id'] ?? null
    ];
    
    file_put_contents(
        $logFile,
        json_encode($logEntry) . PHP_EOL,
        FILE_APPEND
    );
}

/**
 * Log login attempts
 */
function logLoginAttempt($username, $success = false) {
    $details = [
        'username' => $username,
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    logSecurityEvent($success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED', $details);
}

/**
 * ENCRYPTION/DECRYPTION
 */

/**
 * Encrypt sensitive data
 */
function encryptData($data) {
    $key = hash('sha256', getenv('ENCRYPTION_KEY') ?: 'default-key', true);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function decryptData($encryptedData) {
    $key = hash('sha256', getenv('ENCRYPTION_KEY') ?: 'default-key', true);
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, openssl_cipher_iv_length('AES-256-CBC'));
    $encrypted = substr($data, openssl_cipher_iv_length('AES-256-CBC'));
    
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * IP WHITELIST/BLACKLIST
 */

/**
 * Check if IP is blacklisted
 */
function isIPBlacklisted($ip) {
    $blacklistFile = __DIR__ . '/config/ip_blacklist.json';
    
    if (!file_exists($blacklistFile)) {
        return false;
    }
    
    $blacklist = json_decode(file_get_contents($blacklistFile), true);
    
    return in_array($ip, $blacklist['ips'] ?? []);
}

/**
 * Add IP to blacklist
 */
function blacklistIP($ip, $reason = '') {
    $blacklistFile = __DIR__ . '/config/ip_blacklist.json';
    
    if (!file_exists(__DIR__ . '/config')) {
        mkdir(__DIR__ . '/config', 0755, true);
    }
    
    $blacklist = file_exists($blacklistFile) 
        ? json_decode(file_get_contents($blacklistFile), true)
        : ['ips' => [], 'reasons' => []];
    
    if (!in_array($ip, $blacklist['ips'])) {
        $blacklist['ips'][] = $ip;
        $blacklist['reasons'][$ip] = $reason;
        
        file_put_contents($blacklistFile, json_encode($blacklist, JSON_PRETTY_PRINT));
        logSecurityEvent('IP_BLACKLISTED', ['ip' => $ip, 'reason' => $reason]);
    }
}

/**
 * Remove IP from blacklist
 */
function removeIPFromBlacklist($ip) {
    $blacklistFile = __DIR__ . '/config/ip_blacklist.json';
    
    if (!file_exists($blacklistFile)) {
        return;
    }
    
    $blacklist = json_decode(file_get_contents($blacklistFile), true);
    
    if (($key = array_search($ip, $blacklist['ips'])) !== false) {
        unset($blacklist['ips'][$key]);
        unset($blacklist['reasons'][$ip]);
        
        file_put_contents($blacklistFile, json_encode($blacklist, JSON_PRETTY_PRINT));
        logSecurityEvent('IP_WHITELISTED', ['ip' => $ip]);
    }
}

/**
 * SESSION SECURITY
 */

/**
 * Initialize secure session
 */
function initializeSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', !empty($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', 1800);
        
        session_start();
    }
}

/**
 * Regenerate session ID
 */
function regenerateSessionID() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Destroy session securely
 */
function destroySessionSecurely() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies') === '1') {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
}

/**
 * FILE UPLOAD SECURITY
 */

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds limit');
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('File type not allowed');
    }
    
    // Check file extension
    $fileName = $file['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedExtensions = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'application/pdf' => ['pdf'],
    ];
    
    if (!isset($allowedExtensions[$mimeType]) || 
        !in_array($fileExt, $allowedExtensions[$mimeType])) {
        throw new Exception('Invalid file extension');
    }
    
    return true;
}

/**
 * Generate secure filename
 */
function generateSecureFileName($originalFileName) {
    $ext = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    
    return $newName;
}

/**
 * MISCELLANEOUS SECURITY
 */

/**
 * Get client IP address (handles proxies)
 */
function getClientIP() {
    $ipKeys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP');
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Security headers
 */
function setSecurityHeaders() {
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent XSS attacks
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Feature Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com;");
}

/**
 * Audit logging
 */
function auditLog($action, $details = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_id' => $_SESSION['admin_id'] ?? null,
        'ip_address' => getClientIP(),
        'details' => $details
    ];
    
    $logFile = __DIR__ . '/logs/audit.log';
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}

?>
