<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_option('site_title'); ?> - <?php echo get_option('site_description'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2c3e50; --secondary: #3498db; --accent: #e74c3c; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar { background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%); padding: 1rem 0; }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: white !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; margin: 0 0.5rem; }
        .nav-link:hover { color: white !important; }
        .hero { background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%); color: white; padding: 5rem 0; text-align: center; }
        .feature-box { padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s; height: 100%; }
        .feature-box:hover { transform: translateY(-10px); }
        .section { padding: 4rem 0; }
        .section-title { text-align: center; margin-bottom: 3rem; }
        .section-title h2 { font-weight: 700; color: var(--primary); }
        .footer { background: var(--primary); color: white; padding: 3rem 0; }
        .footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .btn-primary { background: var(--secondary); border: none; padding: 0.75rem 2rem; }
        .card { border: none; box-shadow: 0 3px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-laugh-wink me-2"></i><?php echo get_option('site_title'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>
