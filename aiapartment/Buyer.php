<?php
session_start(); // Make sure the session is started
$displayResult = false;
$data = [];
$message = '';

// Check if user email exists in session
$email = isset($_SESSION["email"]) ? $_SESSION["email"] : null;
if (!$email) {
    die("Email not found in session.");
}


$displayResult = false;
$data = [];
$message = '';

// Database configuration
$dbHost = 'localhost';     // Your database host
$dbUsername = 'root';      // Your database username
$dbPassword = '';          // Your database password
$dbName = 'forfree';       // Database name
$tableName = 'data';       // Table name

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    $message = "Database connection failed: " . $conn->connect_error;
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$fields = ['rooms', 'bathrooms', 'kitchen', 'floor', 'reception', 'view', 'location', 'space', 'sale', 'phone'];
    
$data = [
    'rooms' => 1,
    'bathrooms' => 0,
    'kitchen' => 0,
    'floor' => 1,
    'reception' => 0,
    'view' => 'Internal',
    'location' => '',
    'space' => 0,
    'sale' => 0,
    'phone' => ''
];


    // Get values from post
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $data[$field] = htmlspecialchars(trim($_POST[$field]));
        }
    }
    
    // Phone validation
    $phoneNumber = trim($data['phone']);
    $phonePattern = '/^[0-9\+\-\(\)\s]{7,20}$/'; // Basic pattern for phone numbers
    
    if (!preg_match($phonePattern, $phoneNumber)) {
        $message = "Please enter a valid phone number (7-20 digits, may include +, -, (), and spaces)";
    } else {
        // Phone is valid, continue with database insertion
        
        // Insert data into the database
        if ($conn && !$conn->connect_error) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO $tableName (rooms, bathrooms, kitchen, floor, reception, view, location, space, sale, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                $rooms = (int)$data['rooms'];
                $bathrooms = (int)$data['bathrooms'];
                $kitchen = (int)$data['kitchen'];
                $floor = (int)$data['floor'];
                $reception = (int)$data['reception'];
                $view = $data['view'];
                $location = $data['location'];
                $space = (float)$data['space'];
                $sale = (int)$data['sale'];
                $phone = $data['phone'];

                $stmt->bind_param("iiiiissdiss",
                    $rooms,
                    $bathrooms,
                    $kitchen,
                    $floor,
                    $reception,
                    $view,
                    $location,
                    $space,
                    $sale,
                    $phone,
                    $email
                );
                
                // Execute the statement
                if ($stmt->execute()) {
                    $message = "Record saved successfully!";
                    $displayResult = true;
                } else {
                    $message = "Error: " . $stmt->error;
                }
                
                // Close statement
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
            }
        }
    }
}

// Close connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Form</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .container {
            max-width: 900px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #4e73df;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-select, .form-control {
            border-radius: 8px;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ddd;
            transition: border-color 0.3s;
        }

        .form-select:focus, .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: #4e73df;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            padding: 12px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
        }

        .result-card {
            background-color: #f1f5fb;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .result-card ul {
            list-style-type: none;
            padding: 0;
        }

        .result-card ul li {
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
        }

        .result-card ul li:last-child {
            border-bottom: none;
        }

        .result-card h4 {
            color: #4e73df;
            margin-bottom: 20px;
        }

        .d-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .d-grid button {
            font-weight: 600;
        }

        .mb-5, .mt-5 {
            margin-bottom: 30px !important;
        }

        .section-header {
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            color: #4e73df;
            margin-bottom: 40px;
        }

        .section-content {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* Phone input styling */
        .phone-input-wrapper {
            position: relative;
        }
        
        .phone-input-wrapper .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
        }
        
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <!-- Form Section -->
    <div class="section-content">
        <h2 class="section-header">Enter Apartment Details for Seller</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="card p-4">
            <div class="card-header">Apartment Form</div>
            <form method="POST" action="" id="apartmentForm">
                <div class="mb-3">
                    <label for="rooms" class="form-label">Rooms:</label>
                    <select name="rooms" id="rooms" class="form-select" required>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= isset($data['rooms']) && $data['rooms'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="bathrooms" class="form-label">Bathrooms:</label>
                    <input type="number" name="bathrooms" id="bathrooms" class="form-control" value="<?= $data['bathrooms'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="kitchen" class="form-label">Kitchen:</label>
                    <input type="number" name="kitchen" id="kitchen" class="form-control" value="<?= $data['kitchen'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="floor" class="form-label">Floor:</label>
                    <select name="floor" id="floor" class="form-select" required>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= isset($data['floor']) && $data['floor'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="reception" class="form-label">Reception:</label>
                    <input type="number" name="reception" id="reception" class="form-control" value="<?= $data['reception'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="view" class="form-label">View:</label>
                    <select name="view" id="view" class="form-select" required>
                        <option value="Internal" <?= isset($data['view']) && $data['view'] == 'Internal' ? 'selected' : '' ?>>Internal</option>
                        <option value="External" <?= isset($data['view']) && $data['view'] == 'External' ? 'selected' : '' ?>>External</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location:</label>
                    <input type="text" name="location" id="location" class="form-control" value="<?= $data['location'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="space" class="form-label">Space (m²):</label>
                    <input type="number" name="space" id="space" class="form-control" value="<?= $data['space'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="sale" class="form-label">Sale Price:</label>
                    <input type="number" name="sale" id="sale" class="form-control" value="<?= $data['sale'] ?? '' ?>" required>
                </div>

                <div class="mb-3 phone-input-wrapper">
                    <label for="phone" class="form-label">Phone Number:</label>
                    <input type="tel" name="phone" id="phone" class="form-control <?= !empty($message) && strpos($message, 'phone') !== false ? 'is-invalid' : '' ?>" 
                           value="<?= $data['phone'] ?? '' ?>" 
                           placeholder="+1 (123) 456-7890" 
                           pattern="[0-9\+\-\(\)\s]{7,20}"
                           required>
                    <div class="form-text">Format: Country code (optional) followed by phone number. Example: +1 (123) 456-7890</div>
                    <div class="invalid-feedback">
                        Please enter a valid phone number (7-20 digits, may include +, -, (), and spaces)
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Display Result -->
    <?php if ($displayResult): ?>
        <div class="section-content mt-5">
            <div class="result-card">
                <h4>Buyer - Apartment Details</h4>
                <ul>
                    <li><strong>Rooms:</strong> <?= $data['rooms'] ?></li>
                    <li><strong>Bathrooms:</strong> <?= $data['bathrooms'] ?></li>
                    <li><strong>Kitchen:</strong> <?= $data['kitchen'] ?></li>
                    <li><strong>Floor:</strong> <?= $data['floor'] ?></li>
                    <li><strong>Reception:</strong> <?= $data['reception'] ?></li>
                    <li><strong>View:</strong> <?= $data['view'] ?></li>
                    <li><strong>Location:</strong> <?= $data['location'] ?></li>
                    <li><strong>Space:</strong> <?= $data['space'] ?> m²</li>
                    <li><strong>Sale Price:</strong> $<?= !empty($data['sale']) ? number_format((float)$data['sale']) : '0' ?></li>
                    <li><strong>Phone:</strong> <?= $data['phone'] ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Client-side validation for phone number
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const form = document.getElementById('apartmentForm');
    
    form.addEventListener('submit', function(event) {
        const phoneValue = phoneInput.value.trim();
        const phonePattern = /^[0-9\+\-\(\)\s]{7,20}$/;
        
        if (!phonePattern.test(phoneValue)) {
            phoneInput.classList.add('is-invalid');
            event.preventDefault();
        } else {
            phoneInput.classList.remove('is-invalid');
        }
    });
    
    phoneInput.addEventListener('input', function() {
        if (phoneInput.classList.contains('is-invalid')) {
            const phoneValue = phoneInput.value.trim();
            const phonePattern = /^[0-9\+\-\(\)\s]{7,20}$/;
            
            if (phonePattern.test(phoneValue)) {
                phoneInput.classList.remove('is-invalid');
            }
        }
    });
});
</script>

</body>
</html>