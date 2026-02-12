<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    redirect('browse_services.php');
}

$service_id = $_GET['id'];

// select Service Details
$stmt = $pdo->prepare("SELECT s.*, u.first_name, u.last_name, u.profile_photo, u.registration_date 
                       FROM services s 
                       JOIN users u ON s.freelancer_id = u.user_id 
                       WHERE s.service_id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found.");
}


$cookie_name = "recently_viewed";
$viewed = isset($_COOKIE[$cookie_name]) ? explode(',', $_COOKIE[$cookie_name]) : [];
if (($key = array_search($service_id, $viewed)) !== false) {
    unset($viewed[$key]);
}
$viewed[] = $service_id;
if (count($viewed) > 4) {
    array_shift($viewed);
}
setcookie($cookie_name, implode(',', $viewed), time() + (86400 * 30), "/");

// Add items to the to Cart
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    if (isClient()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $exists = false;
        foreach ($_SESSION['cart'] as $item) {
            if ($item['service_id'] === $service_id) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $_SESSION['cart'][] = [
                'service_id' => $service['service_id'],
                'title' => $service['title'],
                'price' => $service['price'],
                'freelancer_id' => $service['freelancer_id'],
                'delivery_time' => $service['delivery_time'],
                'image' => $service['image_1']
            ];
            $message = "Service added to cart!";
        } else {
            $message = "Service already in cart.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($service['title']) ?> – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
    <script>
        function changeImage(src, el) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }
    </script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="two-column-layout">
                <div class="col-left-65">
                    
                    <div class="breadcrumb">
                        <?= htmlspecialchars($service['category']) ?> > <?= htmlspecialchars($service['subcategory']) ?>
                    </div>

                    <h1 class="heading-primary mb-20"><?= htmlspecialchars($service['title']) ?></h1>

                    <div class="gallery">
                        <img id="mainImage" src="<?= htmlspecialchars($service['image_1']) ?>" alt="Service Image" class="main-image">
                        <div class="gallery-thumbs">
                            <img src="<?= htmlspecialchars($service['image_1']) ?>" class="thumb active" onclick="changeImage(this.src, this)">
                            <?php if (!empty($service['image_2'])): ?>
                                <img src="<?= htmlspecialchars($service['image_2']) ?>" class="thumb" onclick="changeImage(this.src, this)">
                            <?php endif; ?>
                            <?php if (!empty($service['image_3'])): ?>
                                <img src="<?= htmlspecialchars($service['image_3']) ?>" class="thumb" onclick="changeImage(this.src, this)">
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="freelancer-info-card">
                        <img src="<?= !empty($service['profile_photo']) ? htmlspecialchars($service['profile_photo']) : 'images/default_user.png' ?>" class="freelancer-avatar">
                        <div>
                            <div class="freelancer-name"><?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?></div>
                            <div class="freelancer-date">Member since <?= date('M Y', strtotime($service['registration_date'])) ?></div>
                        </div>
                    </div>

                    <h3 class="heading-tertiary">About This Service</h3>
                    <div class="service-description">
                        <?= htmlspecialchars($service['description']) ?>
                    </div>

                </div>

                <div class="col-right-35">
                    <div class="booking-card">
                        
                        <?php if ($message): ?>
                            <div class="message message-success"><?= $message ?></div>
                        <?php endif; ?>

                        <div class="price-section">
                            <span class="price-label">Starting at</span>
                            <div class="price-value">$<?= number_format($service['price'], 2) ?></div>
                        </div>

                        <div class="meta-section">
                            <div class="meta-item">⏱ <strong><?= $service['delivery_time'] ?> Days</strong> Delivery</div>
                            <div>🔄 <strong><?= $service['revisions_included'] == 999 ? 'Unlimited' : $service['revisions_included'] ?></strong> Revisions</div>
                        </div>

                        <?php if (isClient()): ?>
                            <form method="POST">
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-block mb-10">Add to Cart</button>
                            </form>
                            <a href="checkout.php" class="btn btn-success btn-block">Order Now</a>
                        <?php elseif (isFreelancer() && $_SESSION['user_id'] === $service['freelancer_id']): ?>
                            <a href="edit_service.php?id=<?= $service['service_id'] ?>" class="btn btn-secondary btn-block">Edit Service</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-block">Login to Order</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
