# World-Class CMS

A professional, feature-rich Content Management System built with procedural PHP and MySQLi.

## Features

### Core Architecture
- ✅ Procedural PHP architecture (no frameworks)
- ✅ MySQLi prepared statements for security
- ✅ Hook system (Actions & Filters like WordPress)
- ✅ Nonce-based CSRF protection
- ✅ Custom Post Types API
- ✅ Asset management (enqueue scripts/styles)
- ✅ Audit logging system
- ✅ Revision control for posts

### Security Features
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ CSRF tokens (nonce system)
- ✅ Password hashing (bcrypt)
- ✅ Role-based access control
- ✅ Session security headers
- ✅ 2FA support structure

### Admin Panel
- ✅ Modern Bootstrap 5 UI
- ✅ Dashboard with statistics
- ✅ Post/Page editor with TinyMCE
- ✅ Media library management
- ✅ Plugin manager
- ✅ Theme switcher
- ✅ User management
- ✅ Settings panel
- ✅ Audit log viewer

### Frontend
- ✅ Theme system
- ✅ Template hierarchy
- ✅ Search functionality
- ✅ Category/tag archives
- ✅ Responsive design
- ✅ SEO-friendly URLs

### Database Structure
- Users (with roles & 2FA)
- Posts (with custom types)
- Post Revisions
- Terms (categories/tags)
- Comments (threaded)
- Media Library
- Options
- Plugins
- Themes
- Audit Logs
- User Sessions
- Scheduled Tasks
- Translations
- Menus

## Installation

1. Configure `config.php` with your database credentials
2. Run `setup.php` in your browser
3. Login with admin/admin123
4. Start building!

## File Structure

```
cms/
├── config.php           # Configuration
├── setup.php            # Installation script
├── index.php            # Frontend controller
├── admin.php            # Admin panel
├── includes/
│   └── functions.php    # Core functions
├── themes/
│   └── multipurpose/    # Default theme
│       ├── header.php
│       ├── footer.php
│       ├── home.php
│       ├── single.php
│       ├── page.php
│       ├── search.php
│       ├── category.php
│       └── 404.php
└── uploads/             # Media files
```

## Usage Examples

### Adding a Hook
```php
// Add action
add_action('init', 'my_custom_function');

// Add filter
add_filter('the_content', 'modify_content');
function modify_content($content) {
    return $content . '<p>Custom footer</p>';
}
```

### Creating Custom Post Type
```php
register_post_type('product', [
    'label' => 'Products',
    'public' => true
]);
```

### Enqueue Assets
```php
enqueue_script('my-script', '/js/custom.js');
enqueue_style('my-style', '/css/custom.css');
```

### Security
```php
// Create nonce
wp_nonce_field('my_action');

// Verify nonce
verify_nonce($_POST['_wpnonce'], 'my_action');
```

## License
MIT License
