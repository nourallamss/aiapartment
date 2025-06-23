
<?php
// Handle AJAX requests at the very top - before ANY output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    
    // Start output buffering to prevent header issues
    ob_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['email'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['property_id']) || !is_numeric($input['property_id'])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
        exit;
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

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'like_count' => intval($like_count)
        ]);
        exit;

    } catch (Exception $e) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        exit;
    }
}

// Continue with your existing sell.php content below this...
?>
<?php
// Add this to the VERY TOP of your layouts/pages/sell.php file
// BEFORE any HTML output or includes

// Handle AJAX like toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_SERVER['CONTENT_TYPE']) && 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    
    // Prevent any output before this point
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer
    ob_start();
    
    try {
        // Check if user is logged in
        if (!isset($_SESSION['email'])) {
            throw new Exception('User not logged in');
        }

        // Get JSON input
        $json_input = file_get_contents('php://input');
        $input = json_decode($json_input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        if (!isset($input['property_id']) || !is_numeric($input['property_id'])) {
            throw new Exception('Invalid property ID');
        }

        $property_id = intval($input['property_id']);
        $user_email = $_SESSION['email'];

        // Database connection
        $host = 'localhost';
        $db = 'forfree';
        $user = 'root';
        $pass = '';

        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if property exists
        $stmt = $pdo->prepare("SELECT id FROM data WHERE id = ?");
        $stmt->execute([$property_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Property not found');
        }

        // Check if user already liked this property
        $stmt = $pdo->prepare("SELECT id FROM property_likes WHERE property_id = ? AND user_email = ?");
        $stmt->execute([$property_id, $user_email]);
        $existing_like = $stmt->fetch();

        if ($existing_like) {
            // Unlike - remove the like
            $stmt = $pdo->prepare("DELETE FROM property_likes WHERE property_id = ? AND user_email = ?");
            $stmt->execute([$property_id, $user_email]);
            $liked = false;
        } else {
            // Like - add the like
            $stmt = $pdo->prepare("INSERT INTO property_likes (property_id, user_email, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$property_id, $user_email]);
            $liked = true;
        }

        // Get updated like count
        $stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM property_likes WHERE property_id = ?");
        $stmt->execute([$property_id]);
        $like_count = $stmt->fetch()['like_count'];

        // Clear any accidental output
        ob_clean();
        
        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'like_count' => intval($like_count)
        ]);
        
    } catch (Exception $e) {
        // Clear any output
        ob_clean();
        
        // Send error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    exit; // Important: Stop execution here for AJAX requests
}

// Your existing sell.php content continues here...
?>
<?php
// Start session at the very top
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Handle AJAX requests for like and report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $host = 'localhost';
    $db = 'forfree';
    $user = 'root';
    $pass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        $property_id = intval($_POST['property_id']);
        $user_email = $_SESSION['email'] ?? null;
        
        if (!$user_email) {
            echo json_encode(['success' => false, 'message' => 'Please login to perform this action']);
            exit;
        }
        
        if ($_POST['action'] === 'like') {
            // Check if user already liked this property
            $stmt = $pdo->prepare("SELECT id FROM property_likes WHERE property_id = ? AND user_email = ?");
            $stmt->execute([$property_id, $user_email]);
            
            if ($stmt->fetch()) {
                // Unlike - remove the like
                $stmt = $pdo->prepare("DELETE FROM property_likes WHERE property_id = ? AND user_email = ?");
                $stmt->execute([$property_id, $user_email]);
                $action = 'unliked';
            } else {
                // Like - add the like
                $stmt = $pdo->prepare("INSERT INTO property_likes (property_id, user_email, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$property_id, $user_email]);
                $action = 'liked';
            }
            
            // Get updated like count
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM property_likes WHERE property_id = ?");
            $stmt->execute([$property_id]);
            $like_count = $stmt->fetch()['count'];
            
            echo json_encode(['success' => true, 'action' => $action, 'like_count' => $like_count]);
            
        } elseif ($_POST['action'] === 'report') {
            $reason = $_POST['reason'] ?? 'No reason provided';
            
            // Check if user already reported this property
            $stmt = $pdo->prepare("SELECT id FROM property_reports WHERE property_id = ? AND user_email = ?");
            $stmt->execute([$property_id, $user_email]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already reported this property']);
            } else {
                // Add report
                $stmt = $pdo->prepare("INSERT INTO property_reports (property_id, user_email, reason, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$property_id, $user_email, $reason]);
                
                echo json_encode(['success' => true, 'message' => 'Property reported successfully']);
            }
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        error_log("Database error: " . $e->getMessage());
    }
    exit;
}
?>

<!DOCTYPE html>
<!--[if IE]><html class="ie" lang="en"><![endif]-->
<!--[if !IE]><!--><html lang="en"><!--<![endif]-->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings | HomeEasy</title>
    <!--[if lt IE 9]>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .property-card {
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            display: flex;
            flex-direction: column;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .property-card-img-container {
            height: 200px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .property-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-card-img {
            transform: scale(1.03);
        }
        
        .card-body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .card-text {
            flex-grow: 1;
            font-size: 0.9rem;
        }
        
        .property-feature {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 5px;
        }
        
        .property-feature i {
            color: var(--primary-color);
            margin-right: 5px;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--success-color);
            margin: 10px 0;
        }
        
        .action-buttons {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        
        .like-report-buttons {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .like-btn {
            transition: all 0.3s ease;
        }
        
        .like-btn.liked {
            color: #dc3545 !important;
            transform: scale(1.1);
        }
        
        .like-btn:hover {
            transform: scale(1.05);
        }
        
        .report-btn:hover {
            color: var(--warning-color) !important;
        }
        
        .like-count {
            font-size: 0.85rem;
            color: var(--secondary-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .form-select {
            max-width: 200px;
            display: inline-block;
        }
        
        /* Loading animation */
        .btn-loading {
            pointer-events: none;
            opacity: 0.6;
        }
        
        .btn-loading .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        
        /* IE10+ specific fixes */
        @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
            .property-card-img {
                width: 100% !important;
                height: auto !important;
            }
            .property-card-img-container {
                display: block;
            }
        }
        
        /* Fallback for very old browsers */
        .no-flexbox .property-card {
            display: block;
            height: auto;
        }
    </style>
</head>
<body>
    <!--[if lt IE 10]>
    <div class="alert alert-warning text-center">
        You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience.
    </div>
    <![endif]-->
    <?php include "navbar.php";?>

   <div class="container mt-4 mt-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 mb-md-5 flex-column flex-md-row">
        <h1 class="h2 mb-3 mb-md-0">Property Listings</h1>
        <div class="d-flex align-items-center">
            <form method="GET" class="d-flex align-items-center">
                <!-- Preserve existing GET parameters -->
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                <?php endif; ?>
                
                <select name="sort" class="form-select me-2" aria-label="Sort properties" onchange="this.form.submit()">
                    <option value="">Sort by</option>
                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="date_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_desc') ? 'selected' : ''; ?>>Newest First</option>
                    <option value="date_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_asc') ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="likes_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'likes_desc') ? 'selected' : ''; ?>>Most Liked</option>
                </select>
            </form>
            <a href="seller.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Property
            </a>
        </div>
    </div>

    <div class="row g-4">
        <?php
        $host = 'localhost';
        $db = 'forfree';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Build ORDER BY clause based on sort parameter
            $sort_param = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
            $order_by = "ORDER BY d.created_at DESC"; // default

            switch ($sort_param) {
                case 'price_asc':
                    $order_by = "ORDER BY CAST(d.sale AS DECIMAL(10,2)) ASC";
                    break;
                case 'price_desc':
                    $order_by = "ORDER BY CAST(d.sale AS DECIMAL(10,2)) DESC";
                    break;
                case 'date_asc':
                    $order_by = "ORDER BY d.created_at ASC";
                    break;
                case 'date_desc':
                    $order_by = "ORDER BY d.created_at DESC";
                    break;
                case 'likes_desc':
                    $order_by = "ORDER BY like_count DESC, d.created_at DESC";
                    break;
                default:
                    $order_by = "ORDER BY d.created_at DESC";
                    break;
            }

            // Updated query to include like counts with dynamic sorting
            $query = "
                SELECT d.*, 
                       COALESCE(like_counts.like_count, 0) as like_count
                FROM data d
                LEFT JOIN (
                    SELECT property_id, COUNT(*) as like_count 
                    FROM property_likes 
                    GROUP BY property_id
                ) like_counts ON d.id = like_counts.property_id
                $order_by
            ";

            $stmt = $pdo->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get user's liked properties if logged in
            $user_likes = [];
            if (isset($_SESSION['email'])) {
                $stmt = $pdo->prepare("SELECT property_id FROM property_likes WHERE user_email = ?");
                $stmt->execute([$_SESSION['email']]);
                $user_likes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'property_id');
            }

            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $images = htmlspecialchars($row['images'] ?? '');
                    $image_tag = '';
                    
                    if (!empty($images) && file_exists($images)) {
                        $image_tag = "<div class='property-card-img-container'>
                                        <img src='$images' class='property-card-img' alt='Property in " . htmlspecialchars($row['location']) . "'>
                                     </div>";
                    } else {
                        $image_tag = "<div class='property-card-img-container bg-light d-flex align-items-center justify-content-center'>
                                        <i class='fas fa-home fa-3x text-secondary'></i>
                                      </div>";
                    }

                    // Format data for display
                    $formatted_price = number_format(floatval($row['sale']), 2);
                    $formatted_total_price = number_format(floatval($row['totalsale']), 2);

                    $date = new DateTime($row['created_at']);
                    $formatted_date = $date->format('M j, Y');
                    $phone = !empty($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A';
                    $email = !empty($row['email']) ? htmlspecialchars($row['email']) : 'N/A';
                    
                    // Check if user liked this property
                    $is_liked = in_array($row['id'], $user_likes);
                    $like_class = $is_liked ? 'liked' : '';
                    $like_icon = $is_liked ? 'fas fa-heart' : 'far fa-heart';

                    echo "<div class='col-12 col-md-6 col-lg-4'>
                            <div class='card property-card h-100'>
                                $image_tag
                                <div class='property-label luxury' id='propertyLabel-{$row['id']}'>" . htmlspecialchars($row['property_type']) . "</div>

                                <div class='card-body'>
                                    <h5 class='card-title'>" . htmlspecialchars($row['location']) . "</h5>
                                    
                                    <div class='mb-2'>
                                        <span class='property-feature'><i class='fas fa-bed'></i> " . htmlspecialchars($row['rooms']) . " beds</span>
                                        <span class='property-feature'><i class='fas fa-bath'></i> " . htmlspecialchars($row['bathrooms']) . " baths</span>
                                        <span class='property-feature'><i class='fas fa-ruler-combined'></i> " . htmlspecialchars($row['space']) . " m²</span>
                                    </div>
                                    
                                    <div class='mb-2'>
                                        <strong>Presenter Price:</strong>
                                        <div class='price-tag'>\$$formatted_price</div>
                                        <strong>Total Price:</strong>
                                        <div class='price-tag'>\$$formatted_total_price</div>
                                    </div>";

                    // Add coordinates if available
                    if (!empty($row['latitude']) && !empty($row['longitude'])) {
                        echo "<div class='mb-2'>
                                <small class='text-muted'>
                                    <i class='fas fa-map-marker-alt me-1'></i> 
                                    Coordinates: " . htmlspecialchars($row['latitude']) . ", " . htmlspecialchars($row['longitude']) . "
                                </small>
                              </div>";
                    }

                    echo "<p class='card-text'>
                            <small class='text-muted'><i class='fas fa-phone me-1'></i> $phone</small><br>
                            <small class='text-muted'><i class='fas fa-envelope me-1'></i> $email</small><br>
                            <small class='text-muted'><i class='fas fa-calendar-alt me-1'></i> $formatted_date</small>
                          </p>
                          
                          <div class='action-buttons'>";
                    
                    // Like and Report buttons (only for logged-in users who don't own the property)
                    if (isset($_SESSION['email']) && $_SESSION['email'] !== $row['email']) { 
                       // Replace this section in your PHP code:
echo "<div class='like-report-buttons d-flex justify-content-between align-items-center mb-2'>
        <div class='d-flex align-items-center'>
            <button class='btn btn-sm btn-outline-danger like-btn $like_class me-2' data-property-id='{$row['id']}' onclick='toggleLike({$row['id']})'>
                <i class='$like_icon'></i>
                <span class='like-count'>{$row['like_count']}</span>
            </button>
        </div>
        <button class='btn btn-sm btn-outline-warning report-btn' data-property-id='{$row['id']}' onclick='reportProperty({$row['id']})'>
            <i class='fas fa-flag'></i> Report
        </button>
      </div>";
                    }    
                    
                    // Always show View button
                    echo "<div class='d-flex gap-2 mb-2'>
                            <a href='?page=view&id={$row['id']}' class='btn btn-primary btn-sm flex-fill'>
                                <i class='fas fa-eye me-1'></i> View Details
                            </a>
                          </div>";

                    // Map button (only if coordinates are available)
                    if (!empty($row['latitude']) && !empty($row['longitude'])) {
                        echo "<div class='d-flex gap-2 mb-2'>
                                <a href='?page=maps&id={$row['id']}&lat=" . urlencode($row['latitude']) . "&lng=" . urlencode($row['longitude']) . "' class='btn btn-success btn-sm flex-fill'>
                                    <i class='fas fa-map me-1'></i> View on Map
                                </a>
                              </div>";
                    }
                    
                    // Edit/Delete buttons (only for property owner)
                    if (isset($_SESSION['email']) && $_SESSION['email'] === $row['email']) {
                        echo "<div class='d-flex gap-2'>
                                <a href='?page=edit&id={$row['id']}' class='btn btn-outline-primary btn-sm flex-fill'>
                                    <i class='fas fa-edit me-1'></i> Edit
                                </a>
                                <a href='?page=delete&id={$row['id']}' class='btn btn-outline-danger btn-sm flex-fill' onclick=\"return confirm('Are you sure you want to delete this property?');\">
                                    <i class='fas fa-trash me-1'></i> Delete
                                </a>
                              </div>";
                    }
                    
                    echo "</div></div></div></div>";
                }
            } else {
                echo "<div class='col-12 empty-state text-center py-5'>
                        <i class='fas fa-home fa-4x text-secondary mb-3'></i>
                        <h3 class='h4'>No Properties Found</h3>
                        <p class='text-muted mb-3'>There are currently no properties listed.</p>
                        <a href='seller.php' class='btn btn-primary'>
                            <i class='fas fa-plus me-1'></i> Add Property
                        </a>
                    </div>";
            }

        } catch (PDOException $e) {
            echo "<div class='col-12'>
                    <div class='alert alert-danger'>
                        <i class='fas fa-exclamation-triangle me-2'></i> 
                        <strong>Error:</strong> Unable to load properties. Please try again later.
                    </div>
                </div>";
            error_log("Database error: " . $e->getMessage());
        }
        ?>
    </div>
</div>

<script>
// Function to toggle like status
function toggleLike(propertyId) {
    <?php if (isset($_SESSION['email'])): ?>
        fetch('ajax/toggle_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                property_id: propertyId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeBtn = document.querySelector(`[data-property-id="${propertyId}"].like-btn`);
                const likeIcon = likeBtn.querySelector('i');
                const likeCount = likeBtn.querySelector('.like-count');
                
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    likeIcon.className = 'fas fa-heart';
                } else {
                    likeBtn.classList.remove('liked');
                    likeIcon.className = 'far fa-heart';
                }
                
                likeCount.textContent = data.like_count;
            } else {
                alert('Error: ' + (data.message || 'Unable to update like status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating like status');
        });
    <?php else: ?>
        alert('Please log in to like properties');
    <?php endif; ?>
}

// Function to report property
function reportProperty(propertyId) {
    <?php if (isset($_SESSION['email'])): ?>
        if (confirm('Are you sure you want to report this property?')) {
            fetch('ajax/report_property.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Property reported successfully. Thank you for your feedback.');
                } else {
                    alert('Error: ' + (data.message || 'Unable to report property'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while reporting the property');
            });
        }
    <?php else: ?>
        alert('Please log in to report properties');
    <?php endif; ?>
}

// Remove the problematic refreshMultipleTimes function call
// and replace with proper like functionality
</script>

        <!-- Pagination -->
        <nav aria-label="Property pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label for="reportReason" class="form-label">Reason for reporting:</label>
                            <select class="form-select" id="reportReason" required>
                                <option value="">Select a reason</option>
                                <option value="inappropriate_content">Inappropriate Content</option>
                                <option value="spam">Spam</option>
                                <option value="fake_listing">Fake Listing</option>
                                <option value="incorrect_information">Incorrect Information</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="otherReasonDiv" style="display: none;">
                            <label for="otherReason" class="form-label">Please specify:</label>
                            <textarea class="form-control" id="otherReason" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="reportPropertyId" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button  type="button" class="btn btn-danger" id="submitReport">Submit Report</button>
                </div>
            </div>
        </div>
    </div>

    
<script>
// Predefined array of curated colors
const colors = [
    "#FF5733", // Red-Orange
    "#33FF57", // Green
    "#3357FF", // Blue
    "#FF33F6", // Pink
    "#F0E130", // Yellow
    "#7D33FF", // Purple
    "#33DFFF", // Light Blue
    "#FF9E33", // Orange
    "#4CAF50", // Green (moderate)
    "#FF8C00", // Dark Orange
    "#8B00FF", // Violet
    "#FFD700", // Gold
    "#00CED1", // Dark Turquoise
    "#ADFF2F", // Green Yellow
    "#FF1493", // Deep Pink
];

// Function to randomly pick a color from the predefined array
function getRandomColor() {
    const randomIndex = Math.floor(Math.random() * colors.length);
    return colors[randomIndex];
}

// Function to change background color and apply translation every second
document.addEventListener("DOMContentLoaded", function() {
    const labels = document.querySelectorAll('.property-label');
    
    // Function to change the color and apply translation to each label
    function changeColorAndTranslate() {
        labels.forEach(function(label) {
            // Get a random color
            const randomColor = getRandomColor();
            label.style.backgroundColor = randomColor;
            label.style.color = 'white'; // Ensure the text remains readable
            
            // Apply a random translation effect (move up, down, left, right)
            const translateX = Math.floor(Math.random() * 21) - 10; // Random X translation (-10 to 10)
            const translateY = Math.floor(Math.random() * 21) - 10; // Random Y translation (-10 to 10)
            label.style.transform = `translate(${translateX}px, ${translateY}px)`;
        });
    }

    // Initially set a random color and translation
    changeColorAndTranslate();

    // Change color and translation every 1 second (1000 milliseconds)
    setInterval(changeColorAndTranslate, 1000);
});
</script>

<style>
/* Ensure smooth transition for background-color and transformation */
.property-label {
    transition: background-color 0.5s ease-in-out, transform 0.3s ease-in-out;
    margin: 5px;
    display: inline-block;
    border-radius: 5px;
    font-weight: bold;
}
</style>
    <!-- Bootstrap JS -->
    
    <!-- Polyfills for older browsers -->
    <!--[if lt IE 10]>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/classlist/1.2.20171210/classList.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flexibility/2.0.1/flexibility.js"></script>
    <script>
        flexibility(document.documentElement);
    </script>
    <![endif]-->
    
    <script>
// Function to toggle like status
function toggleLike(propertyId) {
    <?php if (isset($_SESSION['email'])): ?>
        // Add loading state
        const likeBtn = document.querySelector(`[data-property-id="${propertyId}"].like-btn`);
        if (!likeBtn) {
            console.error('Like button not found for property ID:', propertyId);
            return;
        }
        
        const originalHTML = likeBtn.innerHTML;
        likeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        likeBtn.disabled = true;
        
        fetch(window.location.pathname + window.location.search, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                property_id: parseInt(propertyId)
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            // Check if response contains HTML (which would indicate an error)
            if (text.includes('<html>') || text.includes('<!DOCTYPE')) {
                throw new Error('Received HTML instead of JSON - check server setup');
            }
            
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        })
            console.log('Parsed data:', data);
            
            if (data.success) {
                const likeIcon = likeBtn.querySelector('i');
                const likeCount = likeBtn.querySelector('.like-count');
                
                // Update the like button state
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    if (likeIcon) likeIcon.className = 'fas fa-heart';
                } else {
                    likeBtn.classList.remove('liked');
                    if (likeIcon) likeIcon.className = 'far fa-heart';
                }
                
                // Update like count
                if (likeCount) {
                    likeCount.textContent = data.like_count;
                } else {
                    // Recreate button content with like count
                    const iconClass = data.liked ? 'fas fa-heart' : 'far fa-heart';
                    likeBtn.innerHTML = `<i class="${iconClass}"></i> <span class="like-count">${data.like_count}</span>`;
                }
            } else {
                alert('Error: ' + (data.message || 'Unable to update like status'));
                // Restore original button state
                likeBtn.innerHTML = originalHTML;
            }
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating like status: ' + error.message);
            // Restore original button state
            likeBtn.innerHTML = originalHTML;
        })
        .finally(() => {
            likeBtn.disabled = false;
        });
    <?php else: ?>
        alert('Please log in to like properties');
    <?php endif; ?>
}
        // Initialize functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Sort functionality
            var select = document.querySelector('.form-select');
            if (select) {
                select.addEventListener('change', function() {
                    var sortValue = this.value;
                    if (sortValue) {
                        console.log('Sorting by:', sortValue);
                        // You can implement actual sorting here
                    }
                });
            }
            
            // Like button functionality
            document.querySelectorAll('.like-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var propertyId = this.getAttribute('data-property-id');
                    var likeBtn = this;
                    var icon = likeBtn.querySelector('i');
                    var countSpan = likeBtn.querySelector('.like-count');
                    
                    // Add loading state
                    likeBtn.classList.add('btn-loading');
                    
                    // Send AJAX request
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=like&property_id=' + propertyId
                    })
                    .then(response => response.json())
                    .then(data => {
                        likeBtn.classList.remove('btn-loading');
                        
                        if (data.success) {
                            if (data.action === 'liked') {
                                likeBtn.classList.add('liked');
                                icon.className = 'fas fa-heart';
                            } else {
                                likeBtn.classList.remove('liked');
                                icon.className = 'far fa-heart';
                            }
                            countSpan.textContent = data.like_count;
                        } else {
                            showToast(data.message || 'Please login to like properties', 'warning');
                        }
                    })
                    .catch(error => {
                        likeBtn.classList.remove('btn-loading');
                        showToast('An error occurred. Please try again.', 'danger');
                        console.error('Error:', error);
                    });
                });
            });
            
            // Report button functionality
            document.querySelectorAll('.report-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var propertyId = this.getAttribute('data-property-id');
                    document.getElementById('reportPropertyId').value = propertyId;
                    
                    var reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
                    reportModal.show();
                });
            });
            
            // Report reason change handler
            document.getElementById('reportReason').addEventListener('change', function() {
                var otherDiv = document.getElementById('otherReasonDiv');
                if (this.value === 'other') {
                    otherDiv.style.display = 'block';
                } else {
                    otherDiv.style.display = 'none';
                }
            });
            
            // Submit report
            document.getElementById('submitReport').addEventListener('click', function() {
                var propertyId = document.getElementById('reportPropertyId').value;
                var reason = document.getElementById('reportReason').value;
                var otherReason = document.getElementById('otherReason').value;
                
                if (!reason) {
                    showToast('Please select a reason for reporting', 'warning');
                    return;
                }
                
                var finalReason = reason === 'other' ? otherReason : reason;
                
                // Send AJAX request
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=report&property_id=' + propertyId + '&reason=' + encodeURIComponent(finalReason)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                        document.getElementById('reportForm').reset();
                    } else {
                        showToast(data.message, 'warning');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            });
            
            // Confirm before delete
            var deleteLinks = document.querySelectorAll('[onclick*="confirm"]');
            if (deleteLinks) {
                deleteLinks.forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
                            e.preventDefault();
                        }
                    });
                });
            }
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            var toast = document.getElementById('actionToast');
            var toastBody = toast.querySelector('.toast-body');
            var toastIcon = toast.querySelector('.toast-header i');
            
            // Set message
            toastBody.textContent = message;
            
            // Set icon based on type
            toastIcon.className = 'fas me-2';
            switch(type) {
                case 'success':
                    toastIcon.classList.add('fa-check-circle', 'text-success');
                    break;
                case 'warning':
                    toastIcon.classList.add('fa-exclamation-triangle', 'text-warning');
                    break;
                case 'danger':
                    toastIcon.classList.add('fa-exclamation-circle', 'text-danger');
                    break;
                default:
                    toastIcon.classList.add('fa-info-circle', 'text-primary');
            }
            
            // Show toast
            var bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
        
    </script>
    
   <script>
    // Save current count in sessionStorage
    function refreshMultipleTimes(times) {
      sessionStorage.setItem('refreshCount', times);
      sessionStorage.setItem('scrollPos', window.scrollY);
      location.reload();
    }

    window.addEventListener('load', () => {
      const count = parseInt(sessionStorage.getItem('refreshCount'), 10);
      const scrollPos = sessionStorage.getItem('scrollPos');

      if (scrollPos !== null) {
        window.scrollTo(0, parseInt(scrollPos, 10));
      }

      if (!isNaN(count) && count > 1) {
        sessionStorage.setItem('refreshCount', count - 1);
        setTimeout(() => {
          location.reload();
        }, 1000); // 1 second delay between refreshes
      } else {
        sessionStorage.removeItem('refreshCount');
        sessionStorage.removeItem('scrollPos');
      }
    });
  </script>
</body>
</html>