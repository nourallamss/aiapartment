<?php
/**
 * Property Details Page
 * 
 * This script displays detailed information about a specific property
 * based on the ID provided in the URL parameter.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$message = '';
$property = null;
$propertyId = null;

// Database configuration - consider moving to a separate config file
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'forfree');
define('DB_TABLE', 'data');

/**
 * Function to sanitize input data
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Function to get property details from database
 * @param int $id - Property ID
 * @return array|null - Property details or null if not found
 */
function getPropertyById($id) {
    try {
        // Create database connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Prepare SQL statement to fetch the property
        $stmt = $conn->prepare("SELECT * FROM " . DB_TABLE . " WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $property = $result->fetch_assoc();
        } else {
            return null;
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
        
        return $property;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

// Check if property ID is provided and is valid
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT) !== false) {
        $propertyId = (int)$_GET['id'];
        $property = getPropertyById($propertyId);
        
        if (!$property) {
            $message = "Property not found or database error occurred.";
        }
    } else {
        $message = "Invalid property ID format.";
    }
} else {
    $message = "No property ID specified.";
}

// Page title based on property data
$pageTitle = isset($property) ? "Property in " . htmlspecialchars($property['location']) : "Property Details";

// Function to safely output property data
function safeEcho($data) {
    echo htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Function to format currency values
function formatCurrency($amount) {
    return number_format((float)$amount, 0, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .container {
            max-width: 1200px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .property-header {
            background-color: #4e73df;
            color: white;
            padding: 25px;
        }

        .property-price {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .property-location {
            font-size: 1.3rem;
            margin-top: 5px;
        }

        .property-info {
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
            background-color: white;
        }

        .property-info-item {
            flex: 1 0 25%;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .property-info-item {
                flex: 1 0 50%;
            }
        }

        .property-info-label {
            font-weight: bold;
            color: #4e73df;
            font-size: 0.9rem;
        }

        .property-info-value {
            font-size: 1.2rem;
            margin-top: 5px;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #4e73df;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 30px;
        }

        .property-features {
            display: flex;
            justify-content: space-between;
            margin: 25px 0;
            flex-wrap: wrap;
        }

        .feature-item {
            text-align: center;
            flex: 1 0 25%;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .feature-item {
                flex: 1 0 50%;
            }
        }

        .feature-icon {
            color: #4e73df;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .feature-value {
            font-weight: bold;
            font-size: 1.3rem;
        }

        .feature-label {
            font-size: 1rem;
            color: #6c757d;
        }

        .contact-info {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .contact-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #4e73df;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .contact-icon {
            margin-right: 15px;
            color: #4e73df;
            font-size: 1.2rem;
            width: 25px;
        }
        
        .property-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
     <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">HomeEasy</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="?page=buy">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=rent">Rent</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=sell">Sell</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=feedback">FeedBack</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=agent">Find an Agent</a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="?action=logout" class="btn btn-outline-primary">Logout</a>
                        <?php else: ?>
                            <a href="?page=login" class="btn btn-outline-primary">Login</a>
                            <a href="?page=register" class="btn btn-primary ms-2">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    
    <div class="container mt-5">
        <div class="mb-4">
            <a href="index.php?page=sell" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Listings
            </a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php elseif ($property): ?>
            <div class="card">
                <div class="property-header">
                    <div class="property-price">$<?= formatCurrency($property['sale']) ?></div>
                    <div class="property-location"><?= htmlspecialchars($property['location']) ?></div>
                </div>
                
                <div class="property-features p-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="feature-value"><?= (int)$property['rooms'] ?></div>
                        <div class="feature-label">Bedrooms</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bath"></i>
                        </div>
                        <div class="feature-value"><?= (int)$property['bathrooms'] ?></div>
                        <div class="feature-label">Bathrooms</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="feature-value"><?= (int)$property['kitchen'] ?></div>
                        <div class="feature-label">Kitchen</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-expand"></i>
                        </div>
                        <div class="feature-value"><?= (int)$property['space'] ?> m²</div>
                        <div class="feature-label">Space</div>
                    </div>
                </div>
                
                <div class="property-info">
                    <div class="property-info-item">
                        <div class="property-info-label">PROPERTY TYPE</div>
                        <div class="property-info-value">Apartment</div>
                    </div>
                    <div class="property-info-item">
                        <div class="property-info-label">FLOOR</div>
                        <div class="property-info-value"><?= (int)$property['floor'] ?></div>
                    </div>
                    <div class="property-info-item">
                        <div class="property-info-label">RECEPTION</div>
                        <div class="property-info-value"><?= htmlspecialchars($property['reception']) ?></div>
                    </div>
                    <div class="property-info-item">
                        <div class="property-info-label">VIEW</div>
                        <div class="property-info-value"><?= htmlspecialchars($property['view']) ?></div>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="mb-4">About This Property</h3>
                    <p>
                        This beautiful <?= (int)$property['rooms'] ?>-bedroom apartment is located in 
                        <?= htmlspecialchars($property['location']) ?> with <?= (int)$property['bathrooms'] ?> 
                        bathrooms and <?= (int)$property['kitchen'] ?> kitchen(s). The apartment is situated on 
                        floor <?= (int)$property['floor'] ?> and offers <?= htmlspecialchars($property['view']) ?> views.
                        With <?= (int)$property['space'] ?> m² of living space, this property provides ample room 
                        for comfortable living.
                    </p>
                    
                    <div class="contact-info mt-5">
                        <div class="contact-title">Contact Information</div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>Property Agent</div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>+1 (555) 123-4567</div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>agent@homeeasy.com</div>
                        </div>
                    </div>
                    
                    <div class="property-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-calendar-alt"></i> Schedule a Viewing
                        </button>
                        <button class="btn btn-secondary">
                            <i class="fas fa-heart"></i> Save Property
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> HomeEasy. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>