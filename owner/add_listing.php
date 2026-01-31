<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $gender_type = $_POST['gender_type'];
    $rent = !empty($_POST['rent']) ? $_POST['rent'] : 0;
    $deposit = !empty($_POST['deposit']) ? $_POST['deposit'] : 0;
    $total_beds = !empty($_POST['total_beds']) ? $_POST['total_beds'] : 10;
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $description = trim($_POST['description'] ?? '');
    $rules = trim($_POST['rules'] ?? '');
    $amenities = trim($_POST['amenities'] ?? '');

    // Image Upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
        }
    }

    $stmt = $conn->prepare("INSERT INTO pg_listings (owner_id, name, address, city, gender_type, latitude, longitude, description, rules, amenities, status, rent, deposit, image, total_beds) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $name, $address, $city, $gender_type, $latitude, $longitude, $description, $rules, $amenities, $rent, $deposit, $image_path, $total_beds])) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "OPERATION FAILED. RETRY.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEPLOY NEW PG - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <style>
        #map { height: 350px; width: 100%; border: var(--brutal-border); margin-bottom: 20px; box-shadow: var(--brutal-shadow); }
    </style>
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="brutal-card">
                    <h1 class="display-4 text-center mb-5 border-bottom border-5 border-dark pb-3">INITIALIZE PROPERTY LISTING</h1>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger border-brutal rounded-0 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">PROPERTY DESIGNATION (NAME)</label>
                            <input type="text" name="name" class="form-control" placeholder="E.G. ALPHA HOSTEL" required>
                        </div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">CITY / SECTOR</label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">GENDER PROTOCOL</label>
                                <select name="gender_type" class="form-control" required>
                                    <option value="Co-ed">CO-ED</option>
                                    <option value="Boys">BOYS ONLY</option>
                                    <option value="Girls">GIRLS ONLY</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">TOTAL BEDS (CAPACITY)</label>
                                <input type="number" name="total_beds" class="form-control" placeholder="E.G. 10" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">MONTHLY RENT (₹)</label>
                                <input type="number" name="rent" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">SECURITY DEPOSIT (₹)</label>
                                <input type="number" name="deposit" class="form-control" placeholder="E.G. 5000" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">VISUAL INTEL (MAIN IMAGE)</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">EXACT ADDRESS</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold">GEOPOSITION (PIN ON MAP)</label>
                            <div id="map"></div>
                            <input type="hidden" name="latitude" id="lat" required>
                            <input type="hidden" name="longitude" id="lng" required>
                            <div class="p-2 border-brutal bg-light d-inline-block">
                                <span id="loc-status" class="fw-bold">STATUS: NO COORDS DETECTED</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">INTELLIGENCE BRIEFING (DESCRIPTION)</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="DESCRIBE THE PROPERTY IN DETAIL..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">AMENITIES (COMMA SEPARATED)</label>
                            <input type="text" name="amenities" class="form-control" placeholder="WIFI, LAUNDRY, CCTV, FOOD">
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-bold">RULES OF ENGAGEMENT</label>
                            <textarea name="rules" class="form-control" rows="4" placeholder="CURFEW, GUEST POLICIES, ETC..."></textarea>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-brutal fs-4 py-3">DEPLOY LISTING</button>
                            <a href="dashboard.php" class="btn btn-brutal bg-white text-black btn-sm py-2">ABORT MISSION</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([16.7050, 74.2433], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker;
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('loc-status').innerText = "COORDS LOCKED: " + lat.toFixed(5) + ", " + lng.toFixed(5);
            document.getElementById('loc-status').style.color = "#FF3B30";
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
