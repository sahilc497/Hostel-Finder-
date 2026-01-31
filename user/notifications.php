<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark as read
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>My Notifications</h2>
        <a href="javascript:history.back()" class="btn btn-secondary mb-3">Back</a>
        
        <div class="list-group">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item <?php echo !$n['is_read'] ? 'list-group-item-info' : ''; ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">Alert</h5>
                            <small><?php echo $n['created_at']; ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($n['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">No notifications.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
