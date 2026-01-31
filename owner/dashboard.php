<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM pg_listings WHERE owner_id = ? ORDER BY created_at DESC");
$stmt->execute([$owner_id]);
$listings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Headquarters - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-5 border-dark pb-3">
            <h1 class="display-4 mb-0">PROPERTY PORTFOLIO</h1>
            <a href="add_listing.php" class="btn btn-brutal">RECRUIT NEW PG</a>
        </div>
        
        <?php if (count($listings) > 0): ?>
            <div class="row">
                <?php foreach ($listings as $pg): ?>
                    <div class="col-md-4 mb-5">
                        <div class="brutal-card h-100 d-flex flex-column">
                            <div class="mb-3">
                                <span class="badge bg-<?php echo $pg['status'] == 'approved' ? 'success' : ($pg['status'] == 'rejected' ? 'danger' : 'warning'); ?> text-white">
                                    STATUS: <?php echo strtoupper($pg['status']); ?>
                                </span>
                            </div>
                            <h2 class="mb-1"><?php echo htmlspecialchars($pg['name']); ?></h2>
                            <p class="text-muted fw-bold mb-4"><?php echo strtoupper(htmlspecialchars($pg['city'])); ?></p>
                            
                            <div class="bg-light p-3 border-brutal mb-4 flex-grow-1">
                                <h5 class="mb-3 border-bottom border-2 border-dark">ACTIVE TENANTS</h5>
                                <?php
                                $stmtTenants = $conn->prepare("SELECT u.name, b.bed_number FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.pg_id = ? AND b.status = 'confirmed'");
                                $stmtTenants->execute([$pg['id']]);
                                $tenants = $stmtTenants->fetchAll();
                                if ($tenants): 
                                ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach($tenants as $t): ?>
                                            <li class="mb-2"><i class="fa fa-user me-2"></i> <?php echo strtoupper(htmlspecialchars($t['name'])); ?> [BED <?php echo $t['bed_number']; ?>]</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">ZERO BOOKINGS DETECTED.</p>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-3 mt-auto">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="edit_listing.php?id=<?php echo $pg['id']; ?>" class="btn btn-brutal btn-sm w-100" style="background: #ffc107; color: black !important;">EDIT INTEL</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="view_tenants.php?id=<?php echo $pg['id']; ?>" class="btn btn-brutal btn-sm w-100">TENANT LIST</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="brutal-card bg-brutal-white text-center">
                <h3>NO PROPERTIES COMMANDED</h3>
                <p>YOU HAVE ZERO LISTINGS IN YOUR PORTFOLIO. START DEPLOYING NOW.</p>
                <a href="add_listing.php" class="btn btn-brutal mt-3">INITIALIZE FIRST PG</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
