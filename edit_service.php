<?php
require_once 'includes/config.php';

if (!isFreelancer()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('my_services.php');
}

$service_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// select the needed service to make edits to it .
$stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ? AND freelancer_id = ?");
$stmt->execute([$service_id, $user_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found or access denied.");
}

$error = '';
$success = '';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $delivery_time = $_POST['delivery_time'];
    $revisions = $_POST['revisions_included'];
    $status = $_POST['status'];

    // Handle Image Update
    $image_path = $service['image_1'];
    if (!empty($_FILES['image_1']['name'])) {
        $uploadDir = 'images/';
        $path = $uploadDir . basename($_FILES['image_1']['name']);
        move_uploaded_file($_FILES['image_1']['tmp_name'], $path);
        $image_path = $path;
    }

    $sql = "UPDATE services SET title=?, category=?, subcategory=?, description=?, price=?, delivery_time=?, revisions_included=?, image_1=?, status=? WHERE service_id=?";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$title, $category, $subcategory, $description, $price, $delivery_time, $revisions, $image_path, $status, $service_id]);
        $success = "Service updated successfully!";
        $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h2 class="heading-secondary">Edit Service</h2>
                
                <?php if ($error): ?>
                    <div class="message message-error"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="message message-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label class="form-label">Service Title</label>
                        <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($service['title']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Web Development" <?= $service['category'] == 'Web Development' ? 'selected' : '' ?>>Web Development</option>
                            <option value="Graphic Design" <?= $service['category'] == 'Graphic Design' ? 'selected' : '' ?>>Graphic Design</option>
                            <option value="Writing & Translation" <?= $service['category'] == 'Writing & Translation' ? 'selected' : '' ?>>Writing & Translation</option>
                            <option value="Digital Marketing" <?= $service['category'] == 'Digital Marketing' ? 'selected' : '' ?>>Digital Marketing</option>
                            <option value="Video & Animation" <?= $service['category'] == 'Video & Animation' ? 'selected' : '' ?>>Video & Animation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subcategory</label>
                        <input type="text" name="subcategory" class="form-input" value="<?= htmlspecialchars($service['subcategory']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" required><?= htmlspecialchars($service['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price ($)</label>
                        <input type="number" name="price" class="form-input" value="<?= htmlspecialchars($service['price']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Delivery Time (Days)</label>
                        <input type="number" name="delivery_time" class="form-input" value="<?= htmlspecialchars($service['delivery_time']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Revisions</label>
                        <input type="number" name="revisions_included" class="form-input" value="<?= htmlspecialchars($service['revisions_included']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Current Image</label>
                        <img src="<?= htmlspecialchars($service['image_1']) ?>" style="width: 150px; border-radius: 4px; border: 1px solid #DEE2E6;">
                        <div class="mt-10">
                            <label>Change Image:</label>
                            <input type="file" name="image_1" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div>
                            <label><input type="radio" name="status" value="Active" <?= $service['status'] == 'Active' ? 'checked' : '' ?>> Active</label>
                            <label class="ml-20"><input type="radio" name="status" value="Inactive" <?= $service['status'] == 'Inactive' ? 'checked' : '' ?>> Inactive (Draft)</label>
                        </div>
                    </div>

                    <div class="form-actions flex-between">
                        <a href="my_services.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Service</button>
                    </div>

                </form>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
