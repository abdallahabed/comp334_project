<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('my_orders.php');
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// select Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

// check for authorzirion to edit the data 
if ($order['client_id'] !== $user_id && $order['freelancer_id'] !== $user_id) {
    die("Access Denied.");
}

// get the  Files
$stmtFiles = $pdo->prepare("SELECT * FROM file_attachments WHERE order_id = ?");
$stmtFiles->execute([$order_id]);
$files = $stmtFiles->fetchAll();

// select  Revisions details 
$stmtRevisions = $pdo->prepare("SELECT * FROM revision_requests WHERE order_id = ? ORDER BY request_date DESC");
$stmtRevisions->execute([$order_id]);
$revisions = $stmtRevisions->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'cancel' && isClient() && $order['status'] === 'Pending') {
        $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ?")->execute([$order_id]);
    } elseif ($action === 'deliver' && isFreelancer() && $order['status'] === 'In Progress') {
        $pdo->prepare("UPDATE orders SET status = 'Delivered', completion_date = NOW() WHERE order_id = ?")->execute([$order_id]);
    } elseif ($action === 'complete' && isClient() && $order['status'] === 'Delivered') {
        $pdo->prepare("UPDATE orders SET status = 'Completed' WHERE order_id = ?")->execute([$order_id]);
    } elseif ($action === 'start' && isFreelancer() && $order['status'] === 'Pending') {
        $pdo->prepare("UPDATE orders SET status = 'In Progress' WHERE order_id = ?")->execute([$order_id]);
    }
    
    redirect("order_details.php?id=$order_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= $order_id ?></title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .file-item { display: flex; align-items: center; padding: 15px; border: 1px solid #DEE2E6; border-radius: 6px; margin-bottom: 10px; background: #fff; }
        .file-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: bold; margin-right: 15px; border: 2px solid; }
        .file-icon.pdf { color: #DC3545; border-color: #DC3545; background: #F8D7DA; }
        .file-icon.doc { color: #007BFF; border-color: #007BFF; background: #D1ECF1; }
        .file-icon.img { color: #28A745; border-color: #28A745; background: #D4EDDA; }
        .file-icon.zip { color: #6F42C1; border-color: #6F42C1; background: #E2D9F3; }
        .file-info { flex-grow: 1; }
        .file-name { font-weight: 600; color: #212529; display: block; }
        .file-meta { font-size: 13px; color: #ADB5BD; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="header-flex-mb-20">
                <h1 class="heading-primary">Order #<?= $order_id ?></h1>
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>" style="font-size: 16px; padding: 10px 20px;">
                    <?= $order['status'] ?>
                </span>
            </div>

            <div class="two-column-layout">
                <div class="col-left-65">
                    <div class="card mb-20">
                        <h3>Service Details</h3>
                        <p><strong>Service:</strong> <?= htmlspecialchars($order['service_title']) ?></p>
                        <p><strong>Price:</strong> $<?= number_format($order['price'], 2) ?></p>
                        <p><strong>Order Date:</strong> <?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                        <p><strong>Expected Delivery:</strong> <?= date('M d, Y', strtotime($order['expected_delivery'])) ?></p>
                    </div>

                    <div class="card mb-20">
                        <h3>Requirements</h3>
                        <p><?= nl2br(htmlspecialchars($order['requirements'])) ?></p>
                        
                        <?php if (!empty($files)): ?>
                            <h4 class="mt-20">Attached Files</h4>
                            <?php foreach ($files as $file): 
                                $ext = pathinfo($file['original_filename'], PATHINFO_EXTENSION);
                                $typeClass = 'zip';
                                if (in_array($ext, ['pdf'])) $typeClass = 'pdf';
                                elseif (in_array($ext, ['doc', 'docx'])) $typeClass = 'doc';
                                elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) $typeClass = 'img';
                            ?>
                                <div class="file-item">
                                    <div class="file-icon <?= $typeClass ?>"><?= strtoupper($ext) ?></div>
                                    <div class="file-info">
                                        <a href="<?= $file['file_path'] ?>" class="file-name" download><?= htmlspecialchars($file['original_filename']) ?></a>
                                        <div class="file-meta"><?= round($file['file_size'] / 1024, 1) ?> KB • <?= date('M d, Y', strtotime($file['upload_timestamp'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="card mb-20">
                        <h3>Revision History</h3>
                        <?php if (empty($revisions)): ?>
                            <p class="text-muted">No revisions requested.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Notes</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($revisions as $rev): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($rev['request_date'])) ?></td>
                                            <td><?= htmlspecialchars($rev['revision_notes']) ?></td>
                                            <td><span class="badge badge-<?= strtolower($rev['request_status']) ?>"><?= $rev['request_status'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-right-35">
                    <div class="card">
                        <h3>Actions</h3>
                        <form method="POST">
                            <?php if (isClient() && $order['status'] === 'Pending'): ?>
                                <button type="submit" name="action" value="cancel" class="btn btn-danger btn-block">Cancel Order</button>
                            <?php elseif (isFreelancer() && $order['status'] === 'Pending'): ?>
                                <button type="submit" name="action" value="start" class="btn btn-primary btn-block">Start Working</button>
                            <?php elseif (isFreelancer() && $order['status'] === 'In Progress'): ?>
                                <button type="submit" name="action" value="deliver" class="btn btn-success btn-block">Upload Delivery</button>
                            <?php elseif (isClient() && $order['status'] === 'Delivered'): ?>
                                <button type="submit" name="action" value="complete" class="btn btn-success btn-block">Mark as Completed</button>
                            <?php else: ?>
                                <p class="text-muted">No actions available.</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
