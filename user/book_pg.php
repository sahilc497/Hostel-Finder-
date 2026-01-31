<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$pg_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Check if user already has a booking
$stmtCheck = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$stmtCheck->execute([$user_id]);
if ($stmtCheck->fetch()) {
    header("Location: room_details.php");
    exit;
}

// Fetch PG Details
$stmt = $conn->prepare("SELECT * FROM pg_listings WHERE id = ?");
$stmt->execute([$pg_id]);
$pg = $stmt->fetch();

if (!$pg) die("PG not found.");

// Fetch Existing Bookings
$stmtBookings = $conn->prepare("SELECT * FROM bookings WHERE pg_id = ? AND status = 'confirmed'");
$stmtBookings->execute([$pg_id]);
$bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

$occupied_beds = [];
foreach ($bookings as $b) {
    if ($b['bed_number']) {
        $occupied_beds[] = $b['bed_number'];
    }
}

$total_beds = $pg['total_beds'] ?: 10;

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bed_number = $_POST['bed_number'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? 'Online';
    $payment_status = ($payment_method == 'Cash') ? 'pending' : 'paid';
    
    if ($bed_number <= 0) {
        $error = "MISSING TARGET. SELECT A BED.";
    } else {
        if (in_array($bed_number, $occupied_beds)) {
            $error = "TARGET OCCUPIED. REDEPLOY TO ANOTHER BED.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO bookings (user_id, pg_id, bed_number, status, payment_status, payment_method) VALUES (?, ?, ?, 'confirmed', ?, ?)");
            if ($stmtInsert->execute([$user_id, $pg_id, $bed_number, $payment_status, $payment_method])) {
                header("Location: room_details.php");
                exit;
            } else {
                $error = "OPERATION FAILED. RETRY.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE BED - <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <style>
        .bed-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .bed-box {
            width: 60px;
            height: 60px;
            border: 3px solid #111;
            background-color: #f8f9fa;
            color: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-family: 'Anton', sans-serif;
            font-size: 1.2rem;
            transition: 0.2s;
            box-shadow: 4px 4px 0px #111;
        }
        .bed-box:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0px #111; background-color: #FFE500; }
        .bed-box.occupied {
            background-color: #111;
            color: #444;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .bed-box.selected {
            background-color: #FFE500;
            transform: translate(-4px, -4px);
            box-shadow: 8px 8px 0px #111;
        }
        input[type="radio"] { display: none; }
        
        .payment-check:checked + .payment-label {
            background: #111 !important;
            color: #FFE500 !important;
            box-shadow: 4px 4px 0px #FFE500 !important;
        }
        .payment-label {
            border: 3px solid #111;
            padding: 15px;
            text-align: center;
            font-family: 'Anton', sans-serif;
            cursor: pointer;
            transition: 0.2s;
            background: white;
        }
    </style>
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="brutal-card">
                    <h1 class="display-5 text-center mb-5 border-bottom border-5 border-dark pb-3">SELECT YOUR SECTOR</h1>
                    
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <h3 class="mb-4">PG: <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></h3>
                            <div class="brutal-card bg-light p-3 shadow-none border-2">
                                <h4 class="mb-2">RENT: ₹<?php echo number_format($pg['rent']); ?>/MO</h4>
                                <h4 class="mb-0 text-brutal-red">DEPOSIT: ₹<?php echo number_format($pg['deposit']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-6 text-center d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-center gap-4">
                                <div><div class="bed-box mx-auto" style="cursor:default;box-shadow:none;"></div><small class="fw-bold d-block mt-2">OPEN</small></div>
                                <div><div class="bed-box occupied mx-auto" style="cursor:default;"></div><small class="fw-bold d-block mt-2">TAKEN</small></div>
                                <div><div class="bed-box selected mx-auto" style="cursor:default;"></div><small class="fw-bold d-block mt-2">TARGET</small></div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger border-brutal rounded-0 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (count($occupied_beds) >= $total_beds): ?>
                        <div class="brutal-card bg-brutal-red text-white text-center py-5">
                            <h1 class="display-2 mb-3">HOUSEFULL</h1>
                            <p class="fs-4">ZERO SECTORS AVAILABLE. ABORT OPERATION.</p>
                            <a href="dashboard.php" class="btn btn-brutal bg-white text-black mt-4">REDEPLOY SEARCH</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="bed-grid mb-5">
                                <?php for ($i = 1; $i <= $total_beds; $i++): ?>
                                    <?php $is_occupied = in_array($i, $occupied_beds); ?>
                                    <label>
                                        <input type="radio" name="bed_number" value="<?php echo $i; ?>" <?php echo $is_occupied ? 'disabled' : ''; ?> required onclick="selectBed(this)">
                                        <div class="bed-box <?php echo $is_occupied ? 'occupied' : ''; ?>" id="box-<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </div>
                                    </label>
                                <?php endfor; ?>
                            </div>

                            <div class="border-top border-4 border-dark pt-5 mt-5">
                                <h3 class="mb-4">PAYMENT PROTOCOL</h3>
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-6">
                                        <input type="radio" class="payment-check" name="payment_method" id="pay_online" value="Online" checked>
                                        <label class="payment-label w-100" for="pay_online">ONLINE TRANSFER</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="payment-check" name="payment_method" id="pay_cash" value="Cash">
                                        <label class="payment-label w-100" for="pay_cash">CASH ON ARRIVAL</label>
                                    </div>
                                </div>

                                <div class="brutal-card bg-brutal-white shadow-none border-2 mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h4 class="mb-0">SECURITY DEPOSIT</h4>
                                        <h4 class="mb-0">₹<?php echo number_format($pg['deposit']); ?></h4>
                                    </div>
                                    <div class="d-flex justify-content-between border-top border-2 border-dark pt-2 mt-2">
                                        <h2 class="mb-0">TOTAL DUE NOW</h2>
                                        <h2 class="mb-0 text-brutal-red">₹<?php echo number_format($pg['deposit']); ?></h2>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-brutal w-100 fs-3 py-3">INITIALIZE BOOKING</button>
                                <p class="text-center mt-3 small fw-bold">BY INITIALIZING, YOU COMMIT TO PAYING THE DEPOSIT IMMEDIATELY.</p>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script>
        function selectBed(radio) {
            document.querySelectorAll('.bed-box:not(.occupied)').forEach(box => {
                box.classList.remove('selected');
            });
            document.getElementById('box-' + radio.value).classList.add('selected');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
