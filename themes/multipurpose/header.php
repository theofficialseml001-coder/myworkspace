<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? $post['title'] . ' - ' : ''; ?><?php echo $site_title; ?></title>
    <meta name="description" content="<?php echo isset($post) && $post['excerpt'] ? $post['excerpt'] : $site_description; ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Theme CSS -->
    <link href="<?php echo $theme_path; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-weight: 700; color: var(--primary-color) !important; }
        .hero-section { 
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%); 
            color: white; 
            padding: 100px 0; 
        }
        .feature-box { 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            transition: transform 0.3s;
        }
        .feature-box:hover { transform: translateY(-5px); }
        .feature-icon { 
            font-size: 2.5rem; 
            color: var(--primary-color); 
            margin-bottom: 20px; 
        }
        .testimonial-card { 
            background: var(--light-color); 
            padding: 30px; 
            border-radius: 10px; 
            margin: 20px 0; 
        }
        .cta-section { 
            background: var(--primary-color); 
            color: white; 
            padding: 60px 0; 
        }
        .post-card { 
            border: none; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            transition: transform 0.3s;
        }
        .post-card:hover { transform: translateY(-5px); }
        .post-card img { height: 200px; object-fit: cover; }
        .sidebar-widget { 
            background: var(--light-color); 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
        }
        footer { 
            background: var(--dark-color); 
            color: white; 
            padding: 40px 0 20px; 
        }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
        footer a:hover { color: white; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo $site_title; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach($menu_items as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $item['url'] ?: 'index.php?post=' . $item['post_slug']; ?>"><?php echo $item['label']; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <form class="d-flex ms-3" action="index.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search..." value="<?php echo isset($search_query) ? htmlspecialchars($search_query) : ''; ?>">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>
