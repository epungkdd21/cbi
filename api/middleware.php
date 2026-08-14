<?php
/**
 * Security Middleware
 * Applied to all API requests for comprehensive protection
 */

require_once __DIR__ . '/security.php';

// Initialize security
setSecurityHeaders();
initializeSecureSession();

// Check if IP is blacklisted
$clientIP = getClientIP();
if (isIPBlacklisted($clientIP)) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Access denied']));
}

// Detect and log suspicious patterns
detectSuspiciousActivity();

/**
 * Detect suspicious activity patterns
 */
function detectSuspiciousActivity() {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestURI = $_SERVER['REQUEST_URI'] ?? '';
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $clientIP = getClientIP();
    
    // Check for SQL injection patterns
    $sqlPatterns = [
        '/union.*select/i',
        '/select.*from/i',
        '/insert.*into/i',
        '/delete.*from/i',
        '/drop.*table/i',
        '/update.*set/i',
        '/execute.*\(/i',
        '/__SLEEP/i',
        '/benchmark.*\(/i',
        '/waitfor.*delay/i',
    ];
    
    foreach ($sqlPatterns as $pattern) {
        if (preg_match($pattern, $requestURI . $queryString)) {
            logSecurityEvent('SQL_INJECTION_ATTEMPT', [
                'ip' => $clientIP,
                'uri' => $requestURI,
                'query' => $queryString
            ]);
            http_response_code(403);
            die(json_encode(['status' => 'error', 'message' => 'Invalid request']));
        }
    }
    
    // Check for XSS attempts
    $xssPatterns = [
        '/<script[^>]*>/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
    ];
    
    foreach ($xssPatterns as $pattern) {
        if (preg_match($pattern, $requestURI . $queryString)) {
            logSecurityEvent('XSS_INJECTION_ATTEMPT', [
                'ip' => $clientIP,
                'uri' => $requestURI,
                'query' => $queryString
            ]);
            http_response_code(403);
            die(json_encode(['status' => 'error', 'message' => 'Invalid request']));
        }
    }
    
    // Check for path traversal attempts
    if (preg_match('/\.\.\/|\.\.\\\\/', $requestURI)) {
        logSecurityEvent('PATH_TRAVERSAL_ATTEMPT', [
            'ip' => $clientIP,
            'uri' => $requestURI
        ]);
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Invalid request']));
    }
    
    // Check for command injection attempts
    if (preg_match('/[;&|`$()]/i', $queryString)) {
        if (preg_match('/[|&;`$()]/i', basename($requestURI))) {
            logSecurityEvent('COMMAND_INJECTION_ATTEMPT', [
                'ip' => $clientIP,
                'uri' => $requestURI,
                'query' => $queryString
            ]);
            http_response_code(403);
            die(json_encode(['status' => 'error', 'message' => 'Invalid request']));
        }
    }
}

/**
 * Validate API request
 */
function validateAPIRequest($requiredFields = [], $requireAuth = true) {
    // Check content type
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (!empty($_SERVER['CONTENT_LENGTH']) && strpos($contentType, 'application/json') === false) {
        logSecurityEvent('INVALID_CONTENT_TYPE', [
            'received' => $contentType,
            'ip' => getClientIP()
        ]);
        throw new Exception('Invalid content type. Expected application/json');
    }
    
    // Check rate limit
    $clientIP = getClientIP();
    if (!checkRateLimit($clientIP)) {
        logSecurityEvent('RATE_LIMIT_EXCEEDED', [
            'ip' => $clientIP,
            'uri' => $_SERVER['REQUEST_URI']
        ]);
        throw new Exception('Too many requests. Please try again later.');
    }
    
    // Validate required fields
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];
    
    foreach ($requiredFields as $field) {
        if (!isset($inputData[$field]) || empty($inputData[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check authentication if required
    if ($requireAuth && !isLoggedIn()) {
        http_response_code(401);
        throw new Exception('Unauthorized access');
    }
    
    return $inputData;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

?>
