-- Video Conference SFU Solution Database Schema
-- Procedural PHP with MySQLi

CREATE DATABASE IF NOT EXISTS videoconf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE videoconf_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    phone VARCHAR(20),
    organization VARCHAR(100),
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Meetings Table
CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    host_id INT NOT NULL,
    meeting_type ENUM('instant', 'scheduled', 'recurring') DEFAULT 'instant',
    scheduled_start DATETIME,
    scheduled_end DATETIME,
    duration INT DEFAULT 60,
    password VARCHAR(255),
    is_public TINYINT(1) DEFAULT 1,
    allow_recording TINYINT(1) DEFAULT 1,
    allow_screen_share TINYINT(1) DEFAULT 1,
    allow_chat TINYINT(1) DEFAULT 1,
    allow_file_share TINYINT(1) DEFAULT 1,
    allow_whiteboard TINYINT(1) DEFAULT 1,
    max_participants INT DEFAULT 100,
    status ENUM('waiting', 'active', 'ended', 'cancelled') DEFAULT 'waiting',
    recording_enabled TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_host_id (host_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Participants Table
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NULL,
    display_name VARCHAR(100) NOT NULL,
    join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leave_time TIMESTAMP NULL,
    role ENUM('host', 'co-host', 'participant', 'presenter') DEFAULT 'participant',
    is_muted TINYINT(1) DEFAULT 0,
    is_video_off TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- Chat Messages Table
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    sender_id INT NULL,
    sender_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'file', 'system') DEFAULT 'text',
    file_url VARCHAR(255),
    is_private TINYINT(1) DEFAULT 0,
    recipient_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Recordings Table
CREATE TABLE IF NOT EXISTS recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT,
    duration INT,
    format VARCHAR(10) DEFAULT 'mp4',
    status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- Shared Files Table
CREATE TABLE IF NOT EXISTS shared_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(50),
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id)
) ENGINE=InnoDB;

-- Whiteboard Sessions Table
CREATE TABLE IF NOT EXISTS whiteboard_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    board_data LONGTEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id)
) ENGINE=InnoDB;

-- Screen Share Sessions Table
CREATE TABLE IF NOT EXISTS screen_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    status ENUM('active', 'ended') DEFAULT 'active',
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id)
) ENGINE=InnoDB;

-- Invitations Table
CREATE TABLE IF NOT EXISTS invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('pending', 'accepted', 'expired') DEFAULT 'pending',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_meeting_id (meeting_id)
) ENGINE=InnoDB;

-- Admin: Subscription Plans (SaaS)
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(50) NOT NULL,
    plan_type ENUM('free', 'basic', 'pro', 'enterprise') DEFAULT 'free',
    max_participants INT DEFAULT 100,
    max_duration INT DEFAULT 60,
    max_recordings INT DEFAULT 5,
    recording_storage_gb INT DEFAULT 5,
    features TEXT,
    price_monthly DECIMAL(10,2) DEFAULT 0.00,
    price_yearly DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plan_type (plan_type)
) ENGINE=InnoDB;

-- Admin: User Subscriptions
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'trial',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    cancelled_at TIMESTAMP NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    category VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB;

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value, setting_type, category) VALUES
('site_name', 'VideoConf Pro', 'string', 'general'),
('site_description', 'Professional Video Conferencing Solution', 'string', 'general'),
('max_file_size', '104857600', 'number', 'uploads'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar', 'string', 'uploads'),
('recording_retention_days', '30', 'number', 'recordings'),
('default_meeting_duration', '60', 'number', 'meetings'),
('enable_registration', '1', 'boolean', 'general'),
('enable_guest_join', '1', 'boolean', 'meetings');

-- Insert Default Subscription Plans
INSERT INTO subscription_plans (plan_name, plan_type, max_participants, max_duration, max_recordings, recording_storage_gb, features, price_monthly, price_yearly) VALUES
('Free', 'free', 100, 40, 0, 0, 'Basic video conferencing, screen sharing, chat', 0.00, 0.00),
('Basic', 'basic', 200, 120, 10, 25, 'HD video, recording, file sharing, whiteboard', 9.99, 99.99),
('Pro', 'pro', 500, 240, 50, 100, 'Full HD, analytics, priority support, custom branding', 19.99, 199.99),
('Enterprise', 'enterprise', 1000, 480, -1, 500, 'Unlimited everything, SSO, dedicated support, SLA', 49.99, 499.99);

-- Insert Default Admin User (password: admin123)
INSERT INTO users (username, email, password, full_name, role, status, email_verified) VALUES
('admin', 'admin@videoconf.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active', 1);
