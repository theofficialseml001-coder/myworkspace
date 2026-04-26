<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --school-primary: #1e3a5f; --school-secondary: #c9a227; }
        body { font-family: Arial, sans-serif; }
        .top-bar { background: var(--school-primary); color: white; padding: 10px 0; font-size: 0.9rem; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { color: var(--school-primary) !important; font-weight: bold; font-size: 1.5rem; }
        .hero-banner { background: linear-gradient(rgba(30,58,95,0.9), rgba(30,58,95,0.9)), url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><rect fill=\"%23ddd\" width=\"100\" height=\"100\"/></svg>'); background-size: cover; color: white; padding: 80px 0; text-align: center; }
        .quick-links { background: var(--school-secondary); padding: 20px 0; }
        .quick-links a { color: white; margin: 0 15px; text-decoration: none; }
        .section-title { color: var(--school-primary); border-bottom: 3px solid var(--school-secondary); display: inline-block; padding-bottom: 10px; margin-bottom: 30px; }
        .news-card { border-left: 4px solid var(--school-secondary); }
        footer { background: var(--school-primary); color: white; padding: 40px 0 20px; }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container d-flex justify-content-between">
            <span><i class="fas fa-phone"></i> (555) 123-4567 | <i class="fas fa-envelope"></i> info@school.edu</span>
            <span><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 4:00 PM</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-graduation-cap"></i> <?php echo $site_title; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach($menu_items as $item): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $item['url'] ?: 'index.php?post=' . $item['post_slug']; ?>"><?php echo $item['label']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
