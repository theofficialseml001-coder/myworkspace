<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? $post['title'] . ' - ' : ''; ?><?php echo $site_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Georgia', serif; background: #fafafa; }
        .navbar { background: white !important; border-bottom: 1px solid #eee; }
        .blog-header { text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .main-content { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .post-item { background: white; border-radius: 8px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .post-meta { color: #888; font-size: 0.9rem; margin-bottom: 15px; }
        .sidebar-widget { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        footer { background: #333; color: white; padding: 30px 0; text-align: center; margin-top: 60px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo $site_title; ?></a>
            <div class="ms-auto">
                <?php foreach($menu_items as $item): ?>
                <a class="btn btn-link" href="<?php echo $item['url'] ?: 'index.php?post=' . $item['post_slug']; ?>"><?php echo $item['label']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
