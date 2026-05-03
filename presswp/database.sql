-- presswp_database.sql

CREATE DATABASE IF NOT EXISTS presswp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE presswp;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'subscriber') DEFAULT 'subscriber',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin (Password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@presswp.com', 'admin');

-- Posts/Pages Table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    type ENUM('post', 'page') DEFAULT 'post',
    status ENUM('publish', 'draft') DEFAULT 'publish',
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Site Configuration
CREATE TABLE options (
    option_name VARCHAR(100) PRIMARY KEY,
    option_value LONGTEXT
);

INSERT INTO options (option_name, option_value) VALUES 
('site_title', 'PressWP Clone'),
('active_theme', 'multipurpose'),
('plugins_locked', '1');

-- Plugins Registry
CREATE TABLE plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    description TEXT
);

INSERT INTO plugins (name, slug, is_active, description) VALUES 
('SEO Optimizer', 'seo-optimizer', 1, 'Automatically generates meta tags.'),
('Security Firewall', 'security-firewall', 1, 'Blocks malicious requests.'),
('Contact Forms', 'contact-forms', 1, 'Manage contact forms globally.'),
('Analytics Tracker', 'analytics-tracker', 1, 'Tracks visitor statistics.');

-- Sample Content for Themes
INSERT INTO posts (title, slug, content, type, status, author_id) VALUES 
('Welcome to Multipurpose', 'home-multi', '<h1>We build everything.</h1><p>Professional services for all industries.</p>', 'page', 'publish', 1),
('Latest Tech News', 'news-1', '<p>The world of AI is changing rapidly...</p>', 'post', 'publish', 1),
('School Enrollment Open', 'school-news', '<p>Admissions for the 2024 academic year are now open.</p>', 'post', 'publish', 1),
('My First Blog Post', 'blog-1', '<p>This is a personal story about coding...</p>', 'post', 'publish', 1);
