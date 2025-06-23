<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a secure CSRF token and store it in session.
 */
function generateCsrfToken(): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

/**
 * Validate the CSRF token.
 */
function isValidCsrfToken($token): bool {
    if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
        return false;
    }

    // Token must match
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    // Token must not be expired (e.g., 10 minutes)
    if ($_SESSION['csrf_token_time'] + 600 < time()) {
        return false;
    }

    return true;
}
?>

<?php
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCsrfToken')) {
    function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
