# PressWP - WordPress Clone

A lightweight WordPress clone built with procedural PHP and MySQLi, featuring four professionally designed themes and integrated plugins with admin-only management.

## Features

- **Four Pre-installed Themes:**
  - **Multipurpose** - Complex business/corporate website
  - **Blog** - Clean blogging platform
  - **School** - Educational institution website
  - **News** - Professional news portal

- **Integrated Plugins** (Admin-managed only):
  - SEO Optimizer
  - Security Firewall
  - Contact Forms
  - Analytics Tracker

- **Bootstrap 5 UI** - Responsive, modern interface
- **Procedural PHP** - No OOP, pure procedural code
- **MySQLi Procedural** - Database interactions using procedural mode
- **Admin Protection** - Only administrators can manage plugins and settings

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server

### Setup Instructions

1. **Copy files to your web server:**
   ```bash
   cp -r presswp /var/www/html/
   ```

2. **Create the database:**
   ```bash
   mysql -u root -p < presswp/database.sql
   ```

3. **Configure database connection:**
   Edit `presswp/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'presswp');
   ```

4. **Set proper permissions:**
   ```bash
   chmod -R 755 /var/www/html/presswp
   ```

5. **Access the site:**
   - Frontend: `http://localhost/presswp/`
   - Admin Panel: `http://localhost/presswp/admin/login.php`

## Default Credentials

- **Username:** `admin`
- **Password:** `admin123`

## Directory Structure

```
presswp/
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── login.php          # Login page
│   └── logout.php         # Logout handler
├── includes/              # Include files
├── themes/
│   ├── multipurpose/      # Business theme
│   ├── blog/              # Blog theme
│   ├── school/            # School theme
│   └── news/              # News theme
├── config.php             # Configuration file
├── index.php              # Main entry point
└── database.sql           # Database schema
```

## Theme Switching

Administrators can switch between themes from the admin dashboard:
1. Log in to the admin panel
2. Navigate to Dashboard
3. Use the "Change Theme" dropdown
4. Select desired theme and click "Change Theme"

## Security Notes

- Plugin management is locked to administrators only
- All database queries use prepared statements where applicable
- Output is escaped using `esc_html()` function
- Session-based authentication for admin access

## License

MIT License - Free to use and modify.
