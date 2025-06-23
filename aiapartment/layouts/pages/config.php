<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your database username
define('DB_PASS', ''); // Change to your database password
define('DB_NAME', 'forfree');

// Create database connection
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Get table columns dynamically
function getTableColumns() {
    $pdo = getConnection();
    $stmt = $pdo->query("DESCRIBE data");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $columns;
}

// Search function with dynamic column detection
function searchData($location = '', $searchTerm = '') {
    $pdo = getConnection();
    
    // Get available columns
    $columns = getTableColumns();
    
    $sql = "SELECT * FROM data WHERE 1=1";
    $params = [];
    
    // Check if location column exists before using it
    if (!empty($location) && in_array('location', $columns)) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$location%";
    }
    
    // Build search across available columns (excluding id and timestamp columns)
    if (!empty($searchTerm)) {
        $searchableColumns = array_filter($columns, function($col) {
            return !in_array($col, ['id', 'created_at', 'updated_at']) && 
                   !preg_match('/timestamp|datetime/i', $col);
        });
        
        if (!empty($searchableColumns)) {
            $searchConditions = [];
            foreach ($searchableColumns as $column) {
                $searchConditions[] = "$column LIKE ?";
                $params[] = "%$searchTerm%";
            }
            $sql .= " AND (" . implode(" OR ", $searchConditions) . ")";
        }
    }
    
    // Order by location if it exists, otherwise by first available column
    if (in_array('location', $columns)) {
        $sql .= " ORDER BY location";
        if (in_array('name', $columns)) {
            $sql .= ", name";
        }
    } else {
        $sql .= " ORDER BY " . $columns[1]; // Use second column (skip id)
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all unique locations (with error handling)
function getLocations() {
    $pdo = getConnection();
    
    // Check if location column exists
    $columns = getTableColumns();
    if (!in_array('location', $columns)) {
        return []; // Return empty array if location column doesn't exist
    }
    
    $stmt = $pdo->query("SELECT DISTINCT location FROM data ORDER BY location");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Debug function to show table structure
function showTableStructure() {
    $pdo = getConnection();
    $stmt = $pdo->query("DESCRIBE data");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>