<?php
// Start session to access user data (like the logged-in user's email)

// Database configuration
$host = 'localhost';
$db = 'forfree';
$user = 'root';
$pass = '';

try {
    // Create PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get logged-in user's email from the session
    if (isset($_SESSION['email'])) {
        $user_email = $_SESSION['email'];
    } else {
        // If no email in session, show error or redirect
        header('Location: login.php');
        exit();
    }

    // SQL query to fetch property_id from property_likes where the user_email matches the logged-in user's email
    $sql = "SELECT property_id FROM property_likes WHERE user_email = :user_email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_email' => $user_email]);

    // Fetch all property_ids from property_likes
    $property_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database connection error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Liked Properties - ForFree</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .property-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 30px;
            background: white;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .property-header {
            background: linear-gradient(135deg, #6c63ff 0%, #4834d4 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .property-id {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .like-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        
        .property-body {
            padding: 25px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9ff;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .info-row:hover {
            background: #e8ecff;
            transform: translateX(5px);
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.1rem;
        }
        
        .icon-location { background: linear-gradient(135deg, #ff6b6b, #ee5a24); }
        .icon-email { background: linear-gradient(135deg, #4834d4, #6c63ff); }
        .icon-price { background: linear-gradient(135deg, #00d2d3, #01a3a4); }
        .icon-rooms { background: linear-gradient(135deg, #feca57, #ff9ff3); }
        .icon-bathroom { background: linear-gradient(135deg, #48dbfb, #0abde3); }
        .icon-space { background: linear-gradient(135deg, #1dd1a1, #10ac84); }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 2px;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        
        .price-highlight {
            color: #00d2d3;
            font-size: 1.3rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .error-alert {
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #6c63ff;
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-weight: 500;
        }
        
        .action-buttons {
            padding: 20px 25px;
            background: #f8f9ff;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #6c63ff, #4834d4);
            border: none;
            color: white;
        }
        
        .btn-outline-custom {
            border: 2px solid #6c63ff;
            color: #6c63ff;
            background: transparent;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                text-align: center;
            }
            
            .info-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
        <?php include "navbar.php"; ?>
<br/>
    <div class="container">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="header-content">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-heart text-danger"></i>
                        My Liked Properties
                    </h1>
                    <p class="lead mb-0">Discover your favorite properties in one place</p>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert error-alert" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php else: ?>
                    
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($property_ids); ?></div>
                                <div class="stats-label">Liked Properties</div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($property_ids): ?>
                        <div class="row">
                            <?php foreach ($property_ids as $property): ?>
                                <?php
                                $property_id = $property['property_id'];
                                
                                // SQL query to fetch data for each property_id
                                $data_sql = "SELECT * FROM data WHERE id = :property_id";
                                $data_stmt = $pdo->prepare($data_sql);
                                $data_stmt->execute(['property_id' => $property_id]);
                                
                                // Fetch the property data
                                $property_data = $data_stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($property_data):
                                ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card property-card">
                                        <div class="property-header">
                                            <h3 class="property-id">Property #<?php echo htmlspecialchars($property_data['id']); ?></h3>
                                            <div class="like-badge">
                                                <i class="fas fa-heart text-danger"></i> Liked
                                            </div>
                                        </div>
                                        
                                        <div class="property-body">
                                            <div class="info-row">
                                                <div class="info-icon icon-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Location</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($property_data['location']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="info-row">
                                                <div class="info-icon icon-email">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Owner</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($property_data['email']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="info-row">
                                                <div class="info-icon icon-price">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Price</div>
                                                    <div class="info-value price-highlight">$<?php echo number_format(floatval($property_data['sale']), 2); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-4">
                                                    <div class="info-row">
                                                        <div class="info-icon icon-rooms">
                                                            <i class="fas fa-bed"></i>
                                                        </div>
                                                        <div class="info-content">
                                                            <div class="info-label">Rooms</div>
                                                            <div class="info-value"><?php echo htmlspecialchars($property_data['rooms']); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-4">
                                                    <div class="info-row">
                                                        <div class="info-icon icon-bathroom">
                                                            <i class="fas fa-bath"></i>
                                                        </div>
                                                        <div class="info-content">
                                                            <div class="info-label">Baths</div>
                                                            <div class="info-value"><?php echo htmlspecialchars($property_data['bathrooms']); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-4">
                                                    <div class="info-row">
                                                        <div class="info-icon icon-space">
                                                            <i class="fas fa-expand-arrows-alt"></i>
                                                        </div>
                                                        <div class="info-content">
                                                            <div class="info-label">Space</div>
                                                            <div class="info-value"><?php echo htmlspecialchars($property_data['space']); ?> m²</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="action-buttons">
                                            <a href="?page=view&id=<?= $property_id ?>" class="btn btn-primary-custom btn-custom flex-fill">
                                                <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                      
                                <div class='d-flex gap-2 mb-2'>
        <a href="?page=maps&id=<?= $property_id ?>&lat=<?php echo htmlspecialchars($property_data['latitude']); ?>&lng=<?php echo htmlspecialchars($property_data['longitude']); ?>" class='btn btn-primary btn-sm flex-fill'>
            <i class='fas fa-map me-1'></i> View on Map
        </a>
      </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-heart-broken"></i>
                            </div>
                            <h3>No Liked Properties Yet</h3>
                            <p class="text-muted">Start exploring properties and like the ones you love!</p>
                            <a href="?page=sell" class="btn btn-primary-custom btn-custom mt-3">
                                <i class="fas fa-search me-2"></i>Browse Properties
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
<br/>
<br/>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe all property cards
            document.querySelectorAll('.property-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
            
            // Add click handlers for action buttons
            document.querySelectorAll('.btn-primary-custom').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Add your view details logic here
                    console.log('View details clicked');
                });
            });
            
            document.querySelectorAll('.btn-outline-custom').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Add your unlike property logic here
                    console.log('Unlike property clicked');
                });
            });
        });
    </script>
</body>
</html>