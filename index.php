<?php
/**
 * Main Frontend Controller
 * World-Class CMS with Theme System
 */

require_once __DIR__ . '/includes/functions.php';

// Get active theme
$active_theme = get_option('default_theme', 'multipurpose');
$theme_path = __DIR__ . "/themes/$active_theme";

// Check if theme exists
if (!is_dir($theme_path)) {
    $active_theme = 'multipurpose';
    $theme_path = __DIR__ . "/themes/$active_theme";
}

// Parse request
$request = $_GET['page'] ?? 'home';
$post_slug = $_GET['post'] ?? null;
$category_slug = $_GET['category'] ?? null;
$search_query = $_GET['s'] ?? null;

// Determine template to load
$template = 'home';

if ($search_query) {
    $template = 'search';
} elseif ($post_slug) {
    $template = 'single';
} elseif ($category_slug) {
    $template = 'category';
} elseif ($request && $request !== 'home') {
    // Try to find page by slug
    global $cms_db;
    $stmt = db_query("SELECT * FROM posts WHERE slug=? AND post_type='page' AND status='published'", [$request]);
    $page = db_fetch($stmt);
    if ($page) {
        $template = 'page';
        $GLOBALS['current_page'] = $page;
    } else {
        $template = '404';
    }
}

// Load theme header
if (file_exists("$theme_path/header.php")) {
    include "$theme_path/header.php";
} else {
    echo "<h1>Theme not found</h1>";
    exit;
}

// Load template
$template_file = "$theme_path/{$template}.php";
if (file_exists($template_file)) {
    include $template_file;
} else {
    echo "<div class='container py-5'><h2>Template not found: {$template}</h2></div>";
}

// Load theme footer
if (file_exists("$theme_path/footer.php")) {
    include "$theme_path/footer.php";
}
