<?php
/**
 * CMS Setup Script - Creates database and default data
 */
require_once 'config.php';
mysqli_close($conn);

$root_conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
if (!$root_conn) die("Connection failed: " . mysqli_connect_error());

// Create database
mysqli_query($root_conn, "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
mysqli_select_db($root_conn, DB_NAME);

// Users table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','author','subscriber') DEFAULT 'subscriber',
    display_name VARCHAR(100), avatar VARCHAR(255), bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active','inactive','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Posts table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL, content LONGTEXT, excerpt TEXT,
    featured_image VARCHAR(255), author_id INT, category_id INT,
    status ENUM('draft','published','scheduled','archived') DEFAULT 'draft',
    post_type ENUM('post','page','custom') DEFAULT 'post', views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Categories table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL, description TEXT,
    parent_id INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Tags table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Post_tags table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS post_tags (
    post_id INT, tag_id INT, PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Comments table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY, post_id INT NOT NULL, user_id INT,
    parent_id INT DEFAULT 0, author_name VARCHAR(100), author_email VARCHAR(100),
    content TEXT NOT NULL, status ENUM('pending','approved','spam','trash') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Themes table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL, description TEXT, version VARCHAR(20),
    author VARCHAR(100), screenshot VARCHAR(255), is_active BOOLEAN DEFAULT FALSE,
    settings TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Plugins table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS plugins (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL, description TEXT, version VARCHAR(20),
    author VARCHAR(100), is_active BOOLEAN DEFAULT FALSE, settings TEXT,
    admin_only BOOLEAN DEFAULT TRUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Options table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS options (
    id INT AUTO_INCREMENT PRIMARY KEY, option_name VARCHAR(100) UNIQUE NOT NULL,
    option_value LONGTEXT, autoload BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Media table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255), mime_type VARCHAR(100), file_size INT,
    filepath VARCHAR(255), uploaded_by INT, alt_text VARCHAR(255), caption TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Menus table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL, location VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Menu_items table
mysqli_query($root_conn, "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY, menu_id INT NOT NULL, parent_id INT DEFAULT 0,
    label VARCHAR(100) NOT NULL, url VARCHAR(255), post_id INT, position INT DEFAULT 0,
    target VARCHAR(20) DEFAULT '_self', classes VARCHAR(255),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert admin user
$admin_pwd = password_hash('admin123', PASSWORD_DEFAULT);
mysqli_query($root_conn, "INSERT INTO users (username,email,password,role,display_name,status) VALUES ('admin','admin@example.com','$admin_pwd','admin','Administrator','active')");

// Insert categories
$cats = [['Uncategorized','uncategorized','Default category',0],['News','news','Latest news',0],['Blog','blog','Blog posts',0],['Events','events','Events',0],['Academics','academics','Academics',0]];
foreach($cats as $c) mysqli_query($root_conn, "INSERT INTO categories (name,slug,description,parent_id) VALUES ('{$c[0]}','{$c[1]}','{$c[2]}',{$c[3]})");

// Insert themes
$themes = [
    ['Multipurpose Pro','multipurpose','Professional multipurpose theme for business websites','1.0.0','CMS Team','multipurpose/screenshot.png',1,'{"hero_title":"Welcome","show_features":true}'],
    ['Blog Master','blog','Clean blogging platform','1.0.0','CMS Team','blog/screenshot.png',0,'{"sidebar":"right"}'],
    ['School Edge','school','School website theme','1.0.0','CMS Team','school/screenshot.png',0,'{"show_events":true}'],
    ['News Portal','news','News portal theme','1.0.0','CMS Team','news/screenshot.png',0,'{"breaking_news":true}']
];
foreach($themes as $t) mysqli_query($root_conn, "INSERT INTO themes (name,slug,description,version,author,screenshot,is_active,settings) VALUES ('{$t[0]}','{$t[1]}','{$t[2]}','{$t[3]}','{$t[4]}','{$t[5]}',{$t[6]},'{$t[7]}')");

// Insert plugins (all admin_only=1)
$plugins = [
    ['SEO Optimizer','seo-optimizer','SEO tools and sitemaps','1.0.0','CMS Team',1,'{}'],
    ['Contact Forms','contact-forms','Form builder','1.0.0','CMS Team',1,'{}'],
    ['Security Suite','security-suite','Security features','1.0.0','CMS Team',1,'{}'],
    ['Backup Manager','backup-manager','Automated backups','1.0.0','CMS Team',1,'{}'],
    ['Analytics Pro','analytics-pro','Visitor analytics','1.0.0','CMS Team',1,'{}'],
    ['Social Share','social-share','Social sharing buttons','1.0.0','CMS Team',1,'{}']
];
foreach($plugins as $p) mysqli_query($root_conn, "INSERT INTO plugins (name,slug,description,version,author,is_active,admin_only,settings) VALUES ('{$p[0]}','{$p[1]}','{$p[2]}','{$p[3]}','{$p[4]}',{$p[5]},1,'{$p[6]}')");

// Insert options
$opts = [['site_title','My CMS',1],['site_description','Just another CMS',1],['posts_per_page','10',1],['allow_comments','1',1]];
foreach($opts as $o) mysqli_query($root_conn, "INSERT INTO options (option_name,option_value,autoload) VALUES ('{$o[0]}','{$o[1]}',{$o[2]})");

// Insert menu
mysqli_query($root_conn, "INSERT INTO menus (name,slug,location) VALUES ('Main Menu','main-menu','header')");

// Sample posts
mysqli_query($root_conn, "INSERT INTO posts (title,slug,content,excerpt,author_id,category_id,status,post_type,published_at) VALUES ('Welcome','welcome-to-cms','<h2>Welcome!</h2><p>Your CMS is ready.</p>','Welcome message',1,1,'published','page',NOW())");

mysqli_close($root_conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>CMS Installation Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;display:flex;align-items:center;justify-content:center;}.card{border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.2);}</style>
</head>
<body>
<div class="container"><div class="row justify-content-center"><div class="col-md-6">
<div class="card p-5 text-center">
<h1 class="text-success mb-3">✓ Installation Complete!</h1>
<p class="lead">Your CMS is ready with 4 themes and 6 plugins.</p>
<div class="alert alert-info text-start">
<strong>Admin Login:</strong><br>Username: <code>admin</code><br>Password: <code>admin123</code>
</div>
<a href="admin.php" class="btn btn-primary btn-lg w-100 mb-2">Go to Admin Panel</a>
<a href="index.php" class="btn btn-outline-secondary w-100">View Website</a>
<p class="text-muted mt-3 small">Delete setup.php after installation!</p>
</div>
</div></div></div>
</body></html>
