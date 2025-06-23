<?php
$host = 'localhost';
$db = 'forfree';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $message = '';
    
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Fetch record by ID
        $stmt = $pdo->prepare("SELECT * FROM data WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            die("Record not found.");
        }
    } else {
        die("No ID provided.");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle single file upload
        $imagesPath = $row['images']; // Keep existing images by default
        
        if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/properties/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = $_FILES['images']['name'];
            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Delete old images if it exists and a new one is being uploaded
                if (!empty($row['images']) && file_exists($row['images'])) {
                    unlink($row['images']);
                }
                
                $newFilename = uniqid() . '_' . $filename;
                $uploadPath = $uploadDir . $newFilename;
                
                if (move_uploaded_file($_FILES['images']['tmp_name'], $uploadPath)) {
                    $imagesPath = $uploadPath;
                } else {
                    $message = '<div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">Error uploading images.</div>';
                }
            } else {
                $message = '<div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">Invalid file type. Only JPG, JPEG, PNG, GIF, and WebP are allowed.</div>';
            }
        }
        
        // Update the record
        if (empty($message)) { // Only update if no upload errors
            $stmt = $pdo->prepare("UPDATE data SET email=?, rooms=?, bathrooms=?, kitchen=?, floor=?, reception=?, view=?, location=?, space=?, sale=?, phone=?, description=?, images=? WHERE id=?");
            $result = $stmt->execute([
                $_POST['email'],
                $_POST['rooms'],
                $_POST['bathrooms'],
                $_POST['kitchen'],
                $_POST['floor'],
                $_POST['reception'],
                $_POST['view'],
                $_POST['location'],
                $_POST['space'],
                $_POST['sale'],
                $_POST['phone'],
                $_POST['description'],
                $imagesPath,
                $_POST['id']
            ]);
            
            if ($result) {
                $message = '<div style="color: green; padding: 10px; border: 1px solid green; margin: 10px 0;">Property updated successfully!</div>';
                // Refresh the data
                $stmt = $pdo->prepare("SELECT * FROM data WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = '<div style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">Error updating property.</div>';
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .current-images {
            margin-top: 10px;
            text-align: center;
        }
        
        .current-images img {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
            cursor: pointer;
        }
        
        .current-images img:hover {
            border-color: #007bff;
            transform: scale(1.05);
            transition: all 0.3s ease;
        }
        
        .images-error {
            width: 200px;
            height: 150px;
            background-color: #f8f9fa;
            border: 2px dashed #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            font-size: 14px;
            text-align: center;
            border-radius: 5px;
            margin: 0 auto;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #007bff;
            border-radius: 5px;
        }
        
        .back-link:hover {
            background-color: #007bff;
            color: white;
        }
        
        .row {
            display: flex;
            gap: 20px;
        }
        
        .col {
            flex: 1;
        }
        
        @media (max-width: 600px) {
            .row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-link">← Back to Listings</a>
        
        <?php echo $message; ?>
        
        <h2>Edit Property</h2>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="rooms">Rooms:</label>
                        <input type="number" id="rooms" name="rooms" value="<?= htmlspecialchars($row['rooms']) ?>" min="1" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="bathrooms">Bathrooms:</label>
                        <input type="number" id="bathrooms" name="bathrooms" value="<?= htmlspecialchars($row['bathrooms']) ?>" min="1" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="kitchen">Kitchen:</label>
                        <input type="number" id="kitchen" name="kitchen" value="<?= htmlspecialchars($row['kitchen']) ?>" min="0" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="floor">Floor:</label>
                        <input type="number" id="floor" name="floor" value="<?= htmlspecialchars($row['floor']) ?>" min="0" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="reception">Reception:</label>
                <input type="number" id="reception" name="reception" value="<?= htmlspecialchars($row['reception']) ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="view">View:</label>
                <input type="text" id="view" name="view" value="<?= htmlspecialchars($row['view']) ?>" placeholder="e.g., City view, Garden view, Sea view">
            </div>
            
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($row['location']) ?>" required>
            </div>
             <div class="mb-3">
                        <label class="form-label">📍 Select Location on Map:</label>
                        <div id="locationMap" style="height: 300px; border-radius: 10px; border: 2px solid #e3e6f0;"></div>
                        <div class="form-text">Click on the map to set the property location. Coordinates will be saved with your listing.</div>
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                    </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="space">Space (sq ft):</label>
                        <input type="number" id="space" name="space" value="<?= htmlspecialchars($row['space']) ?>" min="1" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="sale">Sale Price ($):</label>
                        <input type="number" id="sale" name="sale" value="<?= htmlspecialchars($row['sale']) ?>" min="1" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" placeholder="Provide a detailed description of the property..."><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="images">Property images:</label>
                <input type="file" id="images" name="images" accept="images/*">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Select an images (JPG, PNG, GIF, WebP). Maximum file size: 5MB.
                </small>
                
                <?php if (!empty($row['images'])): ?>
                    <div class="current-images">
                        <strong>Current images:</strong><br>
                        <?php 
                        $images = trim($row['images']);
                        $imagesUrl = './' . ltrim($images, './');
                        
                        if (file_exists($images)):
                        ?>
                            <img src="<?= htmlspecialchars($imagesUrl) ?>" 
                                 alt="Property images" 
                                 title="<?= htmlspecialchars($images) ?>"
                                 onclick="window.open('<?= htmlspecialchars($imagesUrl) ?>', '_blank')"
                                 onerror="this.parentElement.innerHTML='<div class=&quot;images-error&quot;>Failed to load images<br><?= htmlspecialchars(basename($images)) ?></div>'">
                        <?php else: ?>
                            <div class="images-error">
                                images not found<br>
                                <?= htmlspecialchars(basename($images)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <input type="submit" value="Update Property">
        </form>
 
    </div>
<script>

    // Leaflet Map Initialization
const map = L.map('locationMap').setView([51.505, -0.09], 13); // Default center (London)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

let marker = null;

// Add click event to place/update marker
map.on('click', function(e) {
    const { lat, lng } = e.latlng;
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map)
            .bindPopup('Property Location<br>' + e.latlng.toString())
            .openPopup();
    }
    
    // Reverse geocode to get address (using Nominatim)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            const address = data.display_name || 'Selected Location';
            document.getElementById('location').value = address;
            if (marker) marker.setPopupContent(`Property Location<br>${address}`).openPopup();
        })
        .catch(error => {
            console.error('Geocoding error:', error);
        });
});

// Try to get user's location for better initial map view
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            map.setView([latitude, longitude], 13);
            
            // If we have coordinates from previous submission, use those
            const savedLat = document.getElementById('latitude').value;
            const savedLng = document.getElementById('longitude').value;
            if (savedLat && savedLng) {
                const latLng = [parseFloat(savedLat), parseFloat(savedLng)];
                map.setView(latLng, 13);
                marker = L.marker(latLng).addTo(map)
                    .bindPopup('Property Location<br>' + latLng.toString())
                    .openPopup();
            }
        },
        (error) => {
            console.log('Geolocation error:', error);
        },
        { timeout: 5000 }
    );
}

// When location input changes, try to geocode it
document.getElementById('location').addEventListener('change', function() {
    const address = this.value.trim();
    if (address.length > 3) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const { lat, lon } = data[0];
                    const latLng = [parseFloat(lat), parseFloat(lon)];
                    map.setView(latLng, 15);
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;
                    
                    if (marker) {
                        marker.setLatLng(latLng);
                    } else {
                        marker = L.marker(latLng).addTo(map)
                            .bindPopup('Property Location<br>' + address)
                            .openPopup();
                    }
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
            });
    }
});
// Enhanced client-side validation and security
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('apartmentForm');
    const phoneInput = document.getElementById('phone');
    const imageUploadArea = document.getElementById('imageUploadArea');
    const imageInput = document.getElementById('propertyImage');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const descriptionTextarea = document.getElementById('description');
    const charCounter = document.getElementById('charCounter');
    let selectedFile = null;
    
    // Character counter for description
    function updateCharCounter() {
        const currentLength = descriptionTextarea.value.length;
        const maxLength = 1000;
        charCounter.textContent = `${currentLength} / ${maxLength} characters`;
        
        // Change color based on usage
        if (currentLength > maxLength * 0.9) {
            charCounter.className = 'char-counter danger';
        } else if (currentLength > maxLength * 0.75) {
            charCounter.className = 'char-counter warning';
        } else {
            charCounter.className = 'char-counter';
        }
    }
    
    // Initialize character counter
    updateCharCounter();
    
    // Update character counter on input
    descriptionTextarea.addEventListener('input', updateCharCounter);
    
    // Enhanced form validation
    form.addEventListener('submit', function(event) {
        let isValid = true;
        const errors = [];
        
        // Phone validation
        const phoneValue = phoneInput.value.trim();
        const phonePattern = /^[\d\+\-\(\)\s]{7,20}$/;
        
        if (!phonePattern.test(phoneValue)) {
            phoneInput.classList.add('is-invalid');
            errors.push('Invalid phone number format');
            isValid = false;
        } else {
            phoneInput.classList.remove('is-invalid');
        }
        
        // Location validation
        const locationInput = document.getElementById('location');
        if (locationInput.value.trim().length < 1) {
            locationInput.classList.add('is-invalid');
            errors.push('Location is required');
            isValid = false;
        } else {
            locationInput.classList.remove('is-invalid');
        }
        
        // Space validation
        const spaceInput = document.getElementById('space');
        const spaceValue = parseFloat(spaceInput.value);
        if (isNaN(spaceValue) || spaceValue < 1 || spaceValue > 10000) {
            spaceInput.classList.add('is-invalid');
            errors.push('Space must be between 1 and 10,000 m²');
            isValid = false;
        } else {
            spaceInput.classList.remove('is-invalid');
        }
        
        // Sale price validation
        const saleInput = document.getElementById('sale');
        const saleValue = parseInt(saleInput.value);
        if (isNaN(saleValue) || saleValue < 0) {
            saleInput.classList.add('is-invalid');
            errors.push('Sale price must be a positive number');
            isValid = false;
        } else {
            saleInput.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            event.preventDefault();
            alert('Please fix the following errors:\n• ' + errors.join('\n• '));
        }
    });
    
    // Real-time phone validation
    phoneInput.addEventListener('input', function() {
        const phoneValue = this.value.trim();
        const phonePattern = /^[\d\+\-\(\)\s]{7,20}$/;
        
        if (phoneValue.length > 0) {
            if (phonePattern.test(phoneValue)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    // Enhanced image upload handling
    imageUploadArea.addEventListener('click', function() {
        imageInput.click();
    });
    
    imageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    imageUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    imageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });
    
    imageInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });
    
    function handleFile(file) {
        // Enhanced file validation
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            showAlert('Please select only image files (JPEG, PNG, GIF).', 'danger');
            return;
        }
        
        // Validate file size
        if (file.size > maxFileSize) {
            showAlert(`File "${file.name}" is too large. Maximum size is 5MB.`, 'danger');
            return;
        }
        
        selectedFile = file;
        createImagePreview(file);
    }
    
    function createImagePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreviewContainer.innerHTML = `
                <div class="image-preview">
                    <img src="${e.target.result}" alt="Preview" loading="lazy">
                    <button type="button" class="image-remove" onclick="removeImage()" title="Remove Image">×</button>
                </div>
                <div class="mt-2 text-muted">
                    <small>📁 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
    
    // Global function for removing image
    window.removeImage = function() {
        selectedFile = null;
        imagePreviewContainer.innerHTML = '';
        imageInput.value = '';
        showAlert('Image removed successfully.', 'info');
    };
    
    // Alert function
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <strong>${type === 'danger' ? '⚠️ Error!' : type === 'success' ? '✅ Success!' : 'ℹ️ Info:'}</strong>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.section-content');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Form security enhancements
    
    // Prevent multiple form submissions
    let isSubmitting = false;
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        isSubmitting = true;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        submitButton.innerHTML = '⏳ Processing...';
        submitButton.disabled = true;
        
        // Re-enable after 10 seconds as a failsafe
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            isSubmitting = false;
        }, 10000);
    });
    
    // Auto-save form data to prevent data loss (using sessionStorage for security)
    const formInputs = form.querySelectorAll('input, select, textarea');
    
    // Load saved data on page load
    formInputs.forEach(input => {
        if (input.type !== 'file' && input.name !== 'csrf_token') {
            const savedValue = sessionStorage.getItem('form_' + input.name);
            if (savedValue && !input.value) {
                input.value = savedValue;
            }
        }
    });
    
    // Save data on input change
    formInputs.forEach(input => {
        if (input.type !== 'file' && input.name !== 'csrf_token') {
            input.addEventListener('input', function() {
                sessionStorage.setItem('form_' + this.name, this.value);
            });
        }
    });
    
    // Clear saved data on successful submission
    form.addEventListener('submit', function() {
        if (!this.querySelector('.is-invalid')) {
            formInputs.forEach(input => {
                if (input.name !== 'csrf_token') {
                    sessionStorage.removeItem('form_' + input.name);
                }
            });
        }
    });
    
    // Update character counter on page load
    updateCharCounter();
    
    // Add smooth animations
    const cards = document.querySelectorAll('.card, .result-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });
});

// Additional security measures
document.addEventListener('contextmenu', function(e) {
    // Optionally disable right-click on sensitive areas
    if (e.target.closest('.security-badge')) {
        e.preventDefault();
    }
});

// Detect potential XSS attempts in form inputs
document.addEventListener('input', function(e) {
    const suspiciousPatterns = [
        /<script/i,
        /javascript:/i,
        /on\w+=/i,
        /<iframe/i
    ];
    
    const value = e.target.value;
    for (let pattern of suspiciousPatterns) {
        if (pattern.test(value)) {
            e.target.value = value.replace(pattern, '');
            console.warn('Potentially malicious input detected and sanitized');
        }
    }
});
</script>
</body>
</html>