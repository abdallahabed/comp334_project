<?php
require_once 'includes/config.php';


if (!isClient() || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$_SESSION['cart'] = array_map(function($item) {
    return is_object($item) ? (array)$item : $item;
}, $_SESSION['cart']);


$_SESSION['checkout_req'] ??= [];
$_SESSION['checkout_pay'] ??= [];

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;


$errors = [];

// Luhn check function for card validation
function luhnCheck($number) {
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = (int)$number[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }
    return $sum % 10 === 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1: Save requirements
    if ($step === 1) {
        foreach ($_SESSION['cart'] as $item) {
            $service_id = $item['service_id'] ?? null;
            if (!$service_id) continue;

            $_SESSION['checkout_req'][$service_id] = [
                'requirements' => $_POST['req_' . $service_id] ?? 'No requirements',
            ];

            $fileKey = 'req_files_' . $service_id;
            if (!empty($_FILES[$fileKey]['name'][0])) {
                $files = [];
                foreach ($_FILES[$fileKey]['tmp_name'] as $index => $tmpName) {
                    $name = basename($_FILES[$fileKey]['name'][$index]);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $targetDir = 'uploads/requirements/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $targetFile = $targetDir . uniqid() . "_$service_id." . $ext;
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $files[] = $targetFile;
                    }
                }
                $_SESSION['checkout_req'][$service_id]['files'] = $files;
            }
        }

        redirect('checkout.php?step=2');
    }

    // STEP 2: Save payment info with validation
    if ($step === 2) {

        $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        // Card number: exactly 16 digits
        if (!preg_match('/^\d{16}$/', $card_number)) {
            $errors[] = 'Card number must be exactly 16 digits.';
        } elseif (!luhnCheck($card_number)) {
            $errors[] = 'Invalid card number.';
        }

        // MM/YY and not expired
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
            $errors[] = 'Expiry must be in MM/YY format.';
        } else {
            [$mm, $yy] = explode('/', $expiry);
            $expiryDate = strtotime("20$yy-$mm-01 +1 month");
            if ($expiryDate < time()) {
                $errors[] = 'Card is expired.';
            }
        }

        // CVV: 3 digits
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = 'CVV must be 3 or 4 digits.';
        }

        // If no errors → save and continue
        if (empty($errors)) {
            $_SESSION['checkout_pay'] = [
                'card_number' => $card_number,
                'expiry' => $expiry,
                'cvv' => $cvv
            ];
            redirect('checkout.php?step=3');
        }
    }

    // STEP 3: Place orders
    if ($step === 3) {
        $cart = $_SESSION['cart'];
        $reqs = $_SESSION['checkout_req'];

        foreach ($cart as $item) {
            $service_id = $item['service_id'] ?? null;
            if (!$service_id) continue;

            $order_id = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $req_text = $reqs[$service_id]['requirements'] ?? 'No requirements';

            $sql = "INSERT INTO orders 
                (order_id, client_id, freelancer_id, service_id, service_title, price, delivery_time, revisions_included, requirements, status, payment_method, expected_delivery)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Credit Card', DATE_ADD(NOW(), INTERVAL ? DAY))";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $order_id,
                $_SESSION['user_id'],
                $item['freelancer_id'] ?? null,
                $item['service_id'],
                $item['title'] ?? '',
                $item['price'] ?? 0,
                $item['delivery_time'] ?? 1,
                999,
                $req_text,
                $item['delivery_time'] ?? 1
            ]);

            if (!empty($reqs[$service_id]['files'])) {
                foreach ($reqs[$service_id]['files'] as $filePath) {
                    $stmtFile = $pdo->prepare("INSERT INTO order_files (order_id, file_path) VALUES (?, ?)");
                    $stmtFile->execute([$order_id, $filePath]);
                }
            }
        }

        // Clear the cart 
        unset($_SESSION['cart'], $_SESSION['checkout_req'], $_SESSION['checkout_pay']);
        redirect('order_success.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout – Step <?= $step ?></title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="wrapper">
    <?php include 'includes/nav.php'; ?>

    <main class="main-content">
        <div class="form-container mw-800">

            <div class="step-indicator">
                <div class="step-line"></div>
                <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
                    <div class="step-circle">1</div>
                    <div>Requirements</div>
                </div>
                <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
                    <div class="step-circle">2</div>
                    <div>Payment</div>
                </div>
                <div class="step <?= $step >= 3 ? 'active' : '' ?>">
                    <div class="step-circle">3</div>
                    <div>Confirm</div>
                </div>
            </div>

            <form action="checkout.php?step=<?= $step ?>" method="POST" enctype="multipart/form-data">

                <?php if ($step === 1): ?>
                    <h2 class="heading-secondary">Service Requirements</h2>

                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="card card-box">
                            <h3><?= htmlspecialchars($item['title'] ?? '') ?></h3>
                            <div class="form-group">
                                <label class="form-label">Describe your requirements</label>
                                <textarea name="req_<?= $item['service_id'] ?>" class="form-textarea" placeholder="Write your requirements here..." required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Attach Files (Optional, max 3)</label>
                                <input type="file" name="req_files_<?= $item['service_id'] ?>[]" class="form-input" multiple accept=".pdf,.doc,.docx,.txt,.zip,.jpg,.png">
                                <small class="text-muted">Max 10MB each.</small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary">Continue to Payment</button>

                <?php elseif ($step === 2): ?>
                    <h2 class="heading-secondary">Payment Information</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="message message-error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Card Number</label>
                        <input type="text" name="card_number" class="form-input" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry</label>
                        <input type="text" name="expiry" class="form-input" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="4" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Continue to Review</button>

                <?php else: ?>
                    <h2 class="heading-secondary">Review Order</h2>
                    <div class="message message-info">
                        You are about to place <?= count($_SESSION['cart']) ?> order(s).
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Place Order</button>
                <?php endif; ?>

            </form>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>