<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    die("ACCESS DENIED");
}

$pg_id = $_GET['id'] ?? 0;
// Verify ownership
$stmt = $conn->prepare("SELECT * FROM pg_listings WHERE id = ? AND owner_id = ?");
$stmt->execute([$pg_id, $_SESSION['user_id']]);
$pg = $stmt->fetch();

if (!$pg) {
    die("PG NOT FOUND.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $description = trim($_POST['description']);
    $rules = trim($_POST['rules']);
    $amenities = trim($_POST['amenities']);
    $rent = !empty($_POST['rent']) ? $_POST['rent'] : 0;
    $deposit = !empty($_POST['deposit']) ? $_POST['deposit'] : 0;
    $total_beds = !empty($_POST['total_beds']) ? $_POST['total_beds'] : 10;

    // Handle Image Update
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
            $stmtImg = $conn->prepare("UPDATE pg_listings SET image=? WHERE id=?");
            $stmtImg->execute([$image_path, $pg_id]);
        }
    }

    $stmtUpdate = $conn->prepare("UPDATE pg_listings SET name=?, city=?, address=?, description=?, rules=?, amenities=?, rent=?, deposit=?, total_beds=? WHERE id=?");
    if ($stmtUpdate->execute([$name, $city, $address, $description, $rules, $amenities, $rent, $deposit, $total_beds, $pg_id])) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "UPDATE FAILED. RETRY.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RECONFIGURE INTEL - <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="brutal-card">
                    <h1 class="display-4 text-center mb-5 border-bottom border-5 border-dark pb-3">MOD PROTOCOL: <?php echo strtoupper(htmlspecialchars($pg['name'])); ?></h1>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger border-brutal rounded-0 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">UPDATE VISUAL INTEL</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($pg['image']): ?>
                                <div class="mt-2 p-2 bg-light border-brutal d-inline-block">
                                    <small class="fw-bold">CURRENT INTEL: <a href="../<?php echo htmlspecialchars($pg['image']); ?>" target="_blank" class="text-brutal-red">VIEW FILE</a></small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">PROPERTY DESIGNATION</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($pg['name']); ?>" required>
                        </div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">MONTHLY RENT (₹)</label>
                                <input type="number" name="rent" class="form-control" value="<?php echo htmlspecialchars($pg['rent']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">SECURITY DEPOSIT (₹)</label>
                                <input type="number" name="deposit" class="form-control" value="<?php echo htmlspecialchars($pg['deposit'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">OPERATIONAL BEDS</label>
                                <input type="number" name="total_beds" class="form-control" value="<?php echo htmlspecialchars($pg['total_beds'] ?? 10); ?>" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CITY / SECTOR</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($pg['city']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ADDRESS</label>
                                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($pg['address']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">MISSION BRIEFING (DESCRIPTION)</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($pg['description']); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">AMENITIES (COMMA SEPARATED)</label>
                            <input type="text" name="amenities" class="form-control" value="<?php echo htmlspecialchars($pg['amenities']); ?>">
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-bold">RULES OF ENGAGEMENT</label>
                            <textarea name="rules" class="form-control" rows="4"><?php echo htmlspecialchars($pg['rules']); ?></textarea>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-brutal fs-4 py-3">REDEPLOY INTEL</button>
                            <a href="dashboard.php" class="btn btn-brutal bg-white text-black btn-sm py-2">ABORT MISSION</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/header_brutal.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
