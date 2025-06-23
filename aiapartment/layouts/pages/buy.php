<?php
/**
 * HomeEasy - My Properties Page (Fixed Version)
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['page']) && $_GET['page'] === 'delete' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $host = 'localhost';
            $db = 'forfree';
            $user = 'root';
            $pass = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Delete only properties belonging to the logged-in user
            $stmt = $pdo->prepare("DELETE FROM data WHERE id = ? AND email = ?");
            $result = $stmt->execute([$id, $_SESSION['email']]);
            
            if ($result) {
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Error deleting property. Please try again.";
        }
    }
}

$pageTitle = "My Properties - HomeEasy";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 0 40px rgba(102, 126, 234, 0.15);
            --text-light: rgba(255, 255, 255, 0.9);
            --text-muted: rgba(255, 255, 255, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Enhanced Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            padding: 1rem 0;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 12px;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 5px;
            left: 50%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 1px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff !important;
        }

        /* Main Content */
        .main-content {
            padding-top: 120px;
            min-height: 100vh;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-xl);
            margin: 2rem auto;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            max-width: 1200px;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        }

        /* Page Title */
        .page-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        /* Enhanced Property Cards */
        .property-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            height: 100%;
        }

        .property-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .property-card:hover::before {
            opacity: 1;
        }

        .property-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), var(--shadow-glow);
        }

        .property-image-container {
            position: relative;
            overflow: hidden;
            height: 250px;
        }

        .property-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .property-card:hover .property-image {
            transform: scale(1.1);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .property-card:hover .image-overlay {
            opacity: 1;
        }

        .price-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--success-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }

        .property-body {
            padding: 2rem;
            color: white;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .property-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .property-details {
            display: flex;
            gap: 1.5rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .detail-item i {
            color: #4facfe;
            width: 16px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
            flex-wrap: wrap;
        }

        .btn-edit {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex: 1;
            min-width: 80px;
        }

        .btn-delete {
            background: var(--danger-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex: 1;
            min-width: 80px;
        }

        .btn-view {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex: 1;
            min-width: 80px;
        }

        .btn-edit:hover, .btn-delete:hover, .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            color: white;
        }

        /* No Image Placeholder */
        .no-image-placeholder {
            height: 250px;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.6);
            font-size: 3rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: white;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .empty-state h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 1.2rem;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .btn-add-property {
            background: var(--success-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
            text-decoration: none;
        }

        .btn-add-property:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(79, 172, 254, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Alert Styles */
        .alert-glass {
            background: rgba(220, 53, 69, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: white;
            border-radius: 16px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .main-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .page-title {
                font-size: 2.8rem;
            }

            .navbar-nav {
                padding: 1rem 0;
            }

            .nav-link {
                margin: 0.25rem 0;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding-top: 100px;
            }

            .main-container {
                padding: 2rem 1rem;
            }
            
            .page-title {
                font-size: 2.5rem;
            }
            
            .property-details {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .action-buttons .btn {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <!-- Include navbar if file exists -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="main-container">
            <h1 class="page-title" data-aos="fade-down">
                <i class="fas fa-building me-3"></i>My Properties
            </h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-glass mb-4" data-aos="fade-up">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <?php
                $host = 'localhost';
                $db = 'forfree';
                $user = 'root';
                $pass = '';
                
                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->prepare("SELECT * FROM data WHERE email = ? ORDER BY created_at DESC");
                    $stmt->execute([$_SESSION['email']]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($rows) > 0) {
                        $delay = 0;
                        foreach ($rows as $row) {
                            $images = htmlspecialchars($row['images'] ?? '');
                            $location = htmlspecialchars($row['location'] ?? 'Location not specified');
                            $sale = is_numeric($row['sale']) ? number_format($row['sale'], 0) : '0';
                            $rooms = htmlspecialchars($row['rooms'] ?? '0');
                            $bathrooms = htmlspecialchars($row['bathrooms'] ?? '0');
                            $id = intval($row['id']);
                            $delay += 100;
                            
                            echo "<div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='$delay'>
                                    <div class='card property-card h-100'>
                                        <div class='property-image-container'>";
                            
                            // Image or placeholder
                            if (!empty($images) && file_exists($images)) {
                                echo "<img src='$images' class='property-image' alt='Property Image' loading='lazy'>
                                      <div class='image-overlay'></div>";
                            } else {
                                echo "<div class='no-image-placeholder'>
                                        <i class='fas fa-home'></i>
                                      </div>";
                            }
                            
                            echo "<div class='price-badge'>
                                        <i class='fas fa-dollar-sign me-1'></i>$sale
                                  </div>
                                  </div>
                                  <div class='card-body property-body'>
                                        <h5 class='property-title'>
                                            <i class='fas fa-map-marker-alt me-2'></i>$location
                                        </h5>
                                        <div class='property-details'>
                                            <div class='detail-item'>
                                                <i class='fas fa-bed'></i>
                                                <span>$rooms Rooms</span>
                                            </div>
                                            <div class='detail-item'>
                                                <i class='fas fa-bath'></i>
                                                <span>$bathrooms Baths</span>
                                            </div>
                                        </div>
                                         <div class='action-buttons'>
                                            <a href='?page=edit&id={$row['id']}' class='btn btn-edit flex-fill'>
                                                <i class='fas fa-edit me-1'></i>Edit
                                            </a>
                                            <a href='?page=delete&id={$row['id']}' class='btn btn-delete flex-fill' 
                                               onclick=\"return confirm('Are you sure you want to delete this property?')\">
                                                <i class='fas fa-trash me-1'></i>Delete
                                            </a>
                                             <a href='?page=view&id={$row['id']}' class='btn btn-edit btn-info flex-fill' 
                                               >
                                                 <i class='fas fa-eye me-1'></i>View
                                            </a>
                                        </div>
                                    </div>
                                  </div>
                                </div>";
                        }
                    } else {
                        echo "<div class='col-12 empty-state' data-aos='fade-up'>
                                <div class='empty-state-icon'>
                                    <i class='fas fa-home'></i>
                                </div>
                                <h3>No Properties Found</h3>
                                <p>You haven't listed any properties yet. Start building your portfolio today!</p>
                                <a href='seller.php' class='btn btn-add-property'>
                                    <i class='fas fa-plus me-2'></i>Add Your First Property
                                </a>
                              </div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='col-12' data-aos='fade-up'>
                            <div class='alert alert-glass'>
                                <i class='fas fa-exclamation-triangle me-2'></i>
                                Error loading properties. Please try again later.
                            </div>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
        });

        // Property card hover effects enhancement
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Image loading optimization
        document.querySelectorAll('.property-image').forEach(img => {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
            });
            
            img.addEventListener('error', function() {
                this.closest('.property-image-container').innerHTML = `
                    <div class='no-image-placeholder'>
                        <i class='fas fa-home'></i>
                    </div>
                `;
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>