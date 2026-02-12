<?php
require_once 'includes/config.php';

if (!isFreelancer()) {
    redirect('login.php');
}

$errors = [];
$step = $_GET['step'] ?? 1;

$_SESSION['create_service'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //step 1 : load the service data
    if ($step == 1) {
        $data = [
            'title' => trim($_POST['title']),
            'category' => $_POST['category'],
            'subcategory' => $_POST['subcategory'],
            'description' => trim($_POST['description']),
            'price' => $_POST['price'],
            'delivery_time' => $_POST['delivery_time'],
            'revisions' => $_POST['revisions']
        ];

        if (strlen($data['title']) < 10 || strlen($data['title']) > 100) $errors[] = "Title must be 10–100 characters.";
        if (strlen($data['description']) < 100 || strlen($data['description']) > 2000) $errors[] = "Description must be 100–2000 characters.";
        if ($data['price'] < 5 || $data['price'] > 10000) $errors[] = "Price must be $5–$10,000.";
        if ($data['delivery_time'] < 1 || $data['delivery_time'] > 90) $errors[] = "Delivery time must be 1–90 days.";
        if ($data['revisions'] < 0 || $data['revisions'] > 999) $errors[] = "Revisions must be 0–999.";

        if (!$errors) {
            $_SESSION['create_service'] = $data;
            redirect('create_service.php?step=2');
        }
    }

    //step 2 : upload the images 
    if ($step == 2) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $uploadDir = 'images/';
        $service_id = mt_rand(1000000000, 9999999999);
        $_SESSION['create_service']['service_id'] = $service_id;

        for ($i = 1; $i <= 3; $i++) {
            $field = "image_$i";

            if ($i == 1 && empty($_FILES[$field]['name'])) {
                $errors[] = "At least one image is required.";
                continue;
            }

            if (!empty($_FILES[$field]['name'])) {
                $mime = mime_content_type($_FILES[$field]['tmp_name']);
                if (!in_array($mime, $allowedTypes)) {
                    $errors[] = "Only JPG or PNG allowed (Image $i).";
                    continue;
                }

                [$w, $h] = getimagesize($_FILES[$field]['tmp_name']);
                if ($w > 800 || $h > 600) {
                    $errors[] = "Image $i must be max 800x600.";
                    continue;
                }

                $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $path = $uploadDir . $service_id . "_$i.$ext";
                move_uploaded_file($_FILES[$field]['tmp_name'], $path);
                $_SESSION['create_service'][$field] = $path;
            }
        }

        if (!$errors) {
            redirect('create_service.php?step=3');
        }
    }

    //step 3 : confirm right enterd  data 
    if ($step == 3 && isset($_POST['confirm'])) {
        $d = $_SESSION['create_service'];

        $sql = "INSERT INTO services 
        (service_id, freelancer_id, title, category, subcategory, description, price, delivery_time, revisions_included,
         image_1, image_2, image_3, status, featured_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'No')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $d['service_id'],
            $_SESSION['user_id'],
            $d['title'],
            $d['category'],
            $d['subcategory'],
            $d['description'],
            $d['price'],
            $d['delivery_time'],
            $d['revisions'],
            $d['image_1'] ?? null,
            $d['image_2'] ?? null,
            $d['image_3'] ?? null
        ]);

        unset($_SESSION['create_service']);
        redirect('my_services.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Service</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<main class="main-content">

<?php if ($errors): ?>
<div class="message message-error">
<?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
</div>
<?php endif; ?>

<!-- info for step 1 : service data  -->
<?php if ($step == 1): ?>
<form method="POST">
<h2>Step 1 – Service Info</h2>
<input name="title" placeholder="Title" required>
<textarea name="description" required></textarea>
<input name="price" type="number" min="5" max="10000" required>
<input name="delivery_time" type="number" min="1" max="90" required>
<input name="revisions" type="number" min="0" max="999" required>
<button class="btn btn-primary">Next</button>
</form>
<?php endif; ?>

<!-- info for STEP 2: upload the images  -->
<?php if ($step == 2): ?>
<form method="POST" enctype="multipart/form-data">
<h2>Step 2 – Upload Images</h2>
<input type="file" name="image_1" required>
<input type="file" name="image_2">
<input type="file" name="image_3">
<button class="btn btn-primary">Next</button>
</form>
<?php endif; ?>

<!-- info for STEP 3 : confrimation -->
<?php if ($step == 3): ?>
<h2>Step 3 – Review & Confirm</h2>
<p><strong><?= $_SESSION['create_service']['title'] ?></strong></p>
<p><?= $_SESSION['create_service']['description'] ?></p>

<form method="POST">
<button name="confirm" class="btn btn-success">Publish Service</button>
</form>
<?php endif; ?>

</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>