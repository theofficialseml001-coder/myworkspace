<?php
/**
 * Main Front-end Controller
 * Loads theme and displays content
 */

require_once 'config.php';
require_once 'includes/functions.php';

// Get active theme
$active_theme = get_active_theme($conn);
if (!$active_theme) {
    die("No active theme found. Please run setup.php first.");
}

$theme_path = 'themes/' . $active_theme['slug'];
$theme_settings = json_decode($active_theme['settings'], true);

// Get site options
$site_title = get_option($conn, 'site_title', SITE_NAME);
$site_description = get_option($conn, 'site_description', '');

// Determine what to display
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$slug = isset($_GET['post']) ? $_GET['post'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_slug = isset($_GET['category']) ? $_GET['category'] : '';

// Get menu items
$menu_items = get_menu_items($conn, 'main-menu');
$categories = get_categories($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = (int)get_option($conn, 'posts_per_page', 10);
$offset = ($page - 1) * $posts_per_page;

// Load appropriate content
$content = '';
$template = 'home';

if ($search_query) {
    $posts = search_posts($conn, $search_query, $posts_per_page);
    $template = 'search';
} elseif ($category_slug) {
    $sql = "SELECT id FROM categories WHERE slug = '" . sanitize_input($category_slug) . "'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $cat = mysqli_fetch_assoc($result);
        $sql = "SELECT p.*, u.display_name as author_name FROM posts p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.category_id = {$cat['id']} AND p.status = 'published' 
                ORDER BY p.published_at DESC LIMIT $posts_per_page OFFSET $offset";
        $result = mysqli_query($conn, $sql);
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        $template = 'category';
    }
} elseif ($slug) {
    $post = get_post_by_slug($conn, $slug);
    if ($post) {
        increment_post_views($conn, $post['id']);
        $template = 'single';
    } else {
        header('HTTP/1.0 404 Not Found');
        $template = '404';
    }
} else {
    $posts = get_posts($conn, $posts_per_page, $offset, 'published');
    $total_posts = get_post_count($conn);
    $total_pages = ceil($total_posts / $posts_per_page);
    $template = 'home';
}

// Load theme header
include $theme_path . '/header.php';

// Load theme template
switch ($template) {
    case 'single':
        include $theme_path . '/single.php';
        break;
    case 'search':
        include $theme_path . '/search.php';
        break;
    case 'category':
        include $theme_path . '/category.php';
        break;
    case '404':
        include $theme_path . '/404.php';
        break;
    default:
        include $theme_path . '/home.php';
}

// Load theme footer
include $theme_path . '/footer.php';

mysqli_close($conn);
?>
