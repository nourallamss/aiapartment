<?php
// Start the session first

// Database connection
$host = 'localhost';
$dbname = 'forfree';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Define admin emails
$admin_emails = ['admin@homeeasy.com', 'support@homeeasy.com'];

// Initialize profile photo and admin check variables
$profile_photo = null;
$is_admin = false;

// Only try to fetch profile photo and check admin status if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT profile_photo, email FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        if (!empty($result['profile_photo'])) {
            $profile_photo = $result['profile_photo'];
        }
        
        // Check if user is admin
        if (in_array($result['email'], $admin_emails)) {
            $is_admin = true;
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Custom styles for better responsiveness and dropdown centering */
@media (max-width: 991.98px) {
    .navbar-nav {
        text-align: center;
        margin-top: 1rem;
    }
    
    .nav-item {
        margin: 0.25rem 0;
    }
    
    .mobile-auth-buttons {
        justify-content: center !important;
        margin-top: 1rem;
        gap: 0.5rem;
    }
    
    .mobile-profile {
        justify-content: center !important;
        margin-top: 1rem;
    }
    
    .dropdown-menu {
        position: absolute !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        margin-top: 0.5rem;
    }
}

@media (min-width: 992px) {
    .dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
    }
}

.navbar-brand {
    font-size: 1.5rem;
}

.profile-image {
    object-fit: cover;
    border: 2px solid #e9ecef;
}

.profile-placeholder {
    background: linear-gradient(135deg, #6c757d, #495057);
}

.dropdown-toggle::after {
    margin-left: 0.5rem;
}

/* Smooth transitions */
.dropdown-menu {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    margin-top: 0.25rem;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

/* Better mobile button styling */
@media (max-width: 575.98px) {
    .btn {
        width: 100%;
        margin: 0.25rem 0;
    }
    
    .mobile-auth-buttons {
        flex-direction: column !important;
        width: 100%;
    }
    
    .navbar-brand {
        font-size: 1.25rem;
    }
}
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">🏠 HomeEasy</a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=buy">🏘️ Properties</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=rent">🏠 Rent</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=sell">💰 Sell</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=feedback">💬 Feedback</a>
                </li>
                
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=agent">👨‍💼 Find Agent</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="?page=admin_reports">📊 Admin Reports</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="d-flex align-items-center mobile-auth-buttons mobile-profile">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Profile Photo Dropdown -->
                    <div class="dropdown">
                        <a class="dropdown-toggle d-flex align-items-center text-decoration-none p-2 rounded-3 hover-bg-light" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false"
                           style="transition: background-color 0.2s ease;">
                            
                            <?php if ($profile_photo): ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" 
                                     alt="Profile" 
                                     class="rounded-circle me-2 profile-image" 
                                     width="40" 
                                     height="40">
                            <?php else: ?>
                                <div class="rounded-circle profile-placeholder d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px;">
                                    <span class="text-white fs-5">👤</span>
                                </div>
                            <?php endif; ?>
                            
                            <span class="d-none d-sm-inline text-dark fw-medium">
                                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>
                            </span>
                        </a>
                        
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="?page=profile">
                                    <span class="me-2">👤</span>View Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="?page=settings">
                                    <span class="me-2">⚙️</span>Settings
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item d-flex align-items-center" href="?page=liked">
                                    <span class="me-2">❤</span>Liked Property
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="?page=buy">
                                    <span class="me-2">🏠</span>My Properties
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center text-danger" href="?action=logout">
                                    <span class="me-2">🚪</span>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-outline-primary me-2">Login</a>
                    <a href="?page=register" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
// Add hover effects and smooth interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effect to dropdown toggle
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    if (dropdownToggle) {
        dropdownToggle.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        
        dropdownToggle.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.dropdown');
        if (dropdown && !dropdown.contains(event.target)) {
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle')).hide();
            }
        }
    });
});
</script>