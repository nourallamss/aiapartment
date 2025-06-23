<?php
// Start session at the VERY TOP of the file before any output

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    // Use JavaScript redirect instead of header() to avoid header sent errors
    echo "<script>window.location.href = '?page=login';</script>";
    exit;
}

// Get property ID from URL
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id <= 0) {
    echo "Invalid property ID";
    exit;
}

// Database connection
$host = 'localhost';
$db = 'forfree';
$user = 'root';
$pass = '';

$content = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if property exists
    $stmt = $pdo->prepare("SELECT * FROM data WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        $content = '<div class="alert alert-danger">Property not found</div>';
    } else {
        // Check if confirmation was received
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            // Delete the property
            $deleteStmt = $pdo->prepare("DELETE FROM data WHERE id = ?");
            
            if ($deleteStmt->execute([$property_id])) {
                // Use JavaScript redirect after successful deletion
                $content = '
                <div class="container mt-5">
                    <div class="alert alert-success">Property deleted successfully!</div>
                    <script>setTimeout(function(){ window.location.href = "index.php"; }, 1500);</script>
                </div>';
            } else {
                $content = '<div class="alert alert-danger">Error deleting property</div>';
            }
        } else {
            // Show confirmation dialog
            $content = '
            <div class="container mt-5">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">Delete Property</h3>
                    </div>
                    <div class="card-body">
                        <p>Are you sure you want to delete the property at <strong>' . htmlspecialchars($property['location']) . '</strong>?</p>
                        <p>This action cannot be undone.</p>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <a href="?page=delete&id=' . $property_id . '&confirm=yes" class="btn btn-danger">Delete Property</a>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }

} catch (PDOException $e) {
    $content = '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Make sure there's no output before this point
renderPage('Delete Property - HomeEasy', $content);
?>