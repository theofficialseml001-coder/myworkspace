<?php
/**
 * PressWP - WordPress Clone
 * index.php - Main Entry Point
 */

require_once 'config.php';

// Get Active Theme
$active_theme = get_option('active_theme');
if (!$active_theme) {
    $active_theme = 'multipurpose';
}

// Include Theme
$theme_path = __DIR__ . '/themes/' . $active_theme . '/index.php';

if (file_exists($theme_path)) {
    include $theme_path;
} else {
    // Fallback to multipurpose theme
    include __DIR__ . '/themes/multipurpose/index.php';
}
?>
