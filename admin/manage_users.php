<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$role = $_GET['role'] ?? '';
$sql = "SELECT u.*, 
       (SELECT COUNT(*) FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE p.owner_id = u.id AND b.status = 'confirmed') as tenant_count 
FROM users u 
WHERE u.role != 'admin'";

if ($role) {
    $sql .= " AND u.role = '$role'";
}
$users = $conn->query($sql . " ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_bookings.php">All Bookings</a></li>
                </ul>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <div class="btn-group">
                <a href="manage_users.php" class="btn btn-outline-primary <?php echo !$role ? 'active' : ''; ?>">All</a>
                <a href="manage_users.php?role=owner" class="btn btn-outline-primary <?php echo $role == 'owner' ? 'active' : ''; ?>">Owners</a>
                <a href="manage_users.php?role=student" class="btn btn-outline-primary <?php echo $role == 'student' ? 'active' : ''; ?>">Students</a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Oversight</th>
                            <th>Phone</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span class="badge bg-<?php echo $u['role'] == 'owner' ? 'info' : 'secondary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td>
                                <?php if ($u['role'] == 'owner'): ?>
                                    <span class="badge bg-dark"><?php echo $u['tenant_count']; ?> Tenants</span>
                                <?php else: ?>
                                    <span class="text-muted small">Student</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
