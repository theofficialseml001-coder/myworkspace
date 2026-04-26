    <div class="hero-banner">
        <div class="container">
            <h1 class="display-4">Welcome to <?php echo $site_title; ?></h1>
            <p class="lead"><?php echo $site_description; ?></p>
            <a href="#academics" class="btn btn-warning btn-lg">Learn More</a>
        </div>
    </div>
    <div class="quick-links text-center">
        <div class="container">
            <a href="#"><i class="fas fa-book"></i> Academics</a>
            <a href="#"><i class="fas fa-calendar"></i> Events</a>
            <a href="#"><i class="fas fa-users"></i> Admissions</a>
            <a href="#"><i class="fas fa-bullhorn"></i> Announcements</a>
        </div>
    </div>
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Latest News & Updates</h2>
            <div class="row">
                <?php foreach(array_slice($posts, 0, 3) as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card news-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo get_excerpt($post['content'], 100); ?></p>
                            <small class="text-muted"><i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?></small>
                        </div>
                        <div class="card-footer bg-white border-0"><a href="index.php?post=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary">Read More</a></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section id="academics" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Academic Programs</h2>
            <div class="row text-center">
                <div class="col-md-3"><div class="p-4"><i class="fas fa-child fa-3x text-warning mb-3"></i><h5>Elementary</h5></div></div>
                <div class="col-md-3"><div class="p-4"><i class="fas fa-user-graduate fa-3x text-warning mb-3"></i><h5>Middle School</h5></div></div>
                <div class="col-md-3"><div class="p-4"><i class="fas fa-graduation-cap fa-3x text-warning mb-3"></i><h5>High School</h5></div></div>
                <div class="col-md-3"><div class="p-4"><i class="fas fa-laptop fa-3x text-warning mb-3"></i><h5>Online Learning</h5></div></div>
            </div>
        </div>
    </section>
