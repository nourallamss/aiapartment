<?php

if (session_status() == PHP_SESSION_NONE) {
    // Security Configuration - only set if session hasn't started
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
    session_regenerate_id(true);
} else {
    // Session already active, just regenerate ID if headers not sent
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
}
// Database configuration
$host = 'localhost';
$dbname = 'forfree';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}

// Security Functions
class SecurityManager {
    private $pdo;
    private $max_attempts = 5;
    private $lockout_time = 1800; // 30 minutes
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // CSRF Token Generation and Validation
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Rate Limiting
    public function checkRateLimit($action, $identifier) {
        $current_time = time();
        $window_start = $current_time - 300; // 5 minute window
        
        // Clean old attempts
        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE timestamp < ?");
        $stmt->execute([$window_start]);
        
        // Count recent attempts
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE action = ? AND identifier = ? AND timestamp > ?");
        $stmt->execute([$action, $identifier, $window_start]);
        $attempts = $stmt->fetchColumn();
        
        if ($attempts >= $this->max_attempts) {
            return false;
        }
        
        // Log this attempt
        $stmt = $this->pdo->prepare("INSERT INTO rate_limits (action, identifier, timestamp) VALUES (?, ?, ?)");
        $stmt->execute([$action, $identifier, $current_time]);
        
        return true;
    }
    
    // Account Lockout Check
    public function isAccountLocked($user_id) {
        $stmt = $this->pdo->prepare("SELECT locked_until FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $locked_until = $stmt->fetchColumn();
        
        if ($locked_until && time() < $locked_until) {
            return true;
        }
        
        // Unlock if time has passed
        if ($locked_until && time() >= $locked_until) {
            $stmt = $this->pdo->prepare("UPDATE users SET locked_until = NULL, failed_attempts = 0 WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        return false;
    }
    
    // Log failed attempts
    public function logFailedAttempt($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $stmt = $this->pdo->prepare("SELECT failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $attempts = $stmt->fetchColumn();
        
        if ($attempts >= $this->max_attempts) {
            $lock_until = time() + $this->lockout_time;
            $stmt = $this->pdo->prepare("UPDATE users SET locked_until = ? WHERE id = ?");
            $stmt->execute([$lock_until, $user_id]);
        }
    }
    
    // Clear failed attempts on successful action
    public function clearFailedAttempts($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    // Input Sanitization
    public function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    // Validate file upload security
    public function validateUpload($file) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error occurred.'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'File too large. Maximum 5MB allowed.'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid file type.'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_extensions)) {
            return ['success' => false, 'message' => 'Invalid file extension.'];
        }
        
        // Check for embedded PHP code
        $file_content = file_get_contents($file['tmp_name']);
        if (strpos($file_content, '<?php') !== false || strpos($file_content, '<?=') !== false) {
            return ['success' => false, 'message' => 'File contains invalid content.'];
        }
        
        return ['success' => true];
    }
    
    // Generate 2FA Secret
    public function generate2FASecret() {
        return base32_encode(random_bytes(20));
    }
    
    // Verify 2FA Code
    public function verify2FA($secret, $code) {
        $time = floor(time() / 30);
        
        // Check current time window and previous/next windows for clock drift
        for ($i = -1; $i <= 1; $i++) {
            $calculated_code = $this->generateTOTP($secret, $time + $i);
            if (hash_equals($calculated_code, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function generateTOTP($secret, $time) {
        $secret = base32_decode($secret);
        $time = pack('N*', 0, $time);
        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    // Audit Logging
    public function logActivity($user_id, $action, $details = '') {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip_address, $user_agent, time()]);
    }
}

// Base32 encoding/decoding functions for 2FA
function base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 8;
        $v += ord($data[$i]);
        $vbits += 8;
        
        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $alphabet[$v >> $vbits];
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    if ($vbits > 0) {
        $v <<= (5 - $vbits);
        $output .= $alphabet[$v];
    }
    
    return $output;
}

function base32_decode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 5;
        $v += strpos($alphabet, $data[$i]);
        $vbits += 5;
        
        if ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr($v >> $vbits);
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    return $output;
}

// Initialize Security Manager
$security = new SecurityManager($pdo);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Check if account is locked
if ($security->isAccountLocked($user_id)) {
    $error = "Account is temporarily locked due to security reasons. Please try again later.";
    session_destroy();
    header("Location: login.php?error=" . urlencode($error));
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !$security->validateCSRFToken($_POST['csrf_token'])) {
        $error = "Security token validation failed. Please refresh the page and try again.";
        $security->logActivity($user_id, 'CSRF_VIOLATION', 'Invalid CSRF token');
    } else {
        
        // Update Profile Information
        if (isset($_POST['update_profile'])) {
            if (!$security->checkRateLimit('profile_update', $client_ip)) {
                $error = "Too many attempts. Please wait before trying again.";
            } else {
                $new_username = $security->sanitizeInput($_POST['username']);
                $new_email = $security->sanitizeInput($_POST['email']);
                
                if (empty($new_username) || empty($new_email)) {
                    $error = "Username and email are required.";
                } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Invalid email format.";
                } elseif (strlen($new_username) < 3 || strlen($new_username) > 30) {
                    $error = "Username must be between 3 and 30 characters.";
                } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
                    $error = "Username can only contain letters, numbers, and underscores.";
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute([$new_username, $new_email, $user_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $error = "Username or email already exists.";
                        $security->logFailedAttempt($user_id);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, updated_at = ? WHERE id = ?");
                        if ($stmt->execute([$new_username, $new_email, date('Y-m-d H:i:s'), $user_id])) {
                            $message = "Profile updated successfully!";
                            $_SESSION['username'] = $new_username;
                            $security->clearFailedAttempts($user_id);
                            $security->logActivity($user_id, 'PROFILE_UPDATE', "Username: $new_username, Email: $new_email");
                        } else {
                            $error = "Error updating profile.";
                        }
                    }
                }
            }
        }
        
        // Change Password
        if (isset($_POST['change_password'])) {
            if (!$security->checkRateLimit('password_change', $client_ip)) {
                $error = "Too many password change attempts. Please wait before trying again.";
            } else {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                $two_fa_code = $_POST['two_fa_code'] ?? '';
                
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error = "All password fields are required.";
                } elseif ($new_password !== $confirm_password) {
                    $error = "New passwords do not match.";
                } elseif (strlen($new_password) < 8) {
                    $error = "Password must be at least 8 characters long.";
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $new_password)) {
                    $error = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                } else {
                    $stmt = $pdo->prepare("SELECT password, two_fa_secret FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch();
                    
                    if (password_verify($current_password, $user_data['password'])) {
                        // Check 2FA if enabled
                        if ($user_data['two_fa_secret'] && !empty($two_fa_code)) {
                            if (!$security->verify2FA($user_data['two_fa_secret'], $two_fa_code)) {
                                $error = "Invalid 2FA code.";
                                $security->logFailedAttempt($user_id);
                            }
                        }
                        
                        if (empty($error)) {
                            $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
                            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = ? WHERE id = ?");
                            if ($stmt->execute([$hashed_password, date('Y-m-d H:i:s'), $user_id])) {
                                $message = "Password changed successfully!";
                                $security->clearFailedAttempts($user_id);
                                $security->logActivity($user_id, 'PASSWORD_CHANGE', 'Password updated');
                            } else {
                                $error = "Error changing password.";
                            }
                        }
                    } else {
                        $error = "Current password is incorrect.";
                        $security->logFailedAttempt($user_id);
                    }
                }
            }
        }
        
        // Upload Profile Photo
        if (isset($_POST['upload_photo']) && isset($_FILES['profile_photo'])) {
            if (!$security->checkRateLimit('photo_upload', $client_ip)) {
                $error = "Too many upload attempts. Please wait before trying again.";
            } else {
                $upload_validation = $security->validateUpload($_FILES['profile_photo']);
                
                if (!$upload_validation['success']) {
                    $error = $upload_validation['message'];
                } else {
                    $upload_dir = 'uploads/profile_photos/';
                    
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file = $_FILES['profile_photo'];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $new_filename = 'profile_' . $user_id . '_' . hash('sha256', uniqid()) . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        chmod($upload_path, 0644);
                        
                        $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $old_photo = $stmt->fetchColumn();
                        
                        if ($old_photo && file_exists($old_photo)) {
                            unlink($old_photo);
                        }
                        
                        $stmt = $pdo->prepare("UPDATE users SET profile_photo = ?, updated_at = ? WHERE id = ?");
                        if ($stmt->execute([$upload_path, date('Y-m-d H:i:s'), $user_id])) {
                            $message = "Profile photo updated successfully!";
                            $security->logActivity($user_id, 'PHOTO_UPDATE', 'Profile photo changed');
                        } else {
                            $error = "Error updating profile photo in database.";
                            unlink($upload_path);
                        }
                    } else {
                        $error = "Error uploading file.";
                    }
                }
            }
        }
        
        // Enable/Disable 2FA
        if (isset($_POST['toggle_2fa'])) {
            $action = $_POST['two_fa_action'];
            
            if ($action === 'enable') {
                $secret = $security->generate2FASecret();
                $stmt = $pdo->prepare("UPDATE users SET two_fa_secret = ?, updated_at = ? WHERE id = ?");
                if ($stmt->execute([$secret, date('Y-m-d H:i:s'), $user_id])) {
                    $message = "2FA has been enabled. Please scan the QR code with your authenticator app.";
                    $security->logActivity($user_id, '2FA_ENABLED', '2FA authentication enabled');
                }
            } elseif ($action === 'disable') {
                $two_fa_code = $_POST['two_fa_disable_code'] ?? '';
                $password = $_POST['password_2fa'] ?? '';
                
                $stmt = $pdo->prepare("SELECT password, two_fa_secret FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch();
                
                if (password_verify($password, $user_data['password']) && $security->verify2FA($user_data['two_fa_secret'], $two_fa_code)) {
                    $stmt = $pdo->prepare("UPDATE users SET two_fa_secret = NULL, updated_at = ? WHERE id = ?");
                    if ($stmt->execute([date('Y-m-d H:i:s'), $user_id])) {
                        $message = "2FA has been disabled.";
                        $security->logActivity($user_id, '2FA_DISABLED', '2FA authentication disabled');
                    }
                } else {
                    $error = "Invalid password or 2FA code.";
                    $security->logFailedAttempt($user_id);
                }
            }
        }
        
        // Delete Account
        if (isset($_POST['delete_account'])) {
            if (!$security->checkRateLimit('account_deletion', $client_ip)) {
                $error = "Too many deletion attempts. Please wait before trying again.";
            } else {
                $password_confirm = $_POST['password_confirm'];
                $two_fa_code = $_POST['delete_2fa_code'] ?? '';
                $confirmation_text = $_POST['confirmation_text'] ?? '';
                
                if (empty($password_confirm)) {
                    $error = "Please enter your password to confirm account deletion.";
                } elseif ($confirmation_text !== 'DELETE') {
                    $error = "Please type 'DELETE' to confirm account deletion.";
                } else {
                    $stmt = $pdo->prepare("SELECT password, profile_photo, two_fa_secret FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch();
                    
                    $password_valid = password_verify($password_confirm, $user_data['password']);
                    $two_fa_valid = true;
                    
                    if ($user_data['two_fa_secret'] && !empty($two_fa_code)) {
                        $two_fa_valid = $security->verify2FA($user_data['two_fa_secret'], $two_fa_code);
                    }
                    
                    if ($password_valid && $two_fa_valid) {
                        // Delete profile photo
                        if ($user_data['profile_photo'] && file_exists($user_data['profile_photo'])) {
                            unlink($user_data['profile_photo']);
                        }
                        
                        // Delete user account
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt->execute([$user_id])) {
                            $security->logActivity($user_id, 'ACCOUNT_DELETED', 'Account permanently deleted');
                            session_destroy();
                            header("Location: login.php?deleted=1");
                            exit();
                        } else {
                            $error = "Error deleting account.";
                        }
                    } else {
                        $error = "Invalid password or 2FA code.";
                        $security->logFailedAttempt($user_id);
                    }
                }
            }
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Generate CSRF token
$csrf_token = $security->generateCSRFToken();

// Generate QR code data for 2FA
$qr_code_url = '';
if ($user['two_fa_secret']) {
    $app_name = 'UserManagement';
    $user_email = $user['email'];
    $qr_code_url = "otpauth://totp/{$app_name}:{$user_email}?secret={$user['two_fa_secret']}&issuer={$app_name}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">
    <title>Secure User Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .security-badge {
            position: absolute;
            top: 15px;
            left: 20px;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .profile-photo-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #999;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 25px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            background: #fafafa;
            position: relative;
        }
        
        .section.secure {
            border-color: #4CAF50;
            background: #f8fff8;
        }
        
        .section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .security-icon {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak { color: #ff4444; }
        .strength-medium { color: #ffaa00; }
        .strength-strong { color: #00aa00; }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .danger-zone {
            border-color: #ff6b6b !important;
            background: #fff5f5 !important;
        }
        
        .two-fa-section {
            border: 2px dashed #4CAF50;
            background: #f8fff8;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .security-info {
            background: #e3f2fd;
            border: 1px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 14px;
            color: #1565C0;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .confirmation-input {
            background: #fff8f0;
            border: 2px solid #ff9800;
        }
        
        .session-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .activity-log {
            max-height: 300px;
            overflow-y: auto;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
        }
        
        .log-entry {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .log-entry:last-child {
            border-bottom: none;
        }
        
        .log-time {
            color: #666;
            font-weight: 600;
        }
        
        .log-action {
            color: #333;
            margin-left: 10px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .section {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .security-badge,
            .logout-btn {
                position: static;
                margin: 10px 0;
                display: inline-block;
            }
        }
         .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color:rgb(0, 0, 0);
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid rgb(0, 0, 0);
            border-radius: 5px;
        }
        
        .back-link:hover {
            background-color:rgb(0, 0, 0);
            color: white;
        }
    </style>
    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push("At least 8 characters");
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push("Lowercase letter");
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push("Uppercase letter");
            
            if (/\d/.test(password)) strength++;
            else feedback.push("Number");
            
            if (/[@$!%*?&]/.test(password)) strength++;
            else feedback.push("Special character");
            
            return { strength, feedback };
        }
        
        function updatePasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('password_strength');
            
            if (!password) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            const result = checkPasswordStrength(password);
            let className = '';
            let text = '';
            
            if (result.strength < 3) {
                className = 'strength-weak';
                text = 'Weak';
            } else if (result.strength < 5) {
                className = 'strength-medium';
                text = 'Medium';
            } else {
                className = 'strength-strong';
                text = 'Strong';
            }
            
            strengthDiv.innerHTML = `<span class="${className}">Password Strength: ${text}</span>`;
            if (result.feedback.length > 0) {
                strengthDiv.innerHTML += `<br><small>Missing: ${result.feedback.join(', ')}</small>`;
            }
        }
        
        // Auto-logout on inactivity
        let inactivityTimer;
        const INACTIVITY_TIME = 30 * 60 * 1000; // 30 minutes
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                alert('Session expired due to inactivity. You will be logged out.');
                window.location.href = 'logout.php';
            }, INACTIVITY_TIME);
        }
        
        // Reset timer on user activity
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);
        
        // Initialize timer
        resetInactivityTimer();
        
        // Form validation
        function validateForm(formName) {
            const form = document.forms[formName];
            if (!form) return true;
            
            // Basic validation
            const required = form.querySelectorAll('[required]');
            for (let field of required) {
                if (!field.value.trim()) {
                    alert(`Please fill in the ${field.name} field.`);
                    field.focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // Confirm dangerous actions
        function confirmDangerousAction(message) {
            return confirm(message + '\n\nThis action cannot be undone. Are you absolutely sure?');
        }
    </script>
</head>
<body>
        <a href="javascript:history.back()" class="back-link">← Back to Listings</a>

    <div class="container">
        <div class="header">
            <div class="security-badge">🔒 Secured</div>
            <a href="logout.php" class="logout-btn">Logout</a>
            <div class="profile-photo-container">
                <?php if ($user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo" class="profile-photo">
                <?php else: ?>
                    <div class="profile-photo">👤</div>
                <?php endif; ?>
            </div>
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Secure Profile Management System</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Session Information -->
            <div class="session-info">
                <strong>Session Info:</strong>
                IP Address: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?> |
                Last Login: <?php echo date('Y-m-d H:i:s', strtotime($user['updated_at'] ?? $user['created_at'])); ?> |
                2FA Status: <?php echo $user['two_fa_secret'] ? '✅ Enabled' : '❌ Disabled'; ?>
            </div>
            
            <!-- Profile Photo Upload -->
            <div class="section secure">
                <h3><div class="security-icon">🔐</div>Profile Photo</h3>
                <div class="security-info">
                    <strong>Security:</strong> Only JPG, PNG, and GIF files under 5MB are allowed. Files are scanned for malicious content.
                </div>
                <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm('photo_form')" name="photo_form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="profile_photo">Choose Profile Photo:</label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/gif" required>
                    </div>
                    <button type="submit" name="upload_photo" class="btn">🔐 Update Photo</button>
                </form>
            </div>
            
            <!-- Update Profile -->
            <div class="section secure">
                <h3><div class="security-icon">👤</div>Profile Information</h3>
                <div class="security-info">
                    <strong>Security:</strong> Username must be unique and contain only letters, numbers, and underscores (3-30 characters).
                </div>
                <form method="POST" onsubmit="return validateForm('profile_form')" name="profile_form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                                   pattern="[a-zA-Z0-9_]{3,30}" title="3-30 characters, letters, numbers, and underscores only" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn">🔐 Update Profile</button>
                </form>
            </div>
            
            <!-- Two-Factor Authentication -->
            <div class="section secure">
                <h3><div class="security-icon">🛡️</div>Two-Factor Authentication (2FA)</h3>
                <div class="security-info">
                    <strong>Enhanced Security:</strong> 2FA adds an extra layer of protection using time-based codes from your authenticator app.
                </div>
                
                <?php if (!$user['two_fa_secret']): ?>
                    <div class="two-fa-section">
                        <h4>Enable 2FA</h4>
                        <p>Protect your account with two-factor authentication using apps like Google Authenticator, Authy, or Microsoft Authenticator.</p>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="two_fa_action" value="enable">
                            <button type="submit" name="toggle_2fa" class="btn btn-success">🛡️ Enable 2FA</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="two-fa-section">
                        <h4>2FA is Enabled ✅</h4>
                        <div class="qr-code">
                            <p><strong>QR Code for Setup:</strong></p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($qr_code_url); ?>" alt="2FA QR Code">
                            <p><small>Secret Key: <?php echo chunk_split($user['two_fa_secret'], 4, ' '); ?></small></p>
                        </div>
                        
                        <form method="POST" onsubmit="return confirmDangerousAction('Disable 2FA protection?')">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="two_fa_action" value="disable">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password_2fa">Password:</label>
                                    <input type="password" id="password_2fa" name="password_2fa" required>
                                </div>
                                <div class="form-group">
                                    <label for="two_fa_disable_code">2FA Code:</label>
                                    <input type="text" id="two_fa_disable_code" name="two_fa_disable_code" pattern="[0-9]{6}" required>
                                </div>
                            </div>
                            <button type="submit" name="toggle_2fa" class="btn btn-danger">🚫 Disable 2FA</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Change Password -->
            <div class="section secure">
                <h3><div class="security-icon">🔑</div>Change Password</h3>
                <div class="security-info">
                    <strong>Security Requirements:</strong> Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                </div>
                <form method="POST" onsubmit="return validateForm('password_form')" name="password_form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" 
                               onkeyup="updatePasswordStrength()" 
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" required>
                        <div id="password_strength" class="password-strength"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <?php if ($user['two_fa_secret']): ?>
                    <div class="form-group">
                        <label for="two_fa_code">2FA Code:</label>
                        <input type="text" id="two_fa_code" name="two_fa_code" pattern="[0-9]{6}" 
                               placeholder="6-digit code from your authenticator app" required>
                    </div>
                    <?php endif; ?>
                    <button type="submit" name="change_password" class="btn">🔐 Change Password</button>
                </form>
            </div>
            
            <!-- Recent Activity Log -->
            <div class="section">
                <h3><div class="security-icon">📋</div>Recent Activity</h3>
                <div class="activity-log">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
                    $stmt->execute([$user_id]);
                    $logs = $stmt->fetchAll();
                    
                    if ($logs):
                        foreach ($logs as $log):
                    ?>
                        <div class="log-entry">
                            <span class="log-time"><?php echo date('Y-m-d H:i:s', $log['timestamp']); ?></span>
                            <span class="log-action"><?php echo htmlspecialchars($log['action']); ?></span>
                            <?php if ($log['details']): ?>
                                <span class="log-details"> - <?php echo htmlspecialchars($log['details']); ?></span>
                            <?php endif; ?>
                            <span class="log-ip"> (IP: <?php echo htmlspecialchars($log['ip_address']); ?>)</span>
                        </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <div class="log-entry">No recent activity found.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Delete Account -->
            <div class="section danger-zone">
                <h3>⚠️ Danger Zone</h3>
                <div class="security-info">
                    <strong>Warning:</strong> Account deletion is permanent and cannot be undone. All your data will be permanently deleted.
                </div>
                <form method="POST" onsubmit="return confirmDangerousAction('PERMANENTLY DELETE your account and ALL data?')">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password_confirm">Password:</label>
                            <input type="password" id="password_confirm" name="password_confirm" required>
                        </div>
                        <?php if ($user['two_fa_secret']): ?>
                        <div class="form-group">
                            <label for="delete_2fa_code">2FA Code:</label>
                            <input type="text" id="delete_2fa_code" name="delete_2fa_code" pattern="[0-9]{6}" required>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="confirmation_text">Type "DELETE" to confirm:</label>
                        <input type="text" id="confirmation_text" name="confirmation_text" 
                               class="confirmation-input" placeholder="Type DELETE here" required>
                    </div>
                    <button type="submit" name="delete_account" class="btn btn-danger">💀 Delete Account Forever</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Additional database tables needed (run these SQL commands):
/*
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    timestamp INT NOT NULL,
    INDEX idx_action_identifier (action, identifier),
    INDEX idx_timestamp (timestamp)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    timestamp INT NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS two_fa_secret VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS failed_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until INT DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
*/
?>