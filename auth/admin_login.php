<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "MISSING CREDENTIALS. ABORT.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = "INVALID ACCESS CODE. DENIED.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTRICTED ACCESS - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <style>
        body { background: var(--brutal-yellow); height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .admin-login-card { max-width: 450px; width: 100%; }
        .restricted-header { background: #111; color: var(--brutal-yellow); padding: 10px; font-family: 'Anton'; letter-spacing: 2px; text-align: center; margin: -20px -20px 30px -20px; text-transform: uppercase; }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="brutal-card bg-white admin-login-card">
            <div class="restricted-header">RESTRICTED AREA</div>
            
            <div class="text-center mb-5">
                <div class="bg-brutal-black text-white d-inline-block p-4 mb-3 border-brutal" style="transform: rotate(-3deg);">
                    <i class="fa fa-lock fa-3x"></i>
                </div>
                <h1 class="display-6 mt-2">ADMIN CENTRAL</h1>
                <p class="fw-bold text-muted">AUTHORIZATION REQUIRED</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger border-brutal rounded-0 mb-4 fw-bold"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold">ADMIN EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="ADMIN@STUDENTNEST.COM" required>
                </div>
                <div class="mb-5">
                    <label class="form-label fw-bold">SECRET ACCESS KEY</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-brutal w-100 fs-4 py-3">UNLOCK SYSTEM</button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="../index.php" class="text-black fw-bold text-decoration-none border-bottom border-2 border-dark pb-1"><i class="fa fa-arrow-left me-2"></i>ABORT TO PUBLIC SITE</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
