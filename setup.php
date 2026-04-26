<?php
/**
 * CMS Installation & Setup Script
 * Creates all tables including advanced features
 */

require_once __DIR__ . '/config.php';

// Check if already installed
if (file_exists(__DIR__ . '/.installed')) {
    die("CMS is already installed. Delete .installed file to reinstall.");
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db(DB_NAME);

$queries = [];

// 1. Users Table with 2FA support
$queries[] = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'author', 'subscriber') DEFAULT 'subscriber',
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT,
    two_factor_secret VARCHAR(32) DEFAULT NULL,
    two_factor_enabled TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 2. Posts Table with scheduling and custom types
$queries[] = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    post_type VARCHAR(50) DEFAULT 'post',
    status ENUM('draft', 'pending', 'published', 'scheduled', 'private', 'trash') DEFAULT 'draft',
    author_id INT,
    featured_image VARCHAR(255),
    comment_status ENUM('open', 'closed') DEFAULT 'open',
    ping_status ENUM('open', 'closed') DEFAULT 'open',
    password_protected VARCHAR(255) DEFAULT NULL,
    published_at TIMESTAMP NULL,
    scheduled_for TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    meta_data JSON,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_post_type (post_type),
    INDEX idx_status (status),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 3. Post Revisions (Version Control)
$queries[] = "CREATE TABLE IF NOT EXISTS post_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    author_id INT,
    revision_note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 4. Categories & Tags (Taxonomies)
$queries[] = "CREATE TABLE IF NOT EXISTS terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    type ENUM('category', 'tag', 'custom') DEFAULT 'category',
    parent_id INT DEFAULT NULL,
    description TEXT,
    term_order INT DEFAULT 0,
    meta_data JSON,
    FOREIGN KEY (parent_id) REFERENCES terms(id) ON DELETE SET NULL,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$queries[] = "CREATE TABLE IF NOT EXISTS term_relationships (
    object_id INT NOT NULL,
    term_id INT NOT NULL,
    taxonomy_order INT DEFAULT 0,
    PRIMARY KEY (object_id, term_id),
    FOREIGN KEY (object_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 5. Comments with threading
$queries[] = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    author_name VARCHAR(100),
    author_email VARCHAR(100),
    author_url VARCHAR(255),
    author_ip VARCHAR(45),
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    like_count INT DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 6. Media Library
$queries[] = "CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    file_size BIGINT,
    width INT,
    height INT,
    alt_text VARCHAR(255),
    caption TEXT,
    description TEXT,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    meta_data JSON,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mime (mime_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 7. Options Table
$queries[] = "CREATE TABLE IF NOT EXISTS options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(100) UNIQUE NOT NULL,
    option_value LONGTEXT,
    autoload TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 8. Plugins Table
$queries[] = "CREATE TABLE IF NOT EXISTS plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    version VARCHAR(20),
    description TEXT,
    author VARCHAR(100),
    is_active TINYINT(1) DEFAULT 0,
    settings JSON,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 9. Themes Table
$queries[] = "CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    version VARCHAR(20),
    description TEXT,
    author VARCHAR(100),
    screenshot VARCHAR(255),
    is_active TINYINT(1) DEFAULT 0,
    settings JSON,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 10. Audit Logs (Security)
$queries[] = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 11. Sessions Table (for 2FA and security)
$queries[] = "CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 12. Scheduled Tasks (Cron-like)
$queries[] = "CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(100) NOT NULL,
    schedule VARCHAR(50),
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    settings JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 13. Translations/Multi-language
$queries[] = "CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL,
    text_domain VARCHAR(50) DEFAULT 'default',
    original_text TEXT NOT NULL,
    translated_text TEXT,
    context VARCHAR(100),
    UNIQUE KEY unique_translation (language_code, text_domain, original_text(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// 14. Menu Navigation
$queries[] = "CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    location VARCHAR(50) DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$queries[] = "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    post_id INT DEFAULT NULL,
    menu_order INT DEFAULT 0,
    target VARCHAR(20) DEFAULT '_self',
    classes VARCHAR(255),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Execute all queries
foreach ($queries as $i => $query) {
    if (!$conn->query($query)) {
        die("Error executing query " . ($i + 1) . ": " . $conn->error);
    }
}

// Insert default admin user
$admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
$conn->query("INSERT INTO users (username, email, password_hash, role) VALUES 
    ('admin', 'admin@example.com', '$admin_pass', 'admin')");

// Insert default options
$options = [
    ['site_title', 'My World-Class CMS', 1],
    ['site_description', 'A professional CMS built with procedural PHP', 1],
    ['site_url', 'http://localhost', 1],
    ['admin_email', 'admin@example.com', 1],
    ['posts_per_page', 10, 1],
    ['default_theme', 'multipurpose', 1],
    ['default_language', 'en', 1],
    ['timezone', 'UTC', 1],
    ['date_format', 'F j, Y', 1],
    ['time_format', 'g:i a', 1],
    ['allow_registration', 0, 1],
    ['default_role', 'subscriber', 1],
    ['comment_moderation', 1, 1],
    ['require_name_email', 1, 1]
];

foreach ($options as $opt) {
    $conn->query("INSERT INTO options (option_name, option_value, autoload) VALUES 
        ('{$opt[0]}', '{$opt[1]}', {$opt[2]})");
}

// Insert default themes
$themes = [
    ['Multipurpose Pro', 'multipurpose', '1.0.0', 'Professional business theme', 'CMS Team', 'screenshot.png'],
    ['Blog Master', 'blog', '1.0.0', 'Perfect for bloggers', 'CMS Team', 'screenshot.png'],
    ['School Edge', 'school', '1.0.0', 'Educational institution theme', 'CMS Team', 'screenshot.png'],
    ['News Portal', 'news', '1.0.0', 'News and magazine theme', 'CMS Team', 'screenshot.png']
];

foreach ($themes as $theme) {
    $conn->query("INSERT INTO themes (name, slug, version, description, author, screenshot, is_active) VALUES 
        ('{$theme[0]}', '{$theme[1]}', '{$theme[2]}', '{$theme[3]}', '{$theme[4]}', '{$theme[5]}', " . ($theme[1] === 'multipurpose' ? 1 : 0) . ")");
}

// Insert default plugins
$plugins = [
    ['SEO Optimizer', 'seo-optimizer', '1.0.0', 'Advanced SEO tools', 'CMS Team'],
    ['Contact Forms', 'contact-forms', '1.0.0', 'Build beautiful forms', 'CMS Team'],
    ['Security Suite', 'security-suite', '1.0.0', 'Firewall and malware protection', 'CMS Team'],
    ['Backup Manager', 'backup-manager', '1.0.0', 'Automated backups', 'CMS Team'],
    ['Analytics Pro', 'analytics-pro', '1.0.0', 'Built-in analytics', 'CMS Team'],
    ['Social Share', 'social-share', '1.0.0', 'Social media integration', 'CMS Team']
];

foreach ($plugins as $plugin) {
    $conn->query("INSERT INTO plugins (name, slug, version, description, author, is_active) VALUES 
        ('{$plugin[0]}', '{$plugin[1]}', '{$plugin[2]}', '{$plugin[3]}', '{$plugin[4]}', 0)");
}

// Insert default categories
$categories = [
    ['Uncategorized', 'uncategorized', 'category', NULL],
    ['News', 'news', 'category', NULL],
    ['Technology', 'technology', 'category', NULL],
    ['Business', 'business', 'category', NULL]
];

foreach ($categories as $cat) {
    $parent = $cat[3] ? "(SELECT id FROM terms WHERE slug='{$cat[3]}')" : 'NULL';
    $conn->query("INSERT INTO terms (name, slug, type, parent_id) VALUES 
        ('{$cat[0]}', '{$cat[1]}', '{$cat[2]}', $parent)");
}

// Create uploads directory
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}
if (!is_dir(__DIR__ . '/uploads/media')) {
    mkdir(__DIR__ . '/uploads/media', 0755, true);
}

// Create mark file
file_put_contents(__DIR__ . '/.installed', time());

echo "<!DOCTYPE html>
<html>
<head>
    <title>Installation Complete</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { max-width: 500px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .success-icon { font-size: 60px; color: #28a745; }
    </style>
</head>
<body>
    <div class='card p-5 text-center'>
        <div class='success-icon'>✓</div>
        <h2 class='mt-3'>Installation Complete!</h2>
        <p class='text-muted'>Your world-class CMS has been successfully installed.</p>
        <hr>
        <div class='text-start'>
            <strong>Admin Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
        <div class='mt-4'>
            <a href='admin.php' class='btn btn-primary w-100'>Go to Admin Panel</a>
            <a href='index.php' class='btn btn-outline-secondary w-100 mt-2'>View Website</a>
        </div>
    </div>
</body>
</html>";

$conn->close();
