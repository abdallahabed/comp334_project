<?php 
require_once 'includes/config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="wrapper">
    <?php include 'includes/nav.php'; ?>

    <main class="main-content">
        
        <section class="hero-section">
            <h1 class="heading-primary">Welcome to Freelance Marketplace</h1>
            <p class="hero-subtitle">Connect with top experts to get your job done.</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="hero-btns">
                    <a href="register.php" class="btn btn-primary">Join as Freelancer/Client</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                </div>
            <?php else: ?>
                <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['first_name']) ?></strong>!</p>
            <?php endif; ?>
        </section>

        <div class="student-info-card">
            <h3 class="info-title">Developer Information</h3>
            <div class="info-details">
                <p><strong>Name:</strong> Abdallah Abed</p>
                <p><strong>Student ID:</strong> 1210802</p>
                <p><strong>Course:</strong> Web Applications and Technologies (COMP 334)</p>
            </div>
        </div>

        <div class="section-header">
            <h2 class="heading-secondary">Featured Services</h2>
            <a href="browse_services.php" class="view-all">View All →</a>
        </div>

        <div class="services-grid">
            <?php
            //show some services on the home page
            $stmt = $pdo->query("SELECT s.*, u.first_name, u.last_name FROM services s 
                                JOIN users u ON s.freelancer_id = u.user_id 
                                WHERE s.featured_status = 'Yes' AND s.status = 'Active' 
                                LIMIT 4");
            $featured = $stmt->fetchAll();

            if ($featured):
                foreach ($featured as $service): ?>
                    <a href="service_detail.php?id=<?= $service['service_id'] ?>" class="service-card">
                        <img src="<?= htmlspecialchars($service['image_1']) ?>" alt="Service" class="service-image">
                        <div class="service-content">
                            <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
                            <p class="service-meta">By <?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?></p>
                            <span class="service-price">$<?= number_format($service['price'], 2) ?></span>
                        </div>
                    </a>
                <?php endforeach; 
            else: ?>
                <p class="message message-info">Browse our directory to find amazing services.</p>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>