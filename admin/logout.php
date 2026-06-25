<?php
/**
 * PixelNova Admin — Logout
 */
session_start();

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']
    );
}

// Destroy the session
session_destroy();

header('Location: login.php');
exit;
