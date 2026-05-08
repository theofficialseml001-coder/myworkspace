<?php
/**
 * Database Setup Script for PDF to Video Converter
 * Run this file once to create the required database tables
 */

require_once 'includes/config.php';

// SQL to create conversions table
$sql_conversions = "CREATE TABLE IF NOT EXISTS conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ip VARCHAR(45) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    video_title VARCHAR(255) DEFAULT NULL,
    script_content TEXT,
    voice_type VARCHAR(50) DEFAULT 'male',
    speech_rate VARCHAR(20) DEFAULT '1.0',
    video_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    video_path VARCHAR(500) DEFAULT NULL,
    thumbnail_path VARCHAR(500) DEFAULT NULL,
    duration_seconds INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    INDEX idx_status (video_status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// SQL to create settings table
$sql_settings = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Execute queries
if (mysqli_query($conn, $sql_conversions)) {
    echo "✓ Table 'conversions' created successfully<br>";
} else {
    echo "✗ Error creating 'conversions' table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_settings)) {
    echo "✓ Table 'settings' created successfully<br>";
    
    // Insert default settings
    $default_settings = [
        ['max_file_size', '10', 'int', 'Maximum PDF file size in MB'],
        ['allowed_extensions', 'pdf', 'string', 'Comma-separated list of allowed file extensions'],
        ['output_format', 'mp4', 'string', 'Default video output format'],
        ['video_width', '1280', 'int', 'Video width in pixels'],
        ['video_height', '720', 'int', 'Video height in pixels'],
        ['fps', '30', 'int', 'Frames per second'],
        ['background_color', '#ffffff', 'string', 'Default background color for slides'],
        ['text_color', '#000000', 'string', 'Default text color'],
        ['font_family', 'Arial', 'string', 'Default font family'],
        ['font_size', '24', 'int', 'Default font size in points'],
        ['slide_duration', '5', 'int', 'Default slide duration in seconds'],
        ['enable_watermark', '0', 'int', 'Enable/disable watermark (0 or 1)'],
        ['watermark_text', 'PDF to Video', 'string', 'Watermark text'],
    ];
    
    foreach ($default_settings as $setting) {
        $check_sql = "SELECT COUNT(*) as count FROM settings WHERE setting_key = '{$setting[0]}'";
        $result = mysqli_query($conn, $check_sql);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] == 0) {
            $insert_sql = "INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                          VALUES ('{$setting[0]}', '{$setting[1]}', '{$setting[2]}', '{$setting[3]}')";
            if (mysqli_query($conn, $insert_sql)) {
                echo "✓ Default setting '{$setting[0]}' inserted<br>";
            }
        }
    }
} else {
    echo "✗ Error creating 'settings' table: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>Database setup completed!</strong><br>";
echo "You can now use the PDF to Video converter.<br>";
echo "<a href='index.php'>Go to Application</a>";

?>
