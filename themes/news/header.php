<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --news-red: #c0392b; --news-dark: #2c3e50; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .top-header { background: var(--news-dark); color: white; padding: 10px 0; }
        .breaking-news { background: var(--news-red); color: white; padding: 10px 0; overflow: hidden; white-space: nowrap; }
        .breaking-label { font-weight: bold; margin-right: 20px; }
        .main-header { background: white; padding: 20px 0; border-bottom: 4px solid var(--news-red); }
        .site-title { font-size: 2.5rem; font-weight: bold; color: var(--news-dark); }
        .nav-section { background: var(--news-dark); }
        .nav-section .nav-link { color: white !important; padding: 15px 20px !important; }
        .nav-section .nav-link:hover { background: var(--news-red) !important; }
        .featured-card { position: relative; color: white; }
        .featured-card img { width: 100%; height: 400px; object-fit: cover; }
        .featured-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); padding: 30px; }
        .news-grid-item { background: white; margin-bottom: 20px; }
        .news-grid-item img { width: 100%; height: 200px; object-fit: cover; }
        .category-badge { background: var(--news-red); color: white; padding: 3px 10px; font-size: 0.75rem; }
        footer { background: var(--news-dark); color: white; padding: 40px 0 20px; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="container d-flex justify-content-between">
            <span><?php echo date('l, F j, Y'); ?></span>
            <span><i class="fas fa-rss"></i> Subscribe | <i class="fas fa-share-alt"></i> Share</span>
        </div>
    </div>
    <div class="breaking-news">
        <div class="container">
            <span class="breaking-label"><i class="fas fa-bolt"></i> BREAKING:</span>
            <marquee>Welcome to <?php echo $site_title; ?> - Your trusted news source!</marquee>
        </div>
    </div>
    <header class="main-header">
        <div class="container text-center">
            <h1 class="site-title"><?php echo $site_title; ?></h1>
            <p class="text-muted"><?php echo $site_description; ?></p>
        </div>
    </header>
    <nav class="nav-section">
        <div class="container">
            <ul class="nav">
                <?php foreach($menu_items as $item): ?>
                <li class="nav-item"><a class="nav-link" href="<?php echo $item['url'] ?: 'index.php?post=' . $item['post_slug']; ?>"><?php echo $item['label']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
