<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    jsonResponse('error', 'Session expired', ['redirect' => LOGIN_URL]);
}

// Refresh session timeout
$_SESSION['last_activity'] = time();

jsonResponse('success', 'Session valid', [
    'admin_id' => $_SESSION['admin_id'],
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
]);
?>
