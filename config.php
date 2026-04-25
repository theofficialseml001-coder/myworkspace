<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'idea_validator';

// Create connection
$conn = mysqli_connect($host, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Select database
if (!mysqli_select_db($conn, $database)) {
    // Database doesn't exist, we'll create it via setup script
    echo "Database not selected. Please run setup first.";
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>
