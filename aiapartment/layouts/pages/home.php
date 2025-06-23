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
<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve latitude, longitude, and address from the POST data
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $address = $_POST['location_address'];

        // You can now use these values (e.g., store them in a database)
        echo "Latitude: " . $latitude . "<br>";
        echo "Longitude: " . $longitude . "<br>";
        echo "Address: " . $address . "<br>";
    }
?>

    <style>
         .element {
            background-image: url("https://images.squarespace-cdn.com/content/v1/60dd579702d1a17b631cc350/8e0967c7-5602-4430-a19d-8aaabfe845ca/beach-villa_BoAo-Residential-Resort.jpg");
        }

        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f97316;
            --light-bg: #f8fafc;
            --dark-text: #0f172a;
            --light-text: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            background-color: var(--light-bg);
        }
        
        /* Header */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            background-color: white;
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .nav-link {
            font-weight: 600;
            color: var(--dark-text);
            margin: 0 0.5rem;
        }
        
        /* Hero Section */
        .hero {
            background-size: cover;
            background-position: center;
            padding: 4rem 0;
            position: relative;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3));
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        /* Search Box */
        .search-box {
            background-color: white;
            border-radius: 8px;
            padding: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .search-input {
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            width: 100%;
            outline: none;
        }
        
        .search-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .search-button:hover {
            background-color: var(--secondary-color);
        }
        
        /* Features Section */
        .features {
            padding: 4rem 0;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            background-color: rgba(37, 99, 235, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .feature-icon i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        /* Property Section */
        .properties {
            padding: 4rem 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: var(--light-text);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .property-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .property-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .property-content {
            padding: 1.5rem;
        }
        
        .property-sale {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .property-address {
            color: var(--light-text);
            margin-bottom: 1rem;
        }
        
        .property-details {
            display: flex;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .property-detail {
            display: flex;
            align-items: center;
        }
        
        .property-detail i {
            margin-right: 0.5rem;
            color: var(--light-text);
        }
        
        /* CTA Section */
        .cta {
            padding: 4rem 0;
            background-color: var(--primary-color);
            color: white;
        }
        
        .cta h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta-button {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 4px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .cta-button:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
        }
        
        /* User Welcome Box */
        .welcome-box {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Footer */
        footer {
            background-color: #0f172a;
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer-logo {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .footer-links h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
        }
        
        .social-links {
            margin-top: 1rem;
        }
        
        .social-links a {
            color: white;
            margin: 0 0.5rem;
            font-size: 1.25rem;
        }
    </style>
     <style>
        /* Property Slider Section */
        .property-slider-section {
            padding: 4rem 0;
            background-color: var(--light-bg);
        }
        
        .property-slider-container {
            position: relative;
            padding: 0 50px;
        }
        
        .property-slider {
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            gap: 20px;
            padding: 20px 0;
        }
        
        .property-slide {
            min-width: calc(25% - 15px);
            flex: 0 0 calc(25% - 15px);
            transition: transform 0.3s ease;
        }
        
        .property-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            background-color: white;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .property-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .property-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .property-content {
            padding: 1.5rem;
        }
        
        .property-sale {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .property-address {
            color: var(--light-text);
            margin-bottom: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .property-details {
            display: flex;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .property-detail {
            display: flex;
            align-items: center;
        }
        
        .property-detail i {
            margin-right: 0.5rem;
            color: var(--light-text);
        }
        
        .slider-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: background-color 0.3s ease;
        }
        
        .slider-control:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .slider-prev {
            left: 0;
        }
        
        .slider-next {
            right: 0;
        }
        
        .slider-indicators {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 8px;
        }
        
        .slider-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #cbd5e1;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .slider-indicator.active {
            background-color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 1199.98px) {
            .property-slide {
                min-width: calc(33.333% - 14px);
                flex: 0 0 calc(33.333% - 14px);
            }
        }
        
        @media (max-width: 991.98px) {
            .property-slide {
                min-width: calc(50% - 10px);
                flex: 0 0 calc(50% - 10px);
            }
        }
        
        @media (max-width: 575.98px) {
            .property-slide {
                min-width: 100%;
                flex: 0 0 100%;
            }
            
            .property-slider-container {
                padding: 0 30px;
            }
        }
    </style>
    <style>
        .property-slider-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .section-title p {
            font-size: 16px;
            color: #777;
        }
        
        .property-slider-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .property-slider {
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            width: 100%;
        }
        
        .property-slide {
            flex: 0 0 33.333%;
            max-width: 33.333%;
            padding: 0 15px;
            transition: transform 0.3s ease;
        }
        
        .property-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .property-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-image img {
            transform: scale(1.05);
        }
        
        .property-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0, 123, 255, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 1;
        }
        
        .property-content {
            padding: 20px;
        }
        
        .property-sale {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .property-address {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
        }
        
        .property-details {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .property-detail {
            display: flex;
            align-items: center;
            color: #555;
        }
        
        .property-detail i {
            margin-right: 5px;
            color: #007bff;
        }
        
        .slider-control {
            background-color: #fff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #333;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease;
            z-index: 2;
        }
        
        .slider-control:hover {
            background-color: #007bff;
            color: #fff;
        }
        
        .slider-indicators {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .slider-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ddd;
            margin: 0 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .slider-indicator.active {
            background-color: #007bff;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .property-slide {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        @media (max-width: 768px) {
            .property-slide {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        /* Hero Section Styles */


/* Search Container */
.search-container {
    background: white !important;
    border-radius: 15px !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
    padding: 2rem !important;
    margin-top: 2rem;
}

/* Search Tabs */
.search-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 1.5rem;
}

.tab-btn {
    padding: 12px 24px;
    border: 2px solid transparent;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.tab-btn.active {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.tab-btn:not(.active) {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    border-color: #e9ecef !important;
}

.tab-btn:not(.active):hover {
    background-color: #e9ecef !important;
    color: #495057 !important;
}

/* Form Styling */
.search-box .row {
    --bs-gutter-x: 0.5rem;
}

.form-group {
    margin-bottom: 0;
}

.input-group {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 12px 15px;
}

.form-control, .form-select {
    border: 1px solid #dee2e6;
    padding: 12px 15px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
    border-radius: 8px;
}

/* Search Button */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    padding: 12px 20px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

/* Advanced Search Link */
.search-container .mt-3 a {
    color: #28a745;
    font-weight: 500;
    transition: color 0.3s ease;
}

.search-container .mt-3 a:hover {
    color: #218838;
    text-decoration: underline !important;
}

.search-container .mt-3 i {
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
 
    
    .search-container {
        padding: 1.5rem !important;
    }
    
    .search-box .row {
        --bs-gutter-x: 0.25rem;
    }
    
    .search-box .col-md-4,
    .search-box .col-md-2,
    .search-box .col-md-1 {
        margin-bottom: 1rem;
    }
    
    .tab-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .search-tabs {
        flex-direction: column;
        gap: 8px;
    }
    
    .tab-btn {
        width: 100%;
        text-align: center;
    }
    
    .search-box .row > * {
        width: 100%;
        margin-bottom: 1rem;
    }
}

/* Fix for duplicate property_type selects */
.search-box select[name="property_type"]:first-of-type {
    /* First select - Ready/Off-Plan */
}

.search-box select[name="property_type"]:last-of-type {
    /* Second select - Apartment/Villa/Townhouse - should probably be renamed */
}

/* Animation for search container */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-container {
    animation: slideUp 0.8s ease-out;
}

/* Improved form field spacing */
.search-box .form-control,
.search-box .form-select {
    height: 48px;
}

/* Better button alignment */
.search-box .btn {
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Enhanced focus states */
.form-control:focus,
.form-select:focus {
    outline: none;
    border-width: 2px;
}

/* Loading state for search button */
.btn-success:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Improved placeholder styling */
.form-control::placeholder {
    color: #9ca3af;
    opacity: 1;
}

/* Better visual hierarchy */
.search-container h3 {
    color: #1f2937;
    margin-bottom: 1rem;
}

/* Accessibility improvements */
.tab-btn:focus,
.form-control:focus,
.form-select:focus,
.btn:focus {
    outline: 2px solid #28a745;
    outline-offset: 2px;
}
    </style>
    <style>
        /* Search Tabs Styling */
        .search-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 1.5rem;
            background: #f8f9fa;
            padding: 6px;
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: transparent;
            color: #6c757d;
        }

        /* Active Tab Styling */
        .tab-btn.active {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            color: white;
            box-shadow: 
                0 4px 12px rgba(13, 110, 253, 0.3),
                0 2px 4px rgba(13, 110, 253, 0.2);
            transform: translateY(-1px);
        }

        /* Inactive Tab Hover */
        .tab-btn:not(.active):hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        /* Ripple Effect */
        .tab-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .tab-btn.active::before {
            width: 300px;
            height: 300px;
        }

        /* Tab Icons */
        .tab-btn i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

        /* Loading State */
        .tab-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .tab-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Focus States for Accessibility */
        .tab-btn:focus {
            outline: 3px solid rgba(13, 110, 253, 0.3);
            outline-offset: 2px;
        }

        /* Mobile Responsive */
        @media (max-width: 576px) {
            .search-tabs {
                flex-direction: column;
                gap: 6px;
                padding: 4px;
            }
            
            .tab-btn {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }

        /* Demo Container Styling */
        .demo-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .tab-content {
            margin-top: 2rem;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #0d6efd;
        }

        .tab-content.hidden {
            display: none;
        }

      

        h2 {
            color: #1a1a1a;
            margin-bottom: 2rem;
            font-weight: 700;
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #2196f3;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list i {
            color: #28a745;
            margin-right: 0.5rem;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <?php include "navbar.php";?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Welcome Box -->
    <div class="container mt-4">
        <div class="welcome-box">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</h3>
                    <p class="mb-0">Continue exploring properties or update your preferences to find your perfect match.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="form.php" class="btn btn-primary">Update Preferences</a>
                </div>
            </div>
        </div>
    </div>


<!-- Hero Section -->
<section class="hero element">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 hero-content">
                <h1>Find the right home at the right sale</h1>
                <p>Search homes across the country and get personalized recommendations based on your preferences.</p>

                <!-- Search Box Form with Tabs -->
                <div class="search-container bg-white rounded shadow p-3">
                    <!-- Tabs -->
                     <div class="search-tabs mb-3">
            <button type="button" class="tab-btn active btn btn-primary me-2" data-tab="buy">
                <i class="fas fa-key"></i>Buy
            </button>
            <button type="button" class="tab-btn btn btn-primary" data-tab="rent">
                <i class="fas fa-home"></i>Rent
            </button>
        </div>

                    <!-- Search Form -->
                    <form method="GET" action="" class="search-box">
                        <div class="row g-2 align-items-end">
                            <!-- Location Input -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <input id="location_address" type="text" class="form-control" name="location" placeholder="Enter location" 
                                               value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>" >
                                    </div>
                                </div>
                            </div>

                            <!-- Property Type Filter -->
                            <div class="col-md-2">
                                <select class="form-select" name="property_type">
                                    <option value="">All Types</option>
                                    <option value="ready" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'ready') ? 'selected' : ''; ?>>Ready</option>
                                    <option value="off-plan" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'off-plan') ? 'selected' : ''; ?>>Off-Plan</option>
                                </select>
                            </div>

                            <!-- property_type Filter -->
                            <div class="col-md-2">
                                <select class="form-select" name="property_type">
                                    <option value="">All Categories</option>
                                    <option value="apartment" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="villa" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
                                    <option value="townhouse" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'townhouse') ? 'selected' : ''; ?>>Townhouse</option>
                                    <option value="Studio" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'Studio') ? 'selected' : ''; ?>>Studio</option>

                                </select>
                            </div>

                            <!-- Rooms Filter -->
                            <div class="col-md-2">
                                <select class="form-select" name="rooms">
                                    <option value="">All Rooms</option>
                                    <option value="1" <?php echo (isset($_GET['rooms']) && $_GET['rooms'] == '1') ? 'selected' : ''; ?>>1 Room</option>
                                    <option value="2" <?php echo (isset($_GET['rooms']) && $_GET['rooms'] == '2') ? 'selected' : ''; ?>>2 Rooms</option>
                                    <option value="3" <?php echo (isset($_GET['rooms']) && $_GET['rooms'] == '3') ? 'selected' : ''; ?>>3 Rooms</option>
                                    <option value="4" <?php echo (isset($_GET['rooms']) && $_GET['rooms'] == '4') ? 'selected' : ''; ?>>4 Rooms</option>
                                    <option value="5+" <?php echo (isset($_GET['rooms']) && $_GET['rooms'] == '5+') ? 'selected' : ''; ?>>5+ Rooms</option>
                                </select>
                            </div>

                            <!-- sale Filter -->
                            <div class="col-md-1">
                                <select class="form-select" name="sale">
                                    <option value="">sale (EGP)</option>
                                    <option value="low" <?php echo (isset($_GET['sale']) && $_GET['sale'] == 'low') ? 'selected' : ''; ?>>Under 1M</option>
                                    <option value="mid" <?php echo (isset($_GET['sale']) && $_GET['sale'] == 'mid') ? 'selected' : ''; ?>>1M - 5M</option>
                                    <option value="high" <?php echo (isset($_GET['sale']) && $_GET['sale'] == 'high') ? 'selected' : ''; ?>>5M+</option>
                                </select>
                            </div>
                            <div class="mb-3">
    <label class="form-label">📍 Select Location on Map:</label>
    <div id="locationMap" style="height: 300px; border-radius: 10px; border: 2px solid #e3e6f0;"></div>
    <div class="form-text">Click on the map to set the property location. Coordinates will be saved with your listing.</div>
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
</div>

                            <!-- Search Button -->
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    Search
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Search Link -->
                        <div class="mt-3">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-car me-2"></i>Search 2.0 Find homes by drive time
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Initialize map variables
let map;
let marker;
let geocodeTimeout;

// Initialize the map
function initMap() {
    // Default location (Cairo, Egypt)
    const defaultLat = 30.0444;
    const defaultLng = 31.2357;
    
    map = L.map('locationMap').setView([defaultLat, defaultLng], 10);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add click event to map
    map.on('click', function(e) {
        updateMapMarker(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

// Update map marker
function updateMapMarker(lat, lng) {
    if (marker) {
        map.removeLayer(marker);
    }
    
    marker = L.marker([lat, lng]).addTo(map);
    map.setView([lat, lng], 13);
    
    // Update hidden fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
}

// Geocode address to coordinates
function geocodeAddress(address) {
    if (!address || address.length < 3) return;
    
    // Show loading indicator
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'block';
    }
    
    // Use Nominatim geocoding service (free)
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&countrycodes=eg`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                updateMapMarker(lat, lng);
            }
        })
        .catch(error => {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            console.error('Geocoding error:', error);
        });
}

// Reverse geocode coordinates to address
function reverseGeocode(lat, lng) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                const locationInput = document.getElementById('location_address');
                if (locationInput) {
                    locationInput.value = data.display_name;
                }
            }
        })
        .catch(error => {
            console.error('Reverse geocoding error:', error);
        });
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();
    
    // Add input event listener for auto-update
    const locationInput = document.getElementById('location_address');
    
    if (locationInput) {
        locationInput.addEventListener('input', function() {
            const address = this.value.trim();
            
            // Clear previous timeout
            if (geocodeTimeout) {
                clearTimeout(geocodeTimeout);
            }
            
            // Set new timeout for geocoding (wait 800ms after user stops typing)
            if (address.length >= 3) {
                geocodeTimeout = setTimeout(() => {
                    geocodeAddress(address);
                }, 800);
            }
        });
        
        // If there's an initial value, geocode it
        const initialValue = locationInput.value.trim();
        if (initialValue.length >= 3) {
            setTimeout(() => {
                geocodeAddress(initialValue);
            }, 1000);
        }
    }
});
</script>
<script>
        class SearchTabs {
            constructor() {
                this.init();
            }

            init() {
                this.bindEvents();
                this.setInitialState();
            }

            bindEvents() {
                // Get all tab buttons
                const tabButtons = document.querySelectorAll('.tab-btn');
                
                // Add click event listeners
                tabButtons.forEach(button => {
                    button.addEventListener('click', (e) => this.handleTabClick(e));
                    
                    // Add keyboard support
                    button.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.handleTabClick(e);
                        }
                    });
                });

                // Add keyboard navigation (arrow keys)
                document.addEventListener('keydown', (e) => {
                    if (e.target.classList.contains('tab-btn')) {
                        this.handleKeyboardNavigation(e);
                    }
                });
            }

            handleTabClick(event) {
                const clickedTab = event.currentTarget;
                const tabType = clickedTab.getAttribute('data-tab');
                
                // Prevent double-clicking active tab
                if (clickedTab.classList.contains('active')) {
                    return;
                }

                // Add loading state (optional)
                this.setLoadingState(clickedTab, true);

                // Simulate loading delay (remove in production)
                setTimeout(() => {
                    this.switchTab(tabType);
                    this.setLoadingState(clickedTab, false);
                }, 300);
            }

            switchTab(activeTabType) {
                const allTabs = document.querySelectorAll('.tab-btn');
                const allContent = document.querySelectorAll('.tab-content');

                // Update tab buttons
                allTabs.forEach(tab => {
                    const tabType = tab.getAttribute('data-tab');
                    
                    if (tabType === activeTabType) {
                        tab.classList.add('active');
                        tab.setAttribute('aria-selected', 'true');
                    } else {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                    }
                });

                // Update content visibility
                allContent.forEach(content => {
                    const contentId = content.id.replace('-content', '');
                    
                    if (contentId === activeTabType) {
                        content.classList.remove('hidden');
                        content.setAttribute('aria-hidden', 'false');
                    } else {
                        content.classList.add('hidden');
                        content.setAttribute('aria-hidden', 'true');
                    }
                });

                // Trigger custom event
                this.dispatchTabChangeEvent(activeTabType);
            }

            setLoadingState(button, isLoading) {
                if (isLoading) {
                    button.classList.add('loading');
                    button.setAttribute('disabled', 'true');
                } else {
                    button.classList.remove('loading');
                    button.removeAttribute('disabled');
                }
            }

            handleKeyboardNavigation(event) {
                const currentTab = event.target;
                const allTabs = Array.from(document.querySelectorAll('.tab-btn'));
                const currentIndex = allTabs.indexOf(currentTab);

                let newIndex;

                switch (event.key) {
                    case 'ArrowLeft':
                        event.preventDefault();
                        newIndex = currentIndex > 0 ? currentIndex - 1 : allTabs.length - 1;
                        break;
                    case 'ArrowRight':
                        event.preventDefault();
                        newIndex = currentIndex < allTabs.length - 1 ? currentIndex + 1 : 0;
                        break;
                    case 'Home':
                        event.preventDefault();
                        newIndex = 0;
                        break;
                    case 'End':
                        event.preventDefault();
                        newIndex = allTabs.length - 1;
                        break;
                    default:
                        return;
                }

                allTabs[newIndex].focus();
            }

            setInitialState() {
                // Set ARIA attributes
                const tabButtons = document.querySelectorAll('.tab-btn');
                tabButtons.forEach((button, index) => {
                    button.setAttribute('role', 'tab');
                    button.setAttribute('aria-selected', button.classList.contains('active') ? 'true' : 'false');
                    button.setAttribute('tabindex', button.classList.contains('active') ? '0' : '-1');
                });

                // Set content ARIA attributes
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => {
                    content.setAttribute('role', 'tabpanel');
                    content.setAttribute('aria-hidden', content.classList.contains('hidden') ? 'true' : 'false');
                });
            }

            dispatchTabChangeEvent(tabType) {
                const event = new CustomEvent('tabChanged', {
                    detail: { activeTab: tabType },
                    bubbles: true
                });
                document.dispatchEvent(event);
            }

            // Public method to programmatically switch tabs
            activateTab(tabType) {
                this.switchTab(tabType);
            }

            // Public method to get current active tab
            getActiveTab() {
                const activeButton = document.querySelector('.tab-btn.active');
                return activeButton ? activeButton.getAttribute('data-tab') : null;
            }
        }

        // Initialize tabs when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.searchTabs = new SearchTabs();

            // Example of listening to tab change events
            document.addEventListener('tabChanged', (event) => {
                console.log('Tab changed to:', event.detail.activeTab);
                
                // You can add custom logic here, like:
                // - Update form fields
                // - Change search parameters
                // - Track analytics
                // - Update URL
            });
        });

        // Example usage functions (you can remove these in production)
        function switchToBuy() {
            window.searchTabs.activateTab('buy');
        }

        function switchToRent() {
            window.searchTabs.activateTab('rent');
        }

        function getCurrentTab() {
            console.log('Current tab:', window.searchTabs.getActiveTab());
        }
    </script>
    <script>
// Map functionality
let map, marker;

function initMap() {
    const defaultLat = 30.0444;
    const defaultLng = 31.2357;
    
    map = L.map('locationMap').setView([defaultLat, defaultLng], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    map.on('click', function(e) {
        updateMapMarker(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

function updateMapMarker(lat, lng) {
    if (marker) {
        map.removeLayer(marker);
    }
    
    marker = L.marker([lat, lng]).addTo(map);
    map.setView([lat, lng], 13);
    
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}

function reverseGeocode(lat, lng) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('location').value = data.display_name;
            }
        })
        .catch(error => console.error('Reverse geocoding error:', error));
}

function geocodeAddress() {
    const address = document.getElementById('location').value.trim();
    if (!address || address.length < 3) return;
    
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&countrycodes=eg`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                updateMapMarker(lat, lng);
            }
        })
        .catch(error => console.error('Geocoding error:', error));
}

// Form functionality
function clearForm() {
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    if (marker) {
        map.removeLayer(marker);
    }
}

// Property interaction functions
function saveProperty(propertyId) {
    // Save property to favorites
    fetch('?action=save_property', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({property_id: propertyId})
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Property saved successfully!');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving property');
    });
}

function shareProperty(propertyId) {
    const url = window.location.origin + '?page=property&id=' + propertyId;
    if (navigator.share) {
        navigator.share({
            title: 'Check out this property',
            text: 'Found an interesting property you might like',
            url: url
        });
    } else {
        // Fallback for browsers without Web Share API
        navigator.clipboard.writeText(url).then(() => {
            alert('Property link copied to clipboard!');
        }).catch(() => {
            prompt('Copy this link to share:', url);
        });
    }
}

function contactAgent(propertyId) {
    // Open contact modal or redirect to contact page
    window.open('?page=contact&property_id=' + propertyId, '_blank', 'width=600,height=400');
}

// Auto-complete location search
function setupLocationAutocomplete() {
    const locationInput = document.getElementById('location');
    let searchTimeout;
    
    locationInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(() => {
                geocodeAddress();
            }, 500);
        }
    });
}

// Price range calculator
function setupPriceRangeSync() {
    const priceRangeSelect = document.getElementById('price_range');
    const minPriceInput = document.getElementById('min_price');
    const maxPriceInput = document.getElementById('max_price');
    
    priceRangeSelect.addEventListener('change', function() {
        const range = this.value;
        
        // Clear custom inputs when selecting predefined range
        if (range) {
            minPriceInput.value = '';
            maxPriceInput.value = '';
        }
    });
    
    // Clear predefined range when using custom inputs
    [minPriceInput, maxPriceInput].forEach(input => {
        input.addEventListener('input', function() {
            if (this.value) {
                priceRangeSelect.value = '';
            }
        });
    });
}

// Advanced search toggle
function toggleAdvancedSearch() {
    const advancedSection = document.getElementById('advanced-search');
    const toggleButton = document.getElementById('advanced-toggle');
    
    if (advancedSection.style.display === 'none') {
        advancedSection.style.display = 'block';
        toggleButton.textContent = 'Hide Advanced Search';
    } else {
        advancedSection.style.display = 'none';
        toggleButton.textContent = 'Show Advanced Search';
    }
}

// Search history management
function saveSearchToHistory() {
    const searchParams = new URLSearchParams(window.location.search);
    const searchData = {
        timestamp: new Date().toISOString(),
        parameters: Object.fromEntries(searchParams),
        results_count: document.querySelectorAll('article').length
    };
    
    let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
    searchHistory.unshift(searchData);
    
    // Keep only last 10 searches
    searchHistory = searchHistory.slice(0, 10);
    
    localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
}

// Load search history
function loadSearchHistory() {
    const searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
    const historyContainer = document.getElementById('search-history');
    
    if (historyContainer && searchHistory.length > 0) {
        historyContainer.innerHTML = '<h4>Recent Searches:</h4>';
        const historyList = document.createElement('ul');
        
        searchHistory.forEach((search, index) => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            
            const params = new URLSearchParams(search.parameters);
            link.href = '?' + params.toString();
            
            let searchDescription = '';
            if (search.parameters.location) {
                searchDescription += `Location: ${search.parameters.location}`;
            }
            if (search.parameters.property_type) {
                searchDescription += ` | Type: ${search.parameters.property_type}`;
            }
            if (search.parameters.price_range) {
                searchDescription += ` | Price: ${search.parameters.price_range}`;
            }
            
            link.textContent = searchDescription || 'General Search';
            
            const resultCount = document.createElement('small');
            resultCount.textContent = ` (${search.results_count} results)`;
            
            const timestamp = document.createElement('small');
            timestamp.textContent = ` - ${new Date(search.timestamp).toLocaleString()}`;
            
            listItem.appendChild(link);
            listItem.appendChild(resultCount);
            listItem.appendChild(timestamp);
            historyList.appendChild(listItem);
        });
        
        historyContainer.appendChild(historyList);
    }
}

// Comparison functionality
let comparisonList = JSON.parse(localStorage.getItem('propertyComparison') || '[]');

function addToComparison(propertyId) {
    if (comparisonList.includes(propertyId)) {
        alert('Property already in comparison list');
        return;
    }
    
    if (comparisonList.length >= 4) {
        alert('Maximum 4 properties can be compared at once');
        return;
    }
    
    comparisonList.push(propertyId);
    localStorage.setItem('propertyComparison', JSON.stringify(comparisonList));
    
    updateComparisonCounter();
    alert('Property added to comparison');
}

function removeFromComparison(propertyId) {
    comparisonList = comparisonList.filter(id => id !== propertyId);
    localStorage.setItem('propertyComparison', JSON.stringify(comparisonList));
    updateComparisonCounter();
}

function updateComparisonCounter() {
    const counter = document.getElementById('comparison-counter');
    if (counter) {
        counter.textContent = comparisonList.length;
        counter.style.display = comparisonList.length > 0 ? 'inline' : 'none';
    }
}

function viewComparison() {
    if (comparisonList.length < 2) {
        alert('Please add at least 2 properties to compare');
        return;
    }
    
    const comparisonUrl = '?page=compare&properties=' + comparisonList.join(',');
    window.open(comparisonUrl, '_blank');
}

// Mortgage calculator
function calculateMortgage() {
    const price = parseFloat(document.getElementById('calc-price').value);
    const downPayment = parseFloat(document.getElementById('calc-down-payment').value);
    const interestRate = parseFloat(document.getElementById('calc-interest-rate').value) / 100 / 12;
    const loanTerm = parseInt(document.getElementById('calc-loan-term').value) * 12;
    
    if (!price || !downPayment || !interestRate || !loanTerm) {
        alert('Please fill in all mortgage calculator fields');
        return;
    }
    
    const loanAmount = price - downPayment;
    const monthlyPayment = (loanAmount * interestRate * Math.pow(1 + interestRate, loanTerm)) / 
                          (Math.pow(1 + interestRate, loanTerm) - 1);
    
    const totalPayment = monthlyPayment * loanTerm;
    const totalInterest = totalPayment - loanAmount;
    
    const resultDiv = document.getElementById('mortgage-result');
    resultDiv.innerHTML = `
        <h4>Mortgage Calculation Results:</h4>
        <p><strong>Loan Amount:</strong> EGP ${loanAmount.toLocaleString()}</p>
        <p><strong>Monthly Payment:</strong> EGP ${monthlyPayment.toLocaleString()}</p>
        <p><strong>Total Payment:</strong> EGP ${totalPayment.toLocaleString()}</p>
        <p><strong>Total Interest:</strong> EGP ${totalInterest.toLocaleString()}</p>
    `;
}

// Search suggestions
function setupSearchSuggestions() {
    const locationInput = document.getElementById('location');
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.id = 'search-suggestions';
    suggestionsContainer.style.display = 'none';
    locationInput.parentNode.appendChild(suggestionsContainer);
    
    locationInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length >= 2) {
            fetchLocationSuggestions(query);
        } else {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

function fetchLocationSuggestions(query) {
    // Simulate API call for location suggestions
    const suggestions = [
        'New Cairo', 'Maadi', '6th of October', 'Alexandria', 'Giza',
        'Heliopolis', 'Zamalek', 'Dokki', 'Mohandessin', 'Nasr City'
    ].filter(location => location.toLowerCase().includes(query.toLowerCase()));
    
    displaySuggestions(suggestions);
}

function displaySuggestions(suggestions) {
    const suggestionsContainer = document.getElementById('search-suggestions');
    
    if (suggestions.length === 0) {
        suggestionsContainer.style.display = 'none';
        return;
    }
    
    suggestionsContainer.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const suggestionItem = document.createElement('div');
        suggestionItem.textContent = suggestion;
        suggestionItem.style.cursor = 'pointer';
        suggestionItem.style.padding = '5px';
        suggestionItem.style.borderBottom = '1px solid #eee';
        
        suggestionItem.addEventListener('click', function() {
            document.getElementById('location').value = suggestion;
            suggestionsContainer.style.display = 'none';
            geocodeAddress();
        });
        
        suggestionsContainer.appendChild(suggestionItem);
    });
    
    suggestionsContainer.style.display = 'block';
    suggestionsContainer.style.position = 'absolute';
    suggestionsContainer.style.backgroundColor = 'white';
    suggestionsContainer.style.border = '1px solid #ccc';
    suggestionsContainer.style.maxHeight = '200px';
    suggestionsContainer.style.overflowY = 'auto';
    suggestionsContainer.style.zIndex = '1000';
}

// Initialize all functionality
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    setupLocationAutocomplete();
    setupPriceRangeSync();
    setupSearchSuggestions();
    loadSearchHistory();
    updateComparisonCounter();
    
    // Save search to history if this is a search results page
    if (window.location.search.includes('search=1')) {
        saveSearchToHistory();
    }
    
    // Add comparison buttons to property cards
    const propertyCards = document.querySelectorAll('article');
    propertyCards.forEach(card => {
        const propertyId = card.querySelector('a[href*="property&id="]')?.href.split('id=')[1];
        if (propertyId) {
            const compareBtn = document.createElement('button');
            compareBtn.innerHTML = '<i class="fas fa-balance-scale"></i> Compare';
            compareBtn.onclick = () => addToComparison(propertyId);
            
            const footer = card.querySelector('footer div');
            if (footer) {
                footer.appendChild(compareBtn);
            }
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('location').focus();
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        clearForm();
    }
});

// Performance optimization - lazy loading for images
function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for browsers without IntersectionObserver
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Analytics tracking
function trackSearchEvent(searchParams) {
    // Track search events for analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'search', {
            'search_term': searchParams.location || '',
            'property_type': searchParams.property_type || '',
            'price_range': searchParams.price_range || ''
        });
    }
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('Application error:', e.error);
    
    // Show user-friendly error message
    const errorDiv = document.createElement('div');
    errorDiv.innerHTML = `
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;">
            <strong>Something went wrong!</strong> Please try refreshing the page or contact support if the problem persists.
        </div>
    `;
    
    document.body.insertBefore(errorDiv, document.body.firstChild);
});

// Service worker registration for offline functionality
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful');
            })
            .catch(error => {
                console.log('ServiceWorker registration failed');
            });
    });
}
</script>
<?php 
if (isset($_GET['location'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "forfree");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get search parameters
    $location = $conn->real_escape_string($_GET['location']);
    $property_type = isset($_GET['property_type']) ? $conn->real_escape_string($_GET['property_type']) : '';
    $property_type = isset($_GET['property_type']) ? $conn->real_escape_string($_GET['property_type']) : '';
    $rooms = isset($_GET['rooms']) ? $conn->real_escape_string($_GET['rooms']) : '';
    $sale = isset($_GET['sale']) ? $conn->real_escape_string($_GET['sale']) : '';

    // Check which columns exist in the table
    $columnsResult = $conn->query("SHOW COLUMNS FROM data");
    $existingColumns = [];
    while ($col = $columnsResult->fetch_assoc()) {
        $existingColumns[] = $col['Field'];
    }

    // Build SQL query with conditions
    $sql = "SELECT * FROM data WHERE location LIKE '%$location%'";
    
    // Add property type filter (only if column exists)
    if (!empty($property_type) && in_array('property_type', $existingColumns)) {
        $sql .= " AND property_type = '$property_type'";
    }
    
    // Add property_type filter (only if column exists)
    if (!empty($property_type) && in_array('property_type', $existingColumns)) {
        $sql .= " AND property_type = '$property_type'";
    }
    
    // Add rooms filter (only if column exists)
    if (!empty($rooms) && in_array('rooms', $existingColumns)) {
        if ($rooms == '5+') {
            $sql .= " AND rooms >= 5";
        } else {
            $sql .= " AND rooms = '$rooms'";
        }
    }
    
    // Add sale filter (only if column exists)
    if (!empty($sale) && in_array('sale', $existingColumns)) {
        switch ($sale) {
            case 'low':
                $sql .= " AND sale < 1000000";
                break;
            case 'mid':
                $sql .= " AND sale BETWEEN 1000000 AND 5000000";
                break;
            case 'high':
                $sql .= " AND sale > 5000000";
                break;
        }
    }
    
    $result = $conn->query($sql);

    echo '<div class="container mt-4">';
    
    // Display active filters
    $activeFilters = [];
    if (!empty($location)) $activeFilters[] = "Location: $location";
    if (!empty($property_type) && in_array('property_type', $existingColumns)) $activeFilters[] = "Type: " . ucfirst($property_type);
    if (!empty($property_type) && in_array('property_type', $existingColumns)) $activeFilters[] = "property_type: " . ucfirst($property_type);
    if (!empty($rooms) && in_array('rooms', $existingColumns)) $activeFilters[] = "Rooms: $rooms";
    if (!empty($sale) && in_array('sale', $existingColumns)) $activeFilters[] = "sale: " . ucfirst($sale);
    
    if (!empty($activeFilters)) {
        echo '<div class="alert alert-info">';
        echo '<strong>Active Filters:</strong> ' . implode(' | ', $activeFilters);
        echo ' <a href="?" class="btn btn-sm btn-outline-secondary ms-2">Clear All</a>';
        echo '</div>';
    }
    
    if ($result->num_rows > 0) {
        echo "<h3>Search Results (" . $result->num_rows . " properties found)</h3>";
        
        // Convert result to array for easier manipulation
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        
        $totalResults = count($results);
        $showLimit = 5;
        
        // Display results container
        echo '<div id="results-container">';
        
        // Show first 5 results
        for ($i = 0; $i < min($showLimit, $totalResults); $i++) {
            $row = $results[$i];
            echo '<div class="card mb-3 p-3 result-card">';
            echo '<div class="row">';
            echo '<div class="col-md-8">';
            echo '<h5>' . htmlspecialchars($row['location']) . '</h5>';
            echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
            
            // Display property details
            if (isset($row['property_type']) && !empty($row['property_type']) && in_array('property_type', $existingColumns)) {
                echo '<p><strong>Type:</strong> ' . htmlspecialchars(ucfirst($row['property_type'])) . '</p>';
            }
            if (isset($row['property_type']) && !empty($row['property_type']) && in_array('property_type', $existingColumns)) {
                echo '<p><strong>property_type:</strong> ' . htmlspecialchars(ucfirst($row['property_type'])) . '</p>';
            }
            if (isset($row['rooms']) && !empty($row['rooms']) && in_array('rooms', $existingColumns)) {
                echo '<p><strong>Rooms:</strong> ' . htmlspecialchars($row['rooms']) . '</p>';
            }
            if (isset($row['sale']) && !empty($row['sale']) && in_array('sale', $existingColumns)) {
                echo '<p><strong>sale:</strong> EGP ' . number_format($row['sale']) . '</p>';
            }
            
            echo '<p>' . htmlspecialchars($row['description']) . '</p>';
            echo '</div>';
            echo '<div class="col-md-4 text-end">';
            echo "<div class='d-flex flex-column gap-2'>
                    <a href='?page=view&id={$row['id']}' class='btn btn-primary btn-sm'>
                        <i class='fas fa-eye me-1'></i> View Details
                    </a>
                  </div>";
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        // Hidden results (initially hidden)
        if ($totalResults > $showLimit) {
            echo '<div id="hidden-results" style="display: none;">';
            for ($i = $showLimit; $i < $totalResults; $i++) {
                $row = $results[$i];
                echo '<div class="card mb-3 p-3 result-card">';
                echo '<div class="row">';
                echo '<div class="col-md-8">';
                echo '<h5>' . htmlspecialchars($row['location']) . '</h5>';
                echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
                
                // Display property details
                if (isset($row['property_type']) && !empty($row['property_type']) && in_array('property_type', $existingColumns)) {
                    echo '<p><strong>Type:</strong> ' . htmlspecialchars(ucfirst($row['property_type'])) . '</p>';
                }
                if (isset($row['property_type']) && !empty($row['property_type']) && in_array('property_type', $existingColumns)) {
                    echo '<p><strong>property_type:</strong> ' . htmlspecialchars(ucfirst($row['property_type'])) . '</p>';
                }
                if (isset($row['rooms']) && !empty($row['rooms']) && in_array('rooms', $existingColumns)) {
                    echo '<p><strong>Rooms:</strong> ' . htmlspecialchars($row['rooms']) . '</p>';
                }
                if (isset($row['sale']) && !empty($row['sale']) && in_array('sale', $existingColumns)) {
                    echo '<p><strong>sale:</strong> EGP ' . number_format($row['sale']) . '</p>';
                }
                
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                echo '</div>';
                echo '<div class="col-md-4 text-end">';
                echo "<div class='d-flex flex-column gap-2'>
                        <a href='?page=view&id={$row['id']}' class='btn btn-primary btn-sm'>
                            <i class='fas fa-eye me-1'></i> View Details
                        </a>
                      </div>";
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            
            // View More button
            echo '<div class="text-center mt-3">
                    <button id="view-more-btn" class="btn btn-outline-primary">
                        <i class="fas fa-chevron-down me-1"></i>
                        View More (' . ($totalResults - $showLimit) . ' more results)
                    </button>
                    <button id="view-less-btn" class="btn btn-outline-secondary" style="display: none;">
                        <i class="fas fa-chevron-up me-1"></i>
                        View Less
                    </button>
                  </div>';
        }
        
        echo '</div>'; // Close results-container
        
        // Add JavaScript for View More functionality
        echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const viewMoreBtn = document.getElementById("view-more-btn");
                    const viewLessBtn = document.getElementById("view-less-btn");
                    const hiddenResults = document.getElementById("hidden-results");
                    
                    if (viewMoreBtn) {
                        viewMoreBtn.addEventListener("click", function() {
                            hiddenResults.style.display = "block";
                            viewMoreBtn.style.display = "none";
                            viewLessBtn.style.display = "inline-block";
                            
                            // Smooth scroll to continue reading
                            viewLessBtn.scrollIntoView({ behavior: "smooth", block: "center" });
                        });
                    }
                    
                    if (viewLessBtn) {
                        viewLessBtn.addEventListener("click", function() {
                            hiddenResults.style.display = "none";
                            viewMoreBtn.style.display = "inline-block";
                            viewLessBtn.style.display = "none";
                            
                            // Scroll back to the view more button position
                            viewMoreBtn.scrollIntoView({ behavior: "smooth", block: "center" });
                        });
                    }
                });
              </script>';
        
    } else {
        echo '<div class="alert alert-warning">';
        echo "<p>No results found matching your criteria.</p>";
        echo '<p>Try adjusting your search filters or <a href="?">start a new search</a>.</p>';
        echo '</div>';
    }
    
    echo '</div>';
    $conn->close();
}
?>
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Find Your Home</h3>
                        <p>Browse thousands of listings to find the perfect home for you and your family.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>Get a Cash Offer</h3>
                        <p>Sell your home directly to us and skip the hassle of listing and showings.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Connect with Agents</h3>
                        <p>Find top local agents who can help you buy or sell your home.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

  <section class="property-slider-section">
        <div class="container">
            <div class="section-title">
                <h2>Discover More Properties</h2>
                <p>Browse through our selection of handpicked properties just for you</p>
            </div>
            
            <div class="property-slider-container">
                <button class="slider-control slider-prev" id="sliderPrev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="property-slider" id="propertySlider">
                    <!-- Property 1 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=500&h=300&fit=crop" alt="Lakeview Drive Property">
                                <div class="property-label">New Listing</div>
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$729,000</div>
                                <div class="property-address">1214 Lakeview Drive, San Francisco, CA 94103</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>4 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>3 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>2,800 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 2 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500&h=300&fit=crop" alt="Hillside Avenue Property">
                                <div class="property-label featured">Featured</div>
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$1,249,000</div>
                                <div class="property-address">87 Hillside Avenue, Seattle, WA 98101</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>5 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>4.5 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>3,650 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 3 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=500&h=300&fit=crop" alt="Maple Street Property">
                                <div class="property-label">sale Reduced</div>
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$499,000</div>
                                <div class="property-address">342 Maple Street, Austin, TX 78701</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>3 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>2 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>1,950 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 4 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&h=300&fit=crop" alt="Ocean View Blvd Property">
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$875,000</div>
                                <div class="property-address">56 Ocean View Blvd, Miami, FL 33101</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>4 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>3 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>2,400 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 5 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=500&h=300&fit=crop" alt="Central Park West Property">
                                <div class="property-label luxury">Luxury</div>
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$2,395,000</div>
                                <div class="property-address">789 Central Park West, New York, NY 10023</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>6 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>5 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>4,800 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 6 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=500&h=300&fit=crop" alt="College Avenue Property">
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$438,500</div>
                                <div class="property-address">420 College Avenue, Boston, MA 02115</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>2 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>2 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>1,200 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 7 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://photos.mandarinoriental.com/is/image/MandarinOriental/bodrum-villa-melisa-exterior?wid=4000&fmt=jpeg,rgb&qlt=63,0&op_sharpen=0&resMode=sharp2&op_usm=0,0,0,0&icc=sRGB%20IEC61966-2.1&iccEmbed=1&printRes=72&fit=wrap&qlt=45,0" alt="Mountain View Road Property">
                                <div class="property-label new-build">New Build</div>
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$639,000</div>
                                <div class="property-address">1521 Mountain View Road, Denver, CO 80202</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>4 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>3.5 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>2,350 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property 8 -->
                    <div class="property-slide">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="https://th.bing.com/th/id/R.e1b8b3d9a2007efea48d2c7073949d9a?rik=smzQDt4GPfRNmA&pid=ImgRaw&r=0" alt="Sunset Drive Property">
                            </div>
                            <div class="property-content">
                                <div class="property-sale">$585,000</div>
                                <div class="property-address">325 Sunset Drive, Portland, OR 97201</div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <span>3 beds</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <span>2.5 baths</span>
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>2,100 sqft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="slider-control slider-next" id="sliderNext">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="slider-indicators" id="sliderIndicators">
                <!-- Indicators will be generated by JavaScript -->
            </div>
        </div>
    </section>
<!-- <?php
$file = '../sell.php';
$code = file_get_contents(__DIR__ .$file);

$tokens = token_get_all($code);
$cleanedCode = '';

foreach ($tokens as $token) {
    if (is_array($token)) {
        $type = $token[0];
        $text = $token[1];

        if (in_array($type, [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE])) {
            // Skip the entire include/require line
            continue;
        }

        $cleanedCode .= $text;
    } else {
        $cleanedCode .= $token;
    }
}

eval("?>$cleanedCode"); // Evaluate the cleaned code
?> -->


<script>
    let map;
    let marker;

    function initMap() {
        // Set the default location (latitude, longitude)
        const defaultLocation = [51.505, -0.09]; // Example: London, UK

        // Create the map centered at the default location
        map = L.map('locationMap').setView(defaultLocation, 13);

        // Add OpenStreetMap tile layer (free and open-source)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add a draggable marker to the map
        marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

        // Event listener for clicks on the map to set the marker
        map.on('click', function (e) {
            const latLng = e.latlng;
            setMarkerPosition(latLng);
        });

        // Event listener for when the marker is dragged to a new position
        marker.on('dragend', function () {
            const newLatLng = marker.getLatLng();
            setCoordinates(newLatLng);
        });
    }

    // Set the position of the marker and update the hidden fields
    function setMarkerPosition(latLng) {
        marker.setLatLng(latLng);
        setCoordinates(latLng);
    }

    // Set the coordinates in the hidden input fields and fetch the address
    function setCoordinates(latLng) {
        const lat = latLng.lat;
        const lng = latLng.lng;

        // Set the hidden inputs to the selected coordinates
        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lng;

        // Call Nominatim to get the address based on lat and lng
        getAddress(lat, lng);
    }

    // Fetch the address using the Nominatim API (OpenStreetMap's reverse geocoding service)
    function getAddress(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const address = data.display_name; // Get the full address from the response
                document.getElementById("location_address").value = address; // Populate the address input
            })
            .catch(error => {
                console.error("Error fetching address:", error);
                alert("Unable to fetch address for the selected location.");
            });
    }

    // Load the map when the page is ready
    window.onload = initMap;
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('propertySlider');
            const prevBtn = document.getElementById('sliderPrev');
            const nextBtn = document.getElementById('sliderNext');
            const indicatorsContainer = document.getElementById('sliderIndicators');
            
            const slides = document.querySelectorAll('.property-slide');
            let currentIndex = 0;
            let slidesPerView = getSlidesPerView();
            let totalSlides = slides.length;
            let totalGroups = Math.ceil(totalSlides / slidesPerView);
            
            // Create indicators
            for (let i = 0; i < totalGroups; i++) {
                const indicator = document.createElement('div');
                indicator.classList.add('slider-indicator');
                if (i === 0) indicator.classList.add('active');
                indicator.dataset.index = i;
                indicator.addEventListener('click', () => {
                    goToSlide(i * slidesPerView);
                });
                indicatorsContainer.appendChild(indicator);
            }
            
            function getSlidesPerView() {
                if (window.innerWidth >= 1200) return 4;
                if (window.innerWidth >= 992) return 3;
                if (window.innerWidth >= 576) return 2;
                return 1;
            }
            
            function updateSlidesPerView() {
                slidesPerView = getSlidesPerView();
                totalGroups = Math.ceil(totalSlides / slidesPerView);
                
                // Recreate indicators
                indicatorsContainer.innerHTML = '';
                for (let i = 0; i < totalGroups; i++) {
                    const indicator = document.createElement('div');
                    indicator.classList.add('slider-indicator');
                    if (Math.floor(currentIndex / slidesPerView) === i) indicator.classList.add('active');
                    indicator.dataset.index = i;
                    indicator.addEventListener('click', () => {
                        goToSlide(i * slidesPerView);
                    });
                    indicatorsContainer.appendChild(indicator);
                }
                
                // Ensure current index is valid
                if (currentIndex > totalSlides - slidesPerView) {
                    goToSlide(totalSlides - slidesPerView);
                }
            }
            
            function goToSlide(index) {
                if (index < 0) index = 0;
                if (index > totalSlides - slidesPerView) index = totalSlides - slidesPerView;
                
                currentIndex = index;
                const slideWidth = slides[0].offsetWidth + 20; // 20px is the gap
                slider.scrollTo({
                    left: slideWidth * currentIndex,
                    behavior: 'smooth'
                });
                
                // Update indicators
                const indicators = document.querySelectorAll('.slider-indicator');
                indicators.forEach((indicator, i) => {
                    if (Math.floor(currentIndex / slidesPerView) === i) {
                        indicator.classList.add('active');
                    } else {
                        indicator.classList.remove('active');
                    }
                });
            }
            
            prevBtn.addEventListener('click', () => {
                goToSlide(currentIndex - slidesPerView);
            });
            
            nextBtn.addEventListener('click', () => {
                goToSlide(currentIndex + slidesPerView);
            });
            
            // Handle window resize
            window.addEventListener('resize', () => {
                updateSlidesPerView();
            });
            
            // Add touch support
            let touchStartX = 0;
            let touchEndX = 0;
            
            slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
            
            function handleSwipe() {
                const swipeThreshold = 50;
                if (touchStartX - touchEndX > swipeThreshold) {
                    // Swipe left
                    goToSlide(currentIndex + slidesPerView);
                }
                
                if (touchEndX - touchStartX > swipeThreshold) {
                    // Swipe right
                    goToSlide(currentIndex - slidesPerView);
                }
            }
            
<section class="p-0 py-8 mt-0 pt-20 bg-gray-900" id="header">
    <div class="container">
        <div class="row items-center">
            <div class="col lg:w-6/12">
                <h1 class="display-4 letter-spacing-xs mb-6 font-semibold">The <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary capitalize">Definition</span> of <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary capitalize">Multipurpose</span>
                </h1>
                <p>
                    <span class="text-muted text-lg">I am <b class="text-gray-300">speed</b>, I am <b class="text-gray-300">efficency</b>, I am <b class="text-gray-300">Quality</b>, <b>I am <span class="text-primary">Milrato.</span>
                        </b>
                    </span>
                </p>
                <div class="flex flex-col md:flex-row justify-center lg:justify-left gap-x-3 gap-y-3">
                    <a class="btn btn-primary btn-lg" href="invite.html" target="_blank">Invite me - Get Started ➔</a>
                    <a class="btn btn-lg btn-ghost btn-primary" href="webfonts/fa-v4compatibility.ttf.html#features">Check out my Features</a>
                </div>
            </div>
            <div class="col lg:w-6/12 mt-12 lg:mt-0">
                <img class="lg:mr-0 lg:max-w-[500px] rounded-none -36 -40 -80 pr-0 px-0 p-0" src="https://d1pnnwteuly8z3.cloudfront.net/images/3d855c05-3914-481c-bc41-c4b9ee1dd08b/5a8d90cf-580e-4bef-9d67-86f8522a1b1d.png" style="filter:drop-shadow(0.5rem 0.5rem 0.25rem rgba(0, 0, 0, 0.075))" width="600" height="470" alt="product image" loading="lazy" />
            </div>
        </div>
    </div>
</section>

<section style="transform:rotateX(180deg)">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="fill-dark text-dark fill-dark -mb-px pt-0 block w-full top-0 bottom-0 static bg-gray-900">
        <path fill-opacity="1" d="M0,288L34.3,266.7C68.6,245,137,203,206,181.3C274.3,160,343,160,411,181.3C480,203,549,245,617,272C685.7,299,754,309,823,293.3C891.4,277,960,235,1029,213.3C1097.1,192,1166,192,1234,197.3C1302.9,203,1371,213,1406,218.7L1440,224L1440,0L1405.7,0C1371.4,0,1303,0,1234,0C1165.7,0,1097,0,1029,0C960,0,891,0,823,0C754.3,0,686,0,617,0C548.6,0,480,0,411,0C342.9,0,274,0,206,0C137.1,0,69,0,34,0L0,0Z"></path>
    </svg>
</section>

            // Auto slide (optional)
            let autoSlideInterval;
            
            function startAutoSlide() {
                autoSlideInterval = setInterval(() => {
                    const nextIndex = currentIndex + slidesPerView;
                    if (nextIndex >= totalSlides) {
                        // Reset to start if reached the end
                        goToSlide(0);
                    } else {
                        goToSlide(nextIndex);
                    }
                }, 5000); // Change slides every 5 seconds
            }
            
            function stopAutoSlide() {
                clearInterval(autoSlideInterval);
            }
            
            // Start auto sliding
            startAutoSlide();
            
            // Pause auto sliding when user interacts
            slider.addEventListener('mouseenter', stopAutoSlide);
            slider.addEventListener('mouseleave', startAutoSlide);
            slider.addEventListener('touchstart', stopAutoSlide);
            slider.addEventListener('touchend', () => {
                setTimeout(startAutoSlide, 2000);
            });
        });
    </script>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container text-center">
            <h2>Ready to Make the Easy Move?</h2>
            <p class="mb-4">Get a cash offer today. See the ways we can help you sell your home.</p>
            <a href="form.php" class="btn cta-button">Get Started</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-logo">HomeEasy</div>
                    <p>Making it simple to find and secure your perfect home.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <div class="footer-links">
                        <h5>Services</h5>
                        <ul>
                            <li><a href="#">Buy a Home</a></li>
                            <li><a href="#">Sell a Home</a></li>
                            <li><a href="#">Rent a Home</a></li>
                            <li><a href="#">Mortgage</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <div class="footer-links">
                        <h5>About</h5>
                        <ul>
                            <li><a href="#">Our Story</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Press</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="footer-links">
                        <h5>Stay Connected</h5>
                        <p>Subscribe to our newsletter for the latest updates.</p>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-light" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 HomeEasy. All rights reserved.</p>
                <p>
                    <a href="#" class="text-decoration-none me-3">Terms & Conditions</a> | 
                    <a href="#" class="text-decoration-none">Privacy Policy</a>
                </p>
            </div>
        </div>
    </footer>