<?php
require_once __DIR__ . '/../config.php';

// Destroy session
session_destroy();

// Clear cookies
setcookie(SESSION_COOKIE_NAME, '', time() - 3600, SESSION_COOKIE_PATH);

// Redirect to login
header('Location: ' . LOGIN_URL);
exit;
?>
