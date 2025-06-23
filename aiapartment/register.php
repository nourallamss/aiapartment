<?php
include __DIR__ . '/includes/csrf_token.php';
$csrf_token = generateCsrfToken();
?>
<?php
require 'db.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    

    // Username validation
    if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors[] = "Username must be 3–20 characters and contain only letters, numbers, or underscores.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation
    if (strlen($password) < 8 || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must be at least 8 characters, with one number and one uppercase letter.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username is already taken.";
    }
    $stmt->close();

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "An account with this email already exists.";
    }
    $stmt->close();

    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            $success = "Registration successful! <a href='login.php'>Login here</a>.";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;">
        <h2 class="text-center mb-4">Register</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" id="username" required value="<?= htmlspecialchars($_POST["username"] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email" required value="<?= htmlspecialchars($_POST["email"] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" id="password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="form-text">At least 8 characters, 1 number, 1 uppercase.</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%;"></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <small>Already have an account? <a href="login.php">Login</a></small>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript Enhancements -->
<script>
const passwordInput = document.getElementById("password");
const strengthBar = document.getElementById("strengthBar");

passwordInput.addEventListener("input", () => {
    const val = passwordInput.value;
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[\W]/.test(val)) strength++;

    strengthBar.style.width = `${strength * 25}%`;
    strengthBar.className = "progress-bar"; // Reset classes

    if (strength <= 1) {
        strengthBar.classList.add("bg-danger");
    } else if (strength === 2) {
        strengthBar.classList.add("bg-warning");
    } else {
        strengthBar.classList.add("bg-success");
    }
});

function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>
</body>
</html>
