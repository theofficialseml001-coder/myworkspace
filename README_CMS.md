# Professional CMS with Bootstrap UI

A WordPress-like Content Management System built with procedural PHP and MySQLi.

## Features

- **4 Pre-installed Professional Themes:**
  - **Multipurpose Pro** - Business/corporate websites with hero sections, features, testimonials
  - **Blog Master** - Clean blogging platform focused on readability
  - **School Edge** - Educational institution website with academics, events sections
  - **News Portal** - News site with breaking news ticker, featured stories

- **6 Integrated Plugins (Admin-Only Management):**
  - SEO Optimizer
  - Contact Forms
  - Security Suite
  - Backup Manager
  - Analytics Pro
  - Social Share

- **User Roles & Permissions:**
  - Admin: Full access including themes, plugins, settings, users
  - Editor: Content management
  - Author: Create and edit own posts
  - Subscriber: View content only

## Installation

1. Configure database settings in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'cms_db');
   ```

2. Run the setup script by visiting: `http://localhost/setup.php`

3. Default admin credentials:
   - Username: `admin`
   - Password: `admin123`

4. Delete `setup.php` after installation for security

## File Structure

```
/workspace
├── config.php          # Configuration file
├── index.php           # Front-end controller
├── admin.php           # Admin panel
├── setup.php           # Installation script
├── includes/
│   └── functions.php   # Helper functions
├── themes/
│   ├── multipurpose/   # Multipurpose theme
│   ├── blog/           # Blog theme
│   ├── school/         # School theme
│   └── news/           # News theme
├── plugins/            # Plugin directory
└── uploads/            # Media uploads
```

## Technology Stack

- **Frontend:** Bootstrap 5, Font Awesome
- **Backend:** Procedural PHP 7.4+
- **Database:** MySQL with MySQLi (procedural)

## Security Features

- Password hashing with bcrypt
- SQL injection prevention via mysqli_real_escape_string
- XSS protection via htmlspecialchars
- Session-based authentication
- Admin-only plugin management
- Role-based access control

## Admin Panel Features

- Dashboard with statistics
- Post/Page management
- Media library
- Theme switcher
- Plugin manager (admin only)
- Settings configuration (admin only)
- User management (admin only)
