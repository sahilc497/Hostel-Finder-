<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                case 'owner':
                    header("Location: ../owner/dashboard.php");
                    break;
                case 'student':
                    header("Location: ../user/dashboard.php");
                    break;
                default:
                    $error = "Invalid role assigned.";
            }
            exit;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body class="bg-brutal">

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="brutal-card mt-5">
                    <h1 class="text-center mb-4">ACCESS GRANTED?</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-brutal rounded-0 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold fw-bold">EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control" placeholder="ENTER YOUR EMAIL" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">PASSWORD</label>
                            <input type="password" name="password" class="form-control" placeholder="ENTER YOUR PASSWORD" required>
                        </div>
                        <button type="submit" class="btn btn-brutal w-100">AUTHENTICATE</button>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-1">NEW RECRUIT? <a href="register.php" class="text-black fw-bold">REGISTER HERE</a></p>
                        <p><a href="../index.php" class="text-black small">ABORT TO HOME</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
