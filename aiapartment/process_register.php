<?php
include __DIR__ . '/includes/csrf_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!isValidCsrfToken($token)) {
        die("❌ Security check failed: Invalid or expired CSRF token.");
    }

    // Clear used token to prevent reuse
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

    // Process form securely
    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    echo "✅ User registered successfully: " . $username;
} else {
    echo "⚠️ Invalid request method.";
}
