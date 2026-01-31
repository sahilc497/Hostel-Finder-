<?php
session_start();
require_once '../config/database.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : 'Guest';

if ($is_logged_in) {
    if ($_SESSION['role'] !== 'student') {
        $dash = ($_SESSION['role'] == 'owner') ? '../owner/dashboard.php' : '../admin/dashboard.php';
        header("Location: $dash");
        exit;
    }
    $stmtCheck = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'confirmed'");
    $stmtCheck->execute([$_SESSION['user_id']]);
    if ($stmtCheck->fetch()) {
        header("Location: room_details.php");
        exit;
    }
}

// Search Params
$city = $_GET['city'] ?? '';
$gender = $_GET['gender'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$sql = "SELECT * FROM pg_listings WHERE status = 'approved'";
$params = [];

if ($city) {
    $sql .= " AND (city ILIKE ? OR address ILIKE ?)";
    $params[] = "%$city%";
    $params[] = "%$city%";
}
if ($gender) {
    $sql .= " AND gender_type = ?";
    $params[] = $gender;
}
if ($max_price) {
    $sql .= " AND rent <= ?";
    $params[] = $max_price;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pgs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hostels & PGs - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <style>
        .hero-brutal {
            background: var(--brutal-black);
            color: var(--brutal-white);
            padding: 80px 0;
            border-bottom: var(--brutal-border);
        }
        .pg-img-brutal {
            height: 250px;
            object-fit: cover;
            border-bottom: var(--brutal-border);
            width: 100%;
        }
    </style>
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="hero-brutal">
        <div class="container text-center">
            <h1 class="display-3 mb-2">TARGET YOUR STAY</h1>
            <p class="fs-5 opacity-75">ELIMINATE THE SEARCH. SECURE THE ROOM.</p>
            
            <div class="brutal-card mt-5 bg-brutal-white text-start">
                <form class="row g-3" method="GET">
                    <div class="col-md-4">
                        <label class="fw-bold small">WHERE TO?</label>
                        <input type="text" name="city" class="form-control" placeholder="CITY OR AREA" value="<?php echo htmlspecialchars($city); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small">WHO FOR?</label>
                        <select name="gender" class="form-control">
                            <option value="">ANY GENDER</option>
                            <option value="Boys" <?php echo $gender == 'Boys' ? 'selected' : ''; ?>>BOYS ONLY</option>
                            <option value="Girls" <?php echo $gender == 'Girls' ? 'selected' : ''; ?>>GIRLS ONLY</option>
                            <option value="Co-ed" <?php echo $gender == 'Co-ed' ? 'selected' : ''; ?>>CO-ED</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small">MAX RENT (₹)</label>
                        <input type="number" name="max_price" class="form-control" placeholder="MAX BUDGET" value="<?php echo htmlspecialchars($max_price); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-brutal w-100">DEPLOY SEARCH</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container brutal-container">
        <h2 class="mb-5 border-bottom border-4 border-dark d-inline-block pb-2">AVAILABLE OPERATIONS</h2>
        
        <div class="row">
            <?php if (count($pgs) > 0): ?>
                <?php foreach ($pgs as $pg): ?>
                    <div class="col-md-4 mb-5">
                        <div class="brutal-card p-0 overflow-hidden h-100 d-flex flex-column">
                            <img src="<?php echo $pg['image'] ? '../' . $pg['image'] : 'https://via.placeholder.com/400x300?text=ROOM+DETECTED'; ?>" class="pg-img-brutal" alt="PG Image">
                            <div class="p-4 flex-grow-1">
                                <span class="badge bg-brutal-black text-white mb-2"><?php echo $pg['gender_type']; ?></span>
                                <h3 class="mb-1"><?php echo htmlspecialchars($pg['name']); ?></h3>
                                <p class="text-muted small mb-3"><i class="fa fa-map-marker me-1"></i><?php echo htmlspecialchars($pg['city']); ?></p>
                                <p class="mb-4"><?php echo substr(htmlspecialchars($pg['description']), 0, 100); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <h4 class="text-brutal-red mb-0">₹<?php echo number_format($pg['rent']); ?>/MO</h4>
                                    <div class="small fw-bold">CAPACITY: <?php echo $pg['total_beds']; ?></div>
                                </div>
                            </div>
                            <div class="p-4 pt-0">
                                <?php
                                $stmtCount = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE pg_id = ? AND status = 'confirmed'");
                                $stmtCount->execute([$pg['id']]);
                                $occupied = $stmtCount->fetchColumn();
                                if ($occupied >= $pg['total_beds']): ?>
                                    <button class="btn btn-brutal w-100 btn-brutal-red" disabled>HOUSEFULL</button>
                                <?php else: ?>
                                    <a href="pg_details.php?id=<?php echo $pg['id']; ?>" class="btn btn-brutal w-100">VIEW INTEL & BOOK</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="brutal-card bg-brutal-red text-white text-center">
                        <h2>NO TARGETS FOUND</h2>
                        <p>YOUR SEARCH CRITERIA RETURNED ZERO RESULTS. ABORT OR RETRY.</p>
                        <a href="dashboard.php" class="btn btn-brutal bg-white text-black mt-3">RESET SEARCH</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
