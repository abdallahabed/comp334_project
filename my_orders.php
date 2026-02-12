<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// select Orders for the client
if ($role === 'Client') {
    $sql = "SELECT o.*, s.title, u.first_name, u.last_name 
            FROM orders o 
            JOIN services s ON o.service_id = s.service_id 
            JOIN users u ON o.freelancer_id = u.user_id 
            WHERE o.client_id = ? 
            ORDER BY o.order_date DESC";
} else {
    $sql = "SELECT o.*, s.title, u.first_name, u.last_name 
            FROM orders o 
            JOIN services s ON o.service_id = s.service_id 
            JOIN users u ON o.client_id = u.user_id 
            WHERE o.freelancer_id = ? 
            ORDER BY o.order_date DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <h2 class="heading-secondary">My Orders</h2>

            <?php if (count($orders) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Service</th>
                            <th><?= $role === 'Client' ? 'Freelancer' : 'Client' ?></th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                <td>$<?= number_format($order['price'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <a href="order_details.php?id=<?= $order['order_id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="message message-info">No orders found.</div>
            <?php endif; ?>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
