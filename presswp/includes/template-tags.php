<?php
/**
 * PressWP - WordPress Clone
 * includes/template-tags.php - Theme Template Tags
 * Procedural PHP implementation for theme functions
 */

/**
 * Display Site Title
 */
function wp_title() {
    echo esc_html(get_option('site_title'));
}

/**
 * Get Site Title
 * @return string
 */
function get_wp_title() {
    return get_option('site_title');
}

/**
 * Display Header
 */
function wp_header() {
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php
}

/**
 * Display Footer Scripts
 */
function wp_footer() {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
}

/**
 * Display Navigation Menu
 * @param string $location
 */
function wp_nav_menu($location = 'primary') {
    ?>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url(); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="#blog">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <?php if (is_admin()): ?>
        <li class="nav-item"><a class="nav-link btn btn-primary px-3" href="<?php echo admin_url('dashboard.php'); ?>">Admin Panel</a></li>
        <?php endif; ?>
    </ul>
    <?php
}

/**
 * The Loop - Display Posts
 * @param array $posts
 */
function the_loop($posts) {
    if (empty($posts)) {
        echo '<p>No posts found.</p>';
        return;
    }
    
    foreach ($posts as $post) {
        the_post($post);
    }
}

/**
 * Display Single Post
 * @param array $post
 */
function the_post($post) {
    ?>
    <article class="post">
        <h2><?php echo esc_html($post['title']); ?></h2>
        <div class="post-meta">
            <span>Published on <?php echo format_date($post['created_at']); ?></span>
        </div>
        <div class="post-content">
            <?php echo nl2br(esc_html($post['content'])); ?>
        </div>
    </article>
    <?php
}

/**
 * Display Post Title
 * @param array $post
 */
function the_title($post) {
    echo esc_html($post['title']);
}

/**
 * Display Post Content
 * @param array $post
 */
function the_content($post) {
    echo nl2br(esc_html($post['content']));
}

/**
 * Display Post Excerpt
 * @param array $post
 * @param int $length
 */
function the_excerpt($post, $length = 150) {
    echo get_excerpt($post['content'], $length);
}

/**
 * Display Post Date
 * @param array $post
 * @param string $format
 */
function the_date($post, $format = 'F j, Y') {
    echo format_date($post['created_at'], $format);
}

/**
 * Display Author Name
 * @param int $author_id
 */
function the_author($author_id) {
    $user = get_user_by_id($author_id);
    if ($user) {
        echo esc_html($user['username']);
    } else {
        echo 'Admin';
    }
}

/**
 * Display Categories
 */
function the_categories() {
    echo '<span class="badge bg-secondary">General</span>';
}

/**
 * Display Tags
 */
function the_tags() {
    echo '<span class="badge bg-light text-dark">tag1</span> ';
    echo '<span class="badge bg-light text-dark">tag2</span>';
}

/**
 * Display Pagination
 * @param int $current_page
 * @param int $total_pages
 */
function the_pagination($current_page = 1, $total_pages = 1) {
    if ($total_pages <= 1) {
        return;
    }
    ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php
}

/**
 * Display Sidebar Widgets
 */
function wp_sidebar() {
    ?>
    <div class="sidebar-widget">
        <h4 class="widget-title">About</h4>
        <p>Welcome to our website! We provide quality content and services.</p>
    </div>
    
    <div class="sidebar-widget">
        <h4 class="widget-title">Categories</h4>
        <ul class="list-unstyled">
            <li><a href="#" class="text-decoration-none">News</a></li>
            <li><a href="#" class="text-decoration-none">Technology</a></li>
            <li><a href="#" class="text-decoration-none">Lifestyle</a></li>
        </ul>
    </div>
    
    <div class="sidebar-widget">
        <h4 class="widget-title">Recent Posts</h4>
        <?php
        $recent_posts = get_recent_posts(5);
        foreach ($recent_posts as $post):
        ?>
        <div class="mb-2">
            <a href="#" class="text-decoration-none"><?php echo esc_html($post['title']); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Display Search Form
 */
function wp_search_form() {
    ?>
    <form class="d-flex" role="search" action="<?php echo site_url(); ?>" method="GET">
        <input class="form-control me-2" type="search" name="s" placeholder="Search..." aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
    <?php
}

/**
 * Display Breadcrumb
 */
function wp_breadcrumb() {
    ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Current Page</li>
        </ol>
    </nav>
    <?php
}

/**
 * Check if Active Plugin
 * @param string $slug
 * @return bool
 */
function is_plugin_active($slug) {
    $plugin = get_plugin_by_slug($slug);
    return ($plugin && $plugin['is_active'] == 1);
}

/**
 * Display Admin Bar
 */
function wp_admin_bar() {
    if (!is_admin()) {
        return;
    }
    ?>
    <div class="admin-bar fixed-top bg-dark text-white py-2">
        <div class="container d-flex justify-content-between">
            <span><i class="fas fa-cog me-2"></i>PressWP Admin</span>
            <div>
                <a href="<?php echo admin_url('dashboard.php'); ?>" class="text-white me-3">Dashboard</a>
                <a href="<?php echo admin_url('logout.php'); ?>" class="text-white">Logout</a>
            </div>
        </div>
    </div>
    <?php
}

?>
