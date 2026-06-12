<?php
/**
 * session.php — Centralized session management for SIGereja
 * This file configures secure session parameters and prevents fixation.
 */

// Define session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    // 'domain' => '', 
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // True if HTTPS
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax' // Protect against cross-site request forgery
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Safely regenerates the session ID and CSRF token.
 * Call this on privilege escalation (login) to prevent session fixation.
 */
function regenerate_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        // Regenerate CSRF token to prevent token reuse across privilege boundaries
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
