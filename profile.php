<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// get User Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// update the user data 
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    
    $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, city = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$first_name, $last_name, $phone, $city, $user_id]);
    
    $message = "Profile updated successfully!";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $_SESSION['first_name'] = $user['first_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="profile-layout">
                

                <div class="profile-left">
                    <div class="card text-center">
                        <img src="<?= !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'images/default_user.png' ?>" class="profile-photo-large">
                        <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge badge-<?= strtolower($user['role']) === 'client' ? 'in-progress' : 'active' ?>">
                            <?= $user['role'] ?>
                        </span>
                        <p class="text-muted-mt-10">Member since <?= date('M Y', strtotime($user['registration_date'])) ?></p>
                    </div>
                </div>

            
                <div class="profile-right">
                    <div class="card">
                        <h2 class="heading-secondary">Edit Profile</h2>
                        
                        <?php if ($message): ?>
                            <div class="message message-success"><?= $message ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($user['city']) ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
