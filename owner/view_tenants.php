<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

$pg_id = $_GET['id'] ?? 0;
$owner_id = $_SESSION['user_id'];

// Verify Ownership and Fetch PG Details
$stmtPG = $conn->prepare("SELECT name FROM pg_listings WHERE id = ? AND owner_id = ?");
$stmtPG->execute([$pg_id, $owner_id]);
$pg = $stmtPG->fetch();

if (!$pg) {
    die("ACCESS DENIED. PG NOT FOUND.");
}

// Fetch Detailed Tenant List
$stmtTenants = $conn->prepare("SELECT b.id as booking_id, u.name, u.email, u.phone, u.gender, b.bed_number, b.booking_date, b.payment_status, b.payment_method, b.leave_notice_date 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                WHERE b.pg_id = ? AND b.status = 'confirmed'
                                ORDER BY b.bed_number ASC");
$stmtTenants->execute([$pg_id]);
$tenants = $stmtTenants->fetchAll();

// Handle Payment Confirmation
if (isset($_POST['confirm_payment'])) {
    $b_id = $_POST['booking_id'];
    
    // Security check
    $stmtConfirm = $conn->prepare("UPDATE bookings b 
                                   SET payment_status = 'paid' 
                                   FROM pg_listings p 
                                   WHERE b.pg_id = p.id AND b.id = ? AND p.owner_id = ?");
    if ($stmtConfirm->execute([$b_id, $owner_id])) {
        $success_msg = "PAYMENT ENCRYPTED. STATUS: PAID.";
    }
    // Refresh tenant list
    $stmtTenants->execute([$pg_id]);
    $tenants = $stmtTenants->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TENANT LOG - <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="brutal-card">
            <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-5 border-dark pb-3">
                <h1 class="display-5 mb-0">TENANT MANIFEST: <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></h1>
                <a href="dashboard.php" class="btn btn-brutal bg-white text-black btn-sm">BACK TO HQ</a>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success border-brutal rounded-0 mb-4 fw-bold"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>BED</th>
                            <th>IDENTITY</th>
                            <th>JOINED</th>
                            <th>NEXT DUE</th>
                            <th>STATUS</th>
                            <th>REFUNDABLE</th>
                            <th>PAYMENT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tenants): ?>
                            <?php foreach ($tenants as $t): ?>
                                <?php
                                    $stmtPGData = $conn->prepare("SELECT rent, deposit FROM pg_listings WHERE id = ?");
                                    $stmtPGData->execute([$pg_id]);
                                    $pg_vals = $stmtPGData->fetch();

                                    $join_date = strtotime($t['booking_date']);
                                    $next_due = strtotime("+1 month", $join_date);
                                    while ($next_due < time()) {
                                        $next_due = strtotime("+1 month", $next_due);
                                    }

                                    $notice_status = "ACTIVE";
                                    $refund_amount = $pg_vals['deposit'];
                                    $bg_color = "bg-success";
                                    
                                    if ($t['leave_notice_date']) {
                                        $notice_date = strtotime($t['leave_notice_date']);
                                        $checkout_date = strtotime("+2 months", $notice_date);
                                        $notice_status = "EXITING: " . date('d M', $checkout_date);
                                        $refund_amount = $pg_vals['deposit'] - ($pg_vals['rent'] * 2);
                                        $bg_color = "bg-warning";
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $t['bed_number']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo strtoupper(htmlspecialchars($t['name'])); ?></div>
                                        <div class="small fw-bold opacity-75"><?php echo $t['phone']; ?></div>
                                    </td>
                                    <td class="small fw-bold"><?php echo date('d M Y', $join_date); ?></td>
                                    <td class="small fw-bold text-brutal-red"><?php echo date('d M Y', $next_due); ?></td>
                                    <td>
                                        <span class="badge <?php echo $bg_color; ?> text-white"><?php echo strtoupper($notice_status); ?></span>
                                    </td>
                                    <td class="fw-bold text-center">
                                        ₹<?php echo number_format($refund_amount); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $t['payment_status'] == 'paid' ? 'success' : 'brutal-black'; ?> text-white">
                                            <?php echo strtoupper($t['payment_status']); ?> [<?php echo strtoupper($t['payment_method']); ?>]
                                        </span>
                                        <?php if ($t['payment_method'] == 'Cash' && $t['payment_status'] == 'pending'): ?>
                                            <form method="POST" class="d-inline ms-2">
                                                <input type="hidden" name="booking_id" value="<?php echo $t['booking_id']; ?>">
                                                <button type="submit" name="confirm_payment" class="btn btn-brutal bg-success text-white py-0 px-2 btn-sm" onclick="return confirm('AUTHORIZE CASH RECEIPT?')">CONFIRM</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <h4 class="text-muted">ZERO TENANTS DETECTED IN THIS SECTOR.</h4>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
