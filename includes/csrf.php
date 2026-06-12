<?php
/**
 * csrf.php — Lightweight CSRF protection helpers.
 * Included automatically via admin_header.php and header.php.
 */

/**
 * Return (and generate if missing) a per-session CSRF token.
 */
function csrf_token(): string {
    require_once __DIR__ . '/session.php';
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden <input> field containing the CSRF token.
 * Drop <?= csrf_field() ?> inside every <form method="POST">.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Verify the CSRF token submitted in a POST request.
 * Call at the top of every POST handler. Aborts with 403 on failure.
 *
 * @param string $redirect  URL to redirect to on failure (empty = hard die)
 */
function csrf_verify(string $redirect = ''): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if (!$expected || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        if ($redirect) {
            $_SESSION['error_msg'] = 'Permintaan tidak valid (CSRF). Silakan coba lagi.';
            header("Location: $redirect");
            exit;
        }
        die('403 Forbidden — CSRF token tidak valid.');
    }
}
?>
