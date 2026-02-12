<?php
require_once 'includes/config.php';

// select all the categoris data 
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $catStmt->fetchAll();

// 
$sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo 
        FROM services s 
        JOIN users u ON s.freelancer_id = u.user_id 
        WHERE s.status = 'Active'";

$params = [];

// Filter data based on user search
if (!empty($_GET['search'])) {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = "%" . $_GET['search'] . "%";
    $params[] = "%" . $_GET['search'] . "%";
}

// filter data based o category type 
if (!empty($_GET['category'])) {
    $sql .= " AND s.category = ?";
    $params[] = $_GET['category'];
}

$sql .= " ORDER BY s.featured_status DESC, s.created_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Services – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
         
            <div class="filter-bar filter-box">
                <form action="browse_services.php" method="GET" class="flex-gap-15">
                    <input type="text" name="search" placeholder="Search keywords..." class="form-input" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex: 2;">
                    
                    <select name="category" class="form-select flex-1">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category_name']) ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if (!empty($_GET['search']) || !empty($_GET['category'])): ?>
                        <a href="browse_services.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <h2 class="heading-secondary">Available Services</h2>
            
            <?php if (count($services) > 0): ?>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card" onclick="window.location.href='service_detail.php?id=<?= $service['service_id'] ?>'" style="cursor: pointer; position: relative;">
                            <?php if ($service['featured_status'] === 'Yes'): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                            
                            <img src="<?= htmlspecialchars($service['image_1']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-image">
                            
                            <div class="service-content">
                                <div class="service-meta">
                                    <img src="<?= !empty($service['profile_photo']) ? htmlspecialchars($service['profile_photo']) : 'images/default_user.png' ?>" alt="User" style="width: 24px; height: 24px; border-radius: 50%;">
                                    <span><?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?></span>
                                </div>
                                
                                <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
                                <p class="service-meta"><?= htmlspecialchars($service['category']) ?></p>
                                
                                <div class="card-footer-flex">
                                    <span class="service-price">Starting at $<?= number_format($service['price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="message message-info">No services found matching your criteria.</div>
            <?php endif; ?>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
