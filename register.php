<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $role = $_POST['role'];

    //  Validation for fileds 
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
        $error = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // step 1: Password Complexity
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', $password)) {
        $error = "Password must contain at least 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
    } 
    // step 2: Phone Number
    elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } 
    else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            // Generate User ID (10 digits)
            $user_id = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert User
            $sql = "INSERT INTO users (user_id, first_name, last_name, email, password, phone, country, city, role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([
                    $user_id,
                    $first_name,
                    $last_name,
                    $email,
                    $hashed_password,
                    $phone,
                    $country,
                    $city,
                    $role
                ]);
                $success = "Account created successfully! Please <a href='login.php'>Login</a>.";
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – Freelance Marketplace</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wrapper">
        <?php include 'includes/nav.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h2 class="heading-secondary">Create an Account</h2>
                
                <?php if ($error): ?>
                    <div class="message message-error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message message-success"><?= $success ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-input" required>
                        <small>Must contain 1 uppercase, 1 lowercase, 1 number, and 1 special character</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone <span class="required">*</span></label>
                        <input type="text" name="phone" class="form-input" required>
                        <small>Exactly 10 digits</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country <span class="required">*</span></label>
                        <input type="text" name="country" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">City <span class="required">*</span></label>
                        <input type="text" name="city" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">I am a: <span class="required">*</span></label>
                        <div>
                            <label><input type="radio" name="role" value="Client" checked> Client</label>
                            <label class="ml-20"><input type="radio" name="role" value="Freelancer"> Freelancer</label>
                        </div>
                    </div>

                    <div class="form-actions flex-between-center">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>

                <p class="text-center-mt-15">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>