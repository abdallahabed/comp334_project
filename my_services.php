<?php
require_once 'includes/config.php';

if (!isFreelancer()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// change Status for the service
if (isset($_POST['action']) && isset($_POST['service_id'])) {
    $service_id = $_POST['service_id'];
    $action = $_POST['action'];
    
    // Verify ownership
    $check = $pdo->prepare("SELECT freelancer_id FROM services WHERE service_id = ?");
    $check->execute([$service_id]);
    $s = $check->fetch();
    
    if ($s && $s['freelancer_id'] === $user_id) {
        if ($action === 'deactivate') {
            $pdo->prepare("UPDATE services SET status = 'Inactive', featured_status = 'No' WHERE service_id = ?")->execute([$service_id]);
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE services SET status = 'Active' WHERE service_id = ?")->execute([$service_id]);
        } elseif ($action === 'delete') {
             $pdo->prepare("DELETE FROM services WHERE service_id = ?")->execute([$service_id]);
        }
    }
    redirect('my_services.php');
}

// select Services
$stmt = $pdo->prepare("SELECT * FROM services WHERE freelancer_id = ? ORDER BY created_date DESC");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll();

// Statistics
$total = count($services);
$active = 0;
$featured = 0;
foreach ($services as $s) {
    if ($s['status'] === 'Active') $active++;
    if ($s['featured_status'] === 'Yes') $featured++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Services – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="header-flex-mb-20">
                <h2 class="heading-secondary">My Services</h2>
                <a href="create_service.php" class="btn btn-primary">Create New Service</a>
            </div>

            <div class="stats-container grid-4-cols">
                <div class="stat-card stat-card-box">
                    <div class="stat-value text-24-bold"><?= $total ?></div>
                    <div class="stat-label text-muted-custom">Total Services</div>
                </div>
                <div class="stat-card stat-card-box">
                    <div class="stat-value text-24-bold-success"><?= $active ?></div>
                    <div class="stat-label text-muted-custom">Active</div>
                </div>
                <div class="stat-card stat-card-box">
                    <div class="stat-value text-24-bold-warning"><?= $featured ?>/3</div>
                    <div class="stat-label text-muted-custom">Featured</div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($service['image_1']) ?>" width="60" height="45" style="object-fit: cover; border-radius: 4px;"></td>
                            <td>
                                <a href="service_detail.php?id=<?= $service['service_id'] ?>" style="font-weight: 600;"><?= htmlspecialchars($service['title']) ?></a>
                            </td>
                            <td><?= htmlspecialchars($service['category']) ?></td>
                            <td>$<?= number_format($service['price'], 2) ?></td>
                            <td>
                                <span class="badge <?= $service['status'] === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $service['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($service['featured_status'] === 'Yes'): ?>
                                    <span class="text-warning-custom">★ Yes</span>
                                <?php else: ?>
                                    No
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_service.php?id=<?= $service['service_id'] ?>" class="btn btn-secondary btn-sm" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                
                                <form action="my_services.php" method="POST" class="d-inline">
                                    <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                    <?php if ($service['status'] === 'Active'): ?>
                                        <button type="submit" name="action" value="deactivate" class="btn btn-danger btn-sm badge-sm">Deactivate</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm badge-sm">Activate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
