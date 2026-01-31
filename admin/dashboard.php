<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Stats
$stats = [
    'students' => $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'owners' => $conn->query("SELECT COUNT(*) FROM users WHERE role='owner'")->fetchColumn(),
    'pgs' => $conn->query("SELECT COUNT(*) FROM pg_listings")->fetchColumn(),
    'bookings' => $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'earnings' => 0 
];

// Check if payments table exists
$hasPayments = $conn->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'payments'")->fetch();
if ($hasPayments) {
    try {
        $stats['earnings'] = $conn->query("SELECT SUM(amount) FROM payments WHERE status='Success'")->fetchColumn();
    } catch (PDOException $e) {
        $stats['earnings'] = 0;
    }
} else {
    $stats['earnings'] = $conn->query("SELECT SUM(p.deposit + p.rent) FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE b.status='confirmed'")->fetchColumn();
}

// Data for Charts
$months = [];
$userCounts = [];
$revenueCounts = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $months[] = date('M', strtotime("-$i months"));
    $uStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE TO_CHAR(created_at, 'YYYY-MM') = ? AND role != 'admin'");
    $uStmt->execute([$date]);
    $userCounts[] = (int)$uStmt->fetchColumn();
    if ($hasPayments) {
        $rStmt = $conn->prepare("SELECT SUM(amount) FROM payments WHERE TO_CHAR(created_at, 'YYYY-MM') = ? AND status='Success'");
    } else {
        $rStmt = $conn->prepare("SELECT SUM(p.deposit + p.rent) FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE TO_CHAR(b.booking_date, 'YYYY-MM') = ? AND b.status='confirmed'");
    }
    $rStmt->execute([$date]);
    $revenueCounts[] = (float)$rStmt->fetchColumn();
}

// Handle Approval
if (isset($_GET['approve'])) {
    $stmt = $conn->prepare("UPDATE pg_listings SET status='approved' WHERE id=?");
    $stmt->execute([$_GET['approve']]);
    header("Location: dashboard.php");
    exit;
}
if (isset($_GET['reject'])) {
    $stmt = $conn->prepare("UPDATE pg_listings SET status='rejected' WHERE id=?");
    $stmt->execute([$_GET['reject']]);
    header("Location: dashboard.php");
    exit;
}

$pendingPgs = $conn->query("SELECT * FROM pg_listings WHERE status='pending'")->fetchAll();
$bookings = $conn->query("SELECT b.*, u.name as user_name, pg.name as pg_name FROM bookings b JOIN users u ON b.user_id=u.id JOIN pg_listings pg ON b.pg_id=pg.id ORDER BY b.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <h1 class="display-3 mb-5 border-bottom border-5 border-dark">CENTRAL COMMAND</h1>
        
        <div class="row text-center mb-5">
            <div class="col-md-3">
                <div class="brutal-card bg-brutal-white">
                    <h5 class="small mb-2">TOTAL USERS</h5>
                    <h1 class="mb-0"><?php echo $stats['students'] + $stats['owners']; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brutal-card bg-brutal-white">
                    <h5 class="small mb-2">PG LISTINGS</h5>
                    <h1 class="text-primary mb-0"><?php echo $stats['pgs']; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brutal-card bg-brutal-white">
                    <h5 class="small mb-2">BOOKINGS</h5>
                    <h1 class="text-warning mb-0"><?php echo $stats['bookings']; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brutal-card bg-brutal-white border-brutal" style="background: #28a745; color: white;">
                    <h5 class="small mb-2">REVENUE</h5>
                    <h1 class="mb-0">₹<?php echo number_format((float)$stats['earnings']); ?></h1>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-8">
                <div class="brutal-card bg-brutal-white">
                    <h4 class="mb-4 border-bottom border-2 border-dark">ANALYTICS FEED</h4>
                    <canvas id="growthChart" height="150"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="brutal-card bg-brutal-white">
                    <h4 class="mb-4 border-bottom border-2 border-dark">DEMOGRAPHICS</h4>
                    <canvas id="userTypeChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-5">
                <div class="brutal-card h-100">
                    <h4 class="mb-4 border-bottom border-2 border-dark">PENDING AUTHORIZATIONS</h4>
                    <?php if (count($pendingPgs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead><tr><th>PROPERTY</th><th>ACTION</th></tr></thead>
                                <tbody>
                                    <?php foreach ($pendingPgs as $pg): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo strtoupper(htmlspecialchars($pg['name'])); ?></div>
                                                <small><?php echo strtoupper(htmlspecialchars($pg['city'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="?approve=<?php echo $pg['id']; ?>" class="btn btn-brutal btn-sm py-1 px-2 text-white bg-success">APPROVE</a>
                                                    <a href="?reject=<?php echo $pg['id']; ?>" class="btn btn-brutal btn-sm py-1 px-2 text-white bg-danger">REJECT</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">ALL SYSTEMS CLEAR. NO PENDING REQUESTS.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6 mb-5">
                <div class="brutal-card h-100">
                    <h4 class="mb-4 border-bottom border-2 border-dark">RECENT TRANSACTIONS</h4>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>USER</th><th>PROPERTY</th><th>BED</th></tr></thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><?php echo strtoupper(htmlspecialchars($b['user_name'])); ?></td>
                                        <td><?php echo strtoupper(htmlspecialchars($b['pg_name'])); ?></td>
                                        <td class="fw-bold">#<?php echo $b['bed_number']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script>
        const ctx = document.getElementById('growthChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: 'USERS',
                        data: <?php echo json_encode($userCounts); ?>,
                        borderColor: '#111',
                        borderWidth: 4,
                        backgroundColor: 'rgba(0,0,0,0.1)',
                        fill: true,
                        pointRadius: 6,
                        pointBackgroundColor: '#FFE500'
                    },
                    {
                        label: 'REVENUE',
                        data: <?php echo json_encode($revenueCounts); ?>,
                        borderColor: '#FF3B30',
                        borderWidth: 4,
                        backgroundColor: 'transparent',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { font: { family: 'Anton', size: 14 } } } },
                scales: {
                    y: { border: { width: 4, color: '#111' } },
                    y1: { position: 'right', border: { width: 4, color: '#111' } }
                }
            }
        });

        const ctx2 = document.getElementById('userTypeChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['STUDENTS', 'OWNERS'],
                datasets: [{
                    data: [<?php echo $stats['students']; ?>, <?php echo $stats['owners']; ?>],
                    backgroundColor: ['#FFE500', '#111111'],
                    borderColor: '#111',
                    borderWidth: 4
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { font: { family: 'Anton' } } } }
            }
        });
    </script>
</body>
</html>
