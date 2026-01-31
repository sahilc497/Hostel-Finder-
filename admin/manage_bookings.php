<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$sql = "SELECT b.*, u.name as student_name, u.email as student_email, pg.name as pg_name, o.name as owner_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN pg_listings pg ON b.pg_id = pg.id 
        JOIN users o ON pg.owner_id = o.id 
        ORDER BY b.created_at DESC";
$bookings = $conn->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Bookings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage_bookings.php">All Bookings</a></li>
                </ul>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Master Booking List</h2>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Hostel / PG</th>
                            <th>Bed #</th>
                            <th>Method</th>
                            <th>Status / Notice</th>
                            <th>Booking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($b['student_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($b['student_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($b['pg_name']); ?></td>
                            <td><span class="badge bg-secondary">Bed <?php echo $b['bed_number']; ?></span></td>
                            <td><small><?php echo $b['payment_method']; ?></small></td>
                            <td>
                                <span class="badge bg-success"><?php echo ucfirst($b['status']); ?></span>
                                <?php if ($b['leave_notice_date']): ?>
                                    <br><span class="badge bg-warning text-dark mt-1">Leaving: <?php echo date('d M', strtotime("+2 months", strtotime($b['leave_notice_date']))); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
