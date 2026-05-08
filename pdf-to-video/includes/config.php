<?php
/**
 * Database Configuration for PDF to Video Converter
 * Replace with your actual database credentials
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'pdf_to_video';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Select database (create if not exists)
$db_check = mysqli_select_db($conn, $db_name);
if (!$db_check) {
    $create_db = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (mysqli_query($conn, $create_db)) {
        mysqli_select_db($conn, $db_name);
    } else {
        die("Database creation failed: " . mysqli_error($conn));
    }
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');

?>
