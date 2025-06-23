<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    die('{"success":false,"message":"User not logged in"}');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['property_id']) || !is_numeric($input['property_id'])) {
    die('{"success":false,"message":"Invalid property ID"}');
}

$property_id = intval($input['property_id']);
$user_email = $_SESSION['email'];

try {
    $host = 'localhost';
    $db = 'forfree';
    $user = 'root';
    $pass = '';

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user already liked this property
    $stmt = $pdo->prepare("SELECT id FROM property_likes WHERE property_id = ? AND user_email = ?");
    $stmt->execute([$property_id, $user_email]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM property_likes WHERE property_id = ? AND user_email = ?");
        $stmt->execute([$property_id, $user_email]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO property_likes (property_id, user_email, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$property_id, $user_email]);
        $liked = true;
    }

    // Get updated like count
    $stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM property_likes WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $like_count = $stmt->fetch()['like_count'];

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'like_count' => intval($like_count)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>