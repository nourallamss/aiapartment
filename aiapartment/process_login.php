<?php
include __DIR__ . '/includes/csrf_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!isValidCsrfToken($token)) {
        die("❌ CSRF token is invalid or expired.");
    }

    // Destroy token after use
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

    // Fake users for demo (in real app, use DB)
    $users = [
        'admin' => password_hash('adminpass', PASSWORD_DEFAULT),
        'user'  => password_hash('userpass', PASSWORD_DEFAULT)
    ];

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!isset($users[$username])) {
        die("❌ User not found.");
    }

    if (!password_verify($password, $users[$username])) {
        die("❌ Incorrect password.");
    }

    // Successful login
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = ($username === 'admin'); // simple admin flag

    header("Location: dashboard.php");
    exit;
} else {
    echo "⚠️ Invalid request.";
}
