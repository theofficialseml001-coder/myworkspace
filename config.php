<?php
/**
 * CMS Configuration File
 * World-Class CMS with Advanced Features
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'worldclass_cms');

// Security Secret Key (Change this in production!)
define('DB_SECRET', 'your-unique-secret-key-change-in-production-' . uniqid());

// Site Configuration
define('SITE_URL', 'http://localhost');
define('ABSPATH', __DIR__ . '/');

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600);
