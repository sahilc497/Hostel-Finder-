<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch User's Booking
$stmt = $conn->prepare("SELECT b.*, p.name as pg_name, p.address as pg_address, p.city as pg_city, p.image as pg_image, p.owner_id, u.name as owner_name, u.phone as owner_phone 
                        FROM bookings b 
                        JOIN pg_listings p ON b.pg_id = p.id 
                        JOIN users u ON p.owner_id = u.id
                        WHERE b.user_id = ? AND b.status = 'confirmed'");
$stmt->execute([$user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: dashboard.php");
    exit;
}

// Handle Leave Notice Submission
if (isset($_POST['apply_notice']) && !$booking['leave_notice_date']) {
    $stmtNotice = $conn->prepare("UPDATE bookings SET leave_notice_date = NOW() WHERE id = ?");
    if ($stmtNotice->execute([$booking['id']])) {
        header("Location: room_details.php?notice=success");
        exit;
    }
}

// Fetch Roommates
$stmtRoommates = $conn->prepare("SELECT u.name, b.bed_number 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                WHERE b.pg_id = ? AND b.user_id != ? AND b.user_id != ? AND b.status = 'confirmed'");
$stmtRoommates->execute([$booking['pg_id'], $user_id, $booking['owner_id']]);
$roommates = $stmtRoommates->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MISSION STATUS - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row g-5">
            <div class="col-md-8">
                <div class="brutal-card p-0 overflow-hidden mb-5">
                    <img src="<?php echo !empty($booking['pg_image']) ? '../' . $booking['pg_image'] : 'https://via.placeholder.com/800x300?text=BASE+HQ'; ?>" style="height: 350px; object-fit: cover; width:100%; border-bottom: var(--brutal-border);" alt="Hostel">
                    <div class="p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="display-4 mb-1"><?php echo strtoupper(htmlspecialchars($booking['pg_name'])); ?></h1>
                                <p class="text-muted fw-bold"><i class="fa fa-map-marker me-1"></i><?php echo strtoupper(htmlspecialchars($booking['pg_address'])); ?>, <?php echo strtoupper(htmlspecialchars($booking['pg_city'])); ?></p>
                            </div>
                            <span class="badge bg-success text-white px-3 py-2 fs-5 mt-2 shadow-sm">DEPLOYED</span>
                        </div>
                        
                        <div class="brutal-card bg-brutal-white shadow-none border-2 mb-0">
                            <div class="row text-center g-4">
                                <div class="col-6">
                                    <h5 class="small mb-1">ASSIGNED BED</h5>
                                    <h2 class="mb-0">#<?php echo $booking['bed_number']; ?></h2>
                                </div>
                                <div class="col-6 border-start border-2 border-dark">
                                    <h5 class="small mb-1">JOINING DATE</h5>
                                    <h2 class="mb-0"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mb-4 border-bottom border-4 border-dark d-inline-block pb-1">SQUAD MEMBERS</h3>
                <div class="row">
                    <?php if ($roommates): ?>
                        <?php foreach ($roommates as $rm): ?>
                            <div class="col-md-6 mb-4">
                                <div class="brutal-card p-3 d-flex align-items-center mb-0 bg-brutal-white">
                                    <div class="bg-brutal-black text-white rounded-0 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-family: 'Anton'; font-size: 2rem; border: var(--brutal-border);">
                                        <?php echo strtoupper(substr($rm['name'], 0, 1)); ?>
                                    </div>
                                    <div class="ms-4">
                                        <h4 class="mb-0"><?php echo strtoupper(htmlspecialchars($rm['name'])); ?></h4>
                                        <span class="badge bg-brutal-yellow text-black border-2 mt-1">BED #<?php echo $rm['bed_number']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="brutal-card bg-light text-center">
                                <h4 class="text-muted mb-0">SOLE OPERATOR DETECTED. NO ROOMMATES FOUND.</h4>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Rent Reminder -->
                <?php
                $join_date = strtotime($booking['booking_date']);
                $next_due = strtotime("+1 month", $join_date);
                while ($next_due < time()) {
                    $next_due = strtotime("+1 month", $next_due);
                }
                ?>
                <div class="brutal-card bg-brutal-black text-white p-4 mb-4">
                    <h3 class="mb-1 text-brutal-yellow">RENT DUE</h3>
                    <p class="small opacity-75 mb-3">NEXT PAYMENT PROTOCOL:</p>
                    <h1 class="display-5 mb-4"><?php echo strtoupper(date('d M Y', $next_due)); ?></h1>
                    <a href="#" class="btn btn-brutal bg-brutal-yellow text-black w-100 fw-bold">SCHEDULE PAYMENT</a>
                </div>

                <!-- Exit Protocol -->
                <div class="brutal-card bg-brutal-white p-4 mb-4">
                    <h3 class="mb-4 border-bottom border-4 border-dark d-inline-block pb-1">EXIT PROTOCOL</h3>
                    
                    <?php if ($booking['leave_notice_date']): ?>
                        <?php 
                        $notice_start = strtotime($booking['leave_notice_date']);
                        $checkout_date = strtotime("+2 months", $notice_start);
                        
                        $stmtPG = $conn->prepare("SELECT rent, deposit FROM pg_listings WHERE id = ?");
                        $stmtPG->execute([$booking['pg_id']]);
                        $pg_data = $stmtPG->fetch();
                        $total_rent_due = $pg_data['rent'] * 2;
                        $refundable = $pg_data['deposit'] - $total_rent_due;
                        ?>
                        <div class="brutal-card bg-brutal-yellow p-3 mb-4 shadow-none">
                            <h4 class="mb-1">NOTICE ACTIVE</h4>
                            <p class="mb-0 small fw-bold">CHECKOUT: <?php echo strtoupper(date('d M Y', checkout_date)); ?></p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tr><td>DEPOSIT</td><td class="text-end fw-bold">₹<?php echo number_format($pg_data['deposit']); ?></td></tr>
                                <tr><td>NOTICE RENT</td><td class="text-end text-brutal-red fw-bold">-₹<?php echo number_format($total_rent_due); ?></td></tr>
                                <tr class="border-top-2 border-dark"><td>REFUNDABLE</td><td class="text-end fw-bold fs-5 <?php echo $refundable >= 0 ? 'text-success' : 'text-danger'; ?>">₹<?php echo number_format($refundable); ?></td></tr>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="small fw-bold mb-4">MANDATORY 2-MONTH NOTICE PERIOD APPLIES. RENT WILL BE DEDUCTED FROM DEPOSIT.</p>
                        <form method="POST" onsubmit="return confirm('INITIATE EXIT PROTOCOL? THIS ACTION IS FINAL.')">
                            <button type="submit" name="apply_notice" class="btn btn-brutal btn-brutal-red w-100 btn-sm">APPLY FOR EXIT</button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Contact -->
                <div class="brutal-card bg-brutal-white p-4">
                    <h3 class="mb-4 border-bottom border-4 border-dark d-inline-block pb-1">COMMANDER</h3>
                    <div class="text-center mb-4">
                        <div class="bg-brutal-black text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; border: var(--brutal-border);">
                            <i class="fa fa-user-secret fa-3x"></i>
                        </div>
                        <h4 class="mb-1"><?php echo strtoupper(htmlspecialchars($booking['owner_name'])); ?></h4>
                        <p class="small fw-bold text-muted mb-0">PROPERTY OWNER</p>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="tel:<?php echo $booking['owner_phone']; ?>" class="btn btn-brutal w-100 py-2">CALL COMMANDER</a>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $booking['owner_phone']); ?>" target="_blank" class="btn btn-brutal w-100 py-2 bg-success text-white">WHATSAPP ENCRYPTED</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
