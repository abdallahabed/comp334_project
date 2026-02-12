<?php
require_once 'includes/config.php';

if (!isClient()) {
    redirect('login.php');
}

//in case user want to remove item from cart 
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['service_id'] === $remove_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }

    $_SESSION['cart'] = array_values($_SESSION['cart']); 
    redirect('cart.php');
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Calculate the price for all items in the cart 
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'];
}
$service_fee = $subtotal * 0.05;
$total = $subtotal + $service_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <?php if (empty($cart)): ?>
                <div class="empty-cart-container">
                    <div class="empty-cart-icon">🛒</div>
                    <h2 class="mb-20">Your cart is empty</h2>
                    <a href="browse_services.php" class="btn btn-primary">Browse Services</a>
                </div>
            <?php else: ?>
                
                <div class="two-column-layout">

                <div class="col-left-70">
                        <h2 class="heading-secondary">Shopping Cart (<?= count($cart) ?> items)</h2>
                        
                        <table class="table bg-white">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $item): ?>
                                    <tr>
                                        <td class="cart-item-cell">
                                            
                                            <img src="<?= htmlspecialchars($item['image']) ?>" class="cart-item-img">
                                            <div>
                                                <div class="font-600"><?= htmlspecialchars($item['title']) ?></div>
                                                <div class="text-muted-small">Delivery: <?= $item['delivery_time'] ?> days</div>
                                            </div>
                                        </td>
                                        <td class="font-bold">$<?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <a href="cart.php?remove=<?= $item['service_id'] ?>" class="btn btn-danger btn-sm btn-sm-padding">×</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-right-30">
                        <div class="card">
                            <h3 class="heading-tertiary">Order Summary</h3>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Service Fee (5%)</span>
                                <span>$<?= number_format($service_fee, 2) ?></span>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-total">
                                <span>Total</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
