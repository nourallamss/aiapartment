<?php
session_start();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require 'db.php';

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simple rate limiting using session (upgrade to database later)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

// Reset attempts after 15 minutes
if (time() - $_SESSION['last_attempt_time'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

$errors = [];
$username = "";

// Handle "Remember Me" cookie (basic version)
if (isset($_COOKIE['remember_user']) && !isset($_SESSION['username'])) {
    $remembered_user = $_COOKIE['remember_user'];
    
    // Verify user still exists and is active
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $remembered_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        session_regenerate_id(true);
        header("Location: index.php");
        exit;
    } else {
        // Invalid user, remove cookie
        setcookie('remember_user', '', time() - 3600, '/', '', false, true);
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    $submitted_token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    
    if (empty($submitted_token) || !hash_equals($session_token, $submitted_token)) {
        $errors[] = "Security token mismatch. Please refresh the page and try again.";
    } else {
        $username = trim($_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';
        $remember = isset($_POST["remember"]);
        
        // Input validation
        if (empty($username) || empty($password)) {
            $errors[] = "All fields are required.";
        } elseif (strlen($username) > 50) {
            $errors[] = "Username is too long.";
        } elseif (strlen($password) > 255) {
            $errors[] = "Password is too long.";
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = "Username contains invalid characters.";
        } elseif ($_SESSION['login_attempts'] >= 5) {
            $errors[] = "Too many login attempts. Please wait 15 minutes before trying again.";
        } else {
            // Attempt login
            $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $email, $hashed_password);
                $stmt->fetch();
                
                if (password_verify($password, $hashed_password)) {
                    // Successful login
                    $_SESSION["user_id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;
                    $_SESSION["login_time"] = time();
                    
                    // Reset login attempts
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['last_attempt_time'] = 0;
                    
                    // Regenerate session ID and CSRF token
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    // Handle "Remember Me" with secure settings
                    if ($remember) {
                        $cookie_options = [
                            'expires' => time() + (30 * 24 * 60 * 60), // 30 days
                            'path' => '/',
                            'domain' => '',
                            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ];
                        setcookie('remember_user', $username, $cookie_options);
                    }
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $errors[] = "Invalid username or password.";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                }
            } else {
                $errors[] = "Invalid username or password.";
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
            }
            
            $stmt->close();
        }
    }
    
    // Regenerate CSRF token after failed attempt
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | AI Apartment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow" style="width: 100%; max-width: 420px;">
        <h2 class="text-center mb-3">Welcome Back</h2>
        <p class="text-center text-muted mb-4">Please log in to your account</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['login_attempts'] >= 3): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Multiple failed attempts detected. <?= 5 - $_SESSION['login_attempts'] ?> attempts remaining.
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="on" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" id="username" class="form-control" 
                           required maxlength="50" autocomplete="username"
                           pattern="^[a-zA-Z0-9_.-]+$"
                           value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-text">Only letters, numbers, dots, hyphens, and underscores allowed.</div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="password" class="form-control" 
                           required maxlength="255" autocomplete="current-password">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Remember Me (30 days)</label>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary" 
                        <?= $_SESSION['login_attempts'] >= 5 ? 'disabled' : '' ?>>
                    <?= $_SESSION['login_attempts'] >= 5 ? 'Account Temporarily Locked' : 'Login' ?>
                </button>
            </div>
        </form>
        
        <div class="text-center">
            <small>Don't have an account? <a href="register.php">Register here</a></small><br>
            <small><a href="forgot-password.php">Forgot your password?</a></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>
<script>
    'use strict';
    
    // Password visibility toggle
    document.getElementById("togglePassword").addEventListener("click", function () {
        const passwordInput = document.getElementById("password");
        const icon = this.querySelector("i");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
            this.setAttribute("aria-label", "Hide password");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
            this.setAttribute("aria-label", "Show password");
        }
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            e.preventDefault();
            alert('Please fill in all fields.');
            return false;
        }
        
        if (username.length > 50) {
            e.preventDefault();
            alert('Username is too long.');
            return false;
        }
        
        if (!/^[a-zA-Z0-9_.-]+$/.test(username)) {
            e.preventDefault();
            alert('Username contains invalid characters.');
            return false;
        }
    });
    
    // Clear password on page unload for security
    window.addEventListener('beforeunload', function() {
        document.getElementById('password').value = '';
    });
    
    // Auto-focus username field
    document.getElementById('username').focus();
</script>
</body>
</html>