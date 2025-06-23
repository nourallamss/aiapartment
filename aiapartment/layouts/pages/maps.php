<?php
/**
 * Property Details View Page
 * Displays detailed information about a specific property
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$db = 'forfree';
$user = 'root';
$pass = '';

// Initialize variables
$property = null;
$error = null;

// Check if property ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error = "Property ID is required.";
} else {
    $property_id = intval($_GET['id']);
    
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // Fetch property details
        $stmt = $pdo->prepare("SELECT * FROM data WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$property) {
            $error = "Property not found.";
        }
        
    } catch (PDOException $e) {
        $error = "Database error: Unable to load property details.";
        error_log("Database error: " . $e->getMessage());
    }
}

// Handle add to cart action
if (isset($_POST['add_to_cart']) && $property) {
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if property is already in cart
    $already_in_cart = false;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $property['id']) {
            $already_in_cart = true;
            break;
        }
    }
    
    if (!$already_in_cart) {
        // Add property to cart
        $_SESSION['cart'][] = [
            'id' => $property['id'],
            'location' => $property['location'],
            'price' => $property['sale'],
            'image' => $property['images']
        ];
        $cart_success = "Property added to cart successfully!";
    } else {
        $cart_error = "This property is already in your cart.";
    }
}

// If there's an error, display error page
if ($error) {
    $content = "
    <div class='row justify-content-center'>
        <div class='col-md-8'>
            <div class='alert alert-danger text-center'>
                <i class='fas fa-exclamation-triangle fa-3x mb-3'></i>
                <h4>Error Loading Property</h4>
                <p>$error</p>
                <a href='?page=buy' class='btn btn-primary mt-3'>
                    <i class='fas fa-arrow-left me-2'></i>Back to Properties
                </a>
            </div>
        </div>
    </div>";
    
    renderPage("Property Not Found | HomeEasy", $content);
    exit;
}

// Format property data for display
$formatted_price = number_format(floatval($property['sale']), 2);
$date = new DateTime($property['created_at']);
$formatted_date = $date->format('F j, Y \a\t g:i A');
$phone = !empty($property['phone']) ? htmlspecialchars($property['phone']) : 'Not provided';
$email = !empty($property['email']) ? htmlspecialchars($property['email']) : 'Not provided';
$location = htmlspecialchars($property['location']);
$rooms = intval($property['rooms']);
$bathrooms = intval($property['bathrooms']);
$space = floatval($property['space']);
$description = !empty($property['description']) ? htmlspecialchars($property['description']) : '';

// Handle image display
$images = htmlspecialchars($property['images'] ?? '');
$has_image = !empty($images) && file_exists($images);

// Get latitude and longitude from the database
$latitude = floatval($property['latitude']);
$longitude = floatval($property['longitude']);

// Check if current user owns this property
$is_owner = isset($_SESSION['email']) && $_SESSION['email'] === $property['email'];

// Check if property is in cart
$in_cart = false;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $property['id']) {
            $in_cart = true;
            break;
        }
    }
}

// Build the content
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Property Details | HomeEasy</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  
  <style>
    body, html {
      margin: 0; padding: 0; height: 100%;
      display: flex; flex-direction: column;
      font-family: Arial, sans-serif;
    }
    #controls {
      padding: 10px;
      background: #f0f0f0;
      display: flex;
      gap: 10px;
    }
    #locationInput {
      flex: 1;
      padding: 8px;
      font-size: 16px;
    }
    #searchBtn {
      padding: 8px 16px;
      font-size: 16px;
      cursor: pointer;
    }
    #map {
      flex-grow: 1;
    }
  </style>
</head>
<body>

  <div id="controls">
    <input id="locationInput" value="<?php echo $location; ?>" type="text" placeholder="Enter location or address" />
    <button id="searchBtn">Show on Map</button>
  </div>

  <div id="map"></div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Latitude and Longitude from PHP
    const latitude = <?php echo $latitude; ?>;
    const longitude = <?php echo $longitude; ?>;
    
    // Initialize the map centered on the property location
    const map = L.map('map').setView([latitude, longitude], 14);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Marker variable
    let marker = L.marker([latitude, longitude]).addTo(map);

    // Handle search button click
    document.getElementById('searchBtn').addEventListener('click', () => {
      const address = document.getElementById('locationInput').value.trim();
      if (!address) {
        alert('Please enter a location.');
        return;
      }

      // Use Nominatim API for geocoding
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            alert('Location not found.');
            return;
          }

          const lat = parseFloat(data[0].lat);
          const lon = parseFloat(data[0].lon);

          // Update marker position
          marker.setLatLng([lat, lon]);

          // Center map to new location
          map.setView([lat, lon], 15);
        })
        .catch(() => alert('Error searching location.'));
    });
  </script>

</body>
</html>
