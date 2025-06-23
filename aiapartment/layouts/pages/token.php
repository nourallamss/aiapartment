<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateToken() {
    // Generate a random token and store it in session
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>
