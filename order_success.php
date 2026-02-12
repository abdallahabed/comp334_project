<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="success-container">
                <div class="success-icon-large">✓</div>
                <h1 class="text-success-custom">Orders Placed Successfully!</h1>
                <p class="text-muted-lg">Thank you for your purchase.</p>
                
                <div class="mt-40">
                    <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
                    <a href="browse_services.php" class="btn btn-secondary">Browse More</a>
                </div>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
