<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navigation">
    <a href="index.php" class="nav-link <?= ($current == 'index.php') ? 'active' : '' ?>">Home</a>
    <a href="browse_services.php" class="nav-link <?= ($current == 'browse_services.php') ? 'active' : '' ?>">Browse Services</a>

    <?php if (isLoggedIn()): ?>
        
        <?php if (isClient()): ?>
            <a href="cart.php" class="nav-link <?= ($current == 'cart.php') ? 'active' : '' ?>">Shopping Cart</a>
            <a href="my_orders.php" class="nav-link <?= ($current == 'my_orders.php') ? 'active' : '' ?>">My Orders</a>
        <?php endif; ?>

        <?php if (isFreelancer()): ?>
            <a href="my_services.php" class="nav-link <?= ($current == 'my_services.php') ? 'active' : '' ?>">My Services</a>
            <a href="my_orders.php" class="nav-link <?= ($current == 'my_orders.php') ? 'active' : '' ?>">My Orders</a>
            <a href="profile.php" class="nav-link <?= ($current == 'profile.php') ? 'active' : '' ?>">My Profile</a>
        <?php endif; ?>

        <a href="logout.php" class="nav-link">Logout</a>

    <?php else: ?>
        <a href="login.php" class="nav-link <?= ($current == 'login.php') ? 'active' : '' ?>">Login</a>
        <a href="register.php" class="nav-link <?= ($current == 'register.php') ? 'active' : '' ?>">Sign Up</a>
    <?php endif; ?>
</nav>
