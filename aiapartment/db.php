<?php
$host = 'localhost';
$db = 'forfree'; // Make sure this is the actual DB name
$user = 'root';
$pass = ''; // For XAMPP, this is usually empty

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
