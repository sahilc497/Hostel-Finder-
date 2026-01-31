<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $role = $_POST['role']; // student or owner

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, gender, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $phone, $gender, $role])) {
                $success = "Registration successful! <a href='login.php' class='fw-bold text-black'>Login here</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Us - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="brutal-card">
                    <h1 class="text-center mb-4">JOIN THE SQUAD</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-brutal rounded-0"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success border-brutal rounded-0"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">FULL NAME</label>
                                <input type="text" name="name" class="form-control" placeholder="YOUR NAME" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">EMAIL ADDRESS</label>
                                <input type="email" name="email" class="form-control" placeholder="YOUR EMAIL" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">PHONE NUMBER</label>
                                <input type="text" name="phone" class="form-control" placeholder="PHONE" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">GENDER</label>
                                <select name="gender" class="form-control" required>
                                    <option value="">SELECT</option>
                                    <option value="Male">MALE</option>
                                    <option value="Female">FEMALE</option>
                                    <option value="Other">OTHER</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">IDENTIFY AS</label>
                            <select name="role" class="form-control" required>
                                <option value="student">STUDENT / TENANT</option>
                                <option value="owner">PG OWNER / LANDLORD</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">PASSWORD</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">CONFIRM PASSWORD</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brutal w-100">JOIN NOW</button>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-1">ALREADY ENLISTED? <a href="login.php" class="text-black fw-bold">LOGIN HERE</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
