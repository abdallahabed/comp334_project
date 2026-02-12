<?php
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Check if account is locked
            $lockTime = 15 * 60;
            $failedAttempts = $user['failed_attempts'];
            $lastAttempt = strtotime($user['last_attempt'] ?? '0');
            $currentTime = time();

            if ($failedAttempts >= 5 && ($currentTime - $lastAttempt) < $lockTime) {
                $error = "Account locked due to multiple failed attempts. Try again after " . ceil(($lockTime - ($currentTime - $lastAttempt)) / 60) . " minutes.";
            } elseif (password_verify($password, $user['password'])) {
                if ($user['status'] === 'Inactive') {
                    $error = "Your account is inactive.";
                } else {
                    // Reset failed attempts on successful login
                    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_attempt = NULL WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);

                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile_photo'] = $user['profile_photo'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'Client') {
                        redirect('browse_services.php');
                    } else {
                        redirect('my_services.php');
                    }
                }
            } else {
                //increment failed attempts for wrong password 
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_attempt = NOW() WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        <div class="form-container mw-400">
            <h2 class="heading-secondary">Login</h2>
            
            <?php if ($error): ?>
                <div class="message message-error"><?= $error ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p class="text-center-mt-15">
                Don't have an account? <a href="register.php">Sign Up</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
