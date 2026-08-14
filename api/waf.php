<?php
/**
 * Web Application Firewall (WAF) Rules
 * Additional layer of protection against common web attacks
 */

require_once __DIR__ . '/security.php';

class WAF {
    private static $blockRules = [];
    private static $allowRules = [];
    private static $violations = [];
    
    /**
     * Initialize WAF
     */
    public static function initialize() {
        // Check if request should be blocked
        self::checkIncomingRequest();
    }
    
    /**
     * Check incoming request
     */
    private static function checkIncomingRequest() {
        $clientIP = getClientIP();
        
        // Check blacklist
        if (isIPBlacklisted($clientIP)) {
            self::blockRequest('IP_BLACKLISTED', [
                'ip' => $clientIP
            ]);
        }
        
        // Check request method
        self::checkRequestMethod();
        
        // Check request size
        self::checkRequestSize();
        
        // Check for malicious patterns
        self::checkMaliciousPatterns();
    }
    
    /**
     * Check HTTP request method
     */
    private static function checkRequestMethod() {
        $method = $_SERVER['REQUEST_METHOD'];
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS', 'PATCH'];
        
        if (!in_array($method, $allowedMethods)) {
            self::blockRequest('INVALID_HTTP_METHOD', [
                'method' => $method
            ]);
        }
        
        // Block dangerous methods
        $blockedMethods = ['TRACE', 'TRACK', 'CONNECT'];
        if (in_array($method, $blockedMethods)) {
            self::blockRequest('DANGEROUS_HTTP_METHOD', [
                'method' => $method
            ]);
        }
    }
    
    /**
     * Check request size
     */
    private static function checkRequestSize() {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        
        if ($contentLength > $maxSize) {
            self::blockRequest('REQUEST_SIZE_EXCEEDED', [
                'size' => $contentLength,
                'max' => $maxSize
            ]);
        }
    }
    
    /**
     * Check for malicious patterns
     */
    private static function checkMaliciousPatterns() {
        $targetData = [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'query' => $_SERVER['QUERY_STRING'] ?? '',
            'headers' => getallheaders() ?? []
        ];
        
        // SQL Injection patterns
        $sqlPatterns = [
            "/union\s+select/i",
            "/select\s+from/i",
            "/insert\s+into/i",
            "/delete\s+from/i",
            "/drop\s+(table|database)/i",
            "/update\s+\w+\s+set/i",
            "/exec\s*\(/i",
            "/execute\s*\(/i",
            "/(sleep|benchmark)\s*\(/i",
            "/waitfor\s+delay/i",
            "/cast\s*\(/i",
            "/convert\s*\(/i",
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (self::searchInData($targetData, $pattern)) {
                self::blockRequest('SQL_INJECTION_PATTERN_DETECTED', [
                    'pattern' => $pattern
                ]);
            }
        }
        
        // XSS patterns
        $xssPatterns = [
            "/<script[^>]*>/i",
            "/javascript:/i",
            "/on\w+\s*=/i",
            "/<iframe[^>]*>/i",
            "/<object[^>]*>/i",
            "/<embed[^>]*>/i",
            "/<img[^>]+on/i",
            "/<svg[^>]+on/i",
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (self::searchInData($targetData, $pattern)) {
                self::blockRequest('XSS_PATTERN_DETECTED', [
                    'pattern' => $pattern
                ]);
            }
        }
        
        // Path Traversal patterns
        $pathPatterns = [
            "/\.\.\//",
            "/\.\.\\/",
            "/%2e%2e/i",
            "/\.\.%2f/i",
            "/%2e%2e%2f/i",
        ];
        
        foreach ($pathPatterns as $pattern) {
            if (self::searchInData($targetData, $pattern)) {
                self::blockRequest('PATH_TRAVERSAL_DETECTED', [
                    'pattern' => $pattern
                ]);
            }
        }
        
        // Command Injection patterns
        $cmdPatterns = [
            "/[;&|`$()]/",
        ];
        
        foreach ($cmdPatterns as $pattern) {
            if (preg_match($pattern, $_SERVER['QUERY_STRING'] ?? '')) {
                if (preg_match("/[|&;`$()]/i", basename($_SERVER['REQUEST_URI'] ?? ''))) {
                    self::blockRequest('COMMAND_INJECTION_PATTERN_DETECTED', [
                        'pattern' => $pattern
                    ]);
                }
            }
        }
        
        // XXE (XML External Entity) patterns
        $xxePatterns = [
            "/<!ENTITY/i",
            "/SYSTEM\s+['\"]?file:/i",
        ];
        
        foreach ($xxePatterns as $pattern) {
            if (self::searchInData($targetData, $pattern)) {
                self::blockRequest('XXE_PATTERN_DETECTED', [
                    'pattern' => $pattern
                ]);
            }
        }
    }
    
    /**
     * Search pattern in data recursively
     */
    private static function searchInData($data, $pattern) {
        if (is_string($data)) {
            return preg_match($pattern, $data) === 1;
        } elseif (is_array($data)) {
            foreach ($data as $value) {
                if (self::searchInData($value, $pattern)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Block malicious request
     */
    private static function blockRequest($reason, $details = []) {
        logSecurityEvent('WAF_BLOCK_' . $reason, array_merge([
            'ip' => getClientIP(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ], $details));
        
        http_response_code(403);
        die(json_encode([
            'status' => 'error',
            'message' => 'Request blocked by security firewall'
        ]));
    }
    
    /**
     * Validate User-Agent header
     */
    public static function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Block known malicious user agents
        $blockedAgents = [
            '/sqlmap/i',
            '/nikto/i',
            '/nessus/i',
            '/nmap/i',
            '/masscan/i',
            '/openvas/i',
            '/qualys/i',
            '/metasploit/i',
        ];
        
        foreach ($blockedAgents as $agent) {
            if (preg_match($agent, $userAgent)) {
                self::blockRequest('SUSPICIOUS_USER_AGENT', [
                    'user_agent' => $userAgent
                ]);
            }
        }
    }
    
    /**
     * Validate HTTP headers
     */
    public static function validateHeaders() {
        $headers = getallheaders();
        
        // Check for suspicious headers
        $suspiciousHeaders = [
            'x-forwarded-for' => true, // Only from trusted proxies
            'x-original-url' => false,
            'x-http-method-override' => false,
            'x-rewrite-url' => false,
        ];
        
        foreach ($suspiciousHeaders as $header => $allowed) {
            if (!$allowed && isset($headers[$header])) {
                self::blockRequest('SUSPICIOUS_HEADER', [
                    'header' => $header
                ]);
            }
        }
    }
    
    /**
     * Check for DDoS patterns
     */
    public static function checkDDoSPattern() {
        $clientIP = getClientIP();
        $cacheKey = 'ddos_check_' . md5($clientIP);
        
        // Check if more than 100 requests in 60 seconds
        if (!checkRateLimit($clientIP, 100, 60)) {
            self::blockRequest('POSSIBLE_DDOS_ATTACK', [
                'ip' => $clientIP
            ]);
        }
    }
}

// Initialize WAF on page load
WAF::initialize();
WAF::validateUserAgent();
WAF::validateHeaders();

?>
