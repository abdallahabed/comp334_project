<?php require_once 'includes/config.php'; ?>

<header class="header">
    <div class="header-container">
        <div class="logo-section">
            <a href="index.php" class="nav-logo-link">
                <img src="images/logo.png" alt="Marketplace Logo" width="50" height="50">
                <h1>Freelance Marketplace</h1>
            </a>
        </div>

        <form class="search-form" action="browse_services.php" method="GET">
            <input type="text" name="search"
                   placeholder="Search services..."
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Search</button>
        </form>

        <div class="auth-controls">
            <?php if (isLoggedIn()): ?>

                <?php
                    $userType = isClient() ? 'client' : 'freelancer';
                    $profilePhoto = !empty($_SESSION['profile_photo'])
                        ? htmlspecialchars($_SESSION['profile_photo'])
                        : 'images/default_user.png';
                    $firstName = htmlspecialchars($_SESSION['first_name'] ?? 'User');
                ?>

                <a href="profile.php" class="profile-card-header <?= $userType ?>">
                    <img src="<?= $profilePhoto ?>" alt="Profile">
                    <span><?= $firstName ?></span>
                </a>

                <?php if (isClient()): ?>
                    <?php
                        $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    ?>
                    <a href="cart.php" class="cart-icon">
                        🛒
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>

            <?php else: ?>

                <a href="login.php" class="btn btn-primary">Login</a>
                <a href="register.php" class="btn btn-secondary">Sign Up</a>

            <?php endif; ?>
        </div>
    </div> </header>