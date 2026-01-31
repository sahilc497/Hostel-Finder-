<?php
session_start();
require_once '../config/database.php';

$pg_id = $_GET['id'] ?? 0;
// Fetch PG Details
$stmt = $conn->prepare("SELECT p.*, u.name as owner_name, u.phone as owner_phone 
                        FROM pg_listings p 
                        JOIN users u ON p.owner_id = u.id 
                        WHERE p.id = ?");
$stmt->execute([$pg_id]);
$pg = $stmt->fetch();

if (!$pg) die("PG not found.");

// Check if user already has a booking
if (isset($_SESSION['user_id'])) {
    $stmtCheck = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'confirmed'");
    $stmtCheck->execute([$_SESSION['user_id']]);
    if ($stmtCheck->fetch()) {
        header("Location: room_details.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo strtoupper(htmlspecialchars($pg['name'])); ?> - INTEL - STUDENTNEST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/brutalism.css">
    <style>
        #map { height: 400px; width: 100%; border: var(--brutal-border); margin-top: 20px; box-shadow: var(--brutal-shadow); }
        .pg-image-brutal { height: 100%; max-height: 500px; object-fit: cover; width: 100%; border-bottom: var(--brutal-border); }
    </style>
</head>
<body>

    <?php include '../includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <div class="row g-5">
            <!-- Left Column -->
            <div class="col-md-8">
                <div class="brutal-card p-0 overflow-hidden">
                    <img src="<?php echo $pg['image'] ? '../' . $pg['image'] : 'https://via.placeholder.com/800x400?text=BASE+DETECTED'; ?>" class="pg-image-brutal" alt="PG Image">
                    <div class="p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h1 class="display-4 mb-1"><?php echo strtoupper(htmlspecialchars($pg['name'])); ?></h1>
                                <p class="text-muted fw-bold"><i class="fa fa-map-marker me-1"></i><?php echo strtoupper(htmlspecialchars($pg['address'])); ?>, <?php echo strtoupper(htmlspecialchars($pg['city'])); ?></p>
                            </div>
                            <span class="badge bg-brutal-black text-white px-3 py-2 fs-5 mt-2"><?php echo $pg['gender_type']; ?></span>
                        </div>
                        
                        <div class="row mb-5">
                            <div class="col-6">
                                <h2 class="text-brutal-red mb-0">₹<?php echo number_format($pg['rent']); ?> <small class="text-muted fs-6">/ MONTH</small></h2>
                            </div>
                            <div class="col-6 text-end">
                                <h4 class="mb-0">CAPACITY: <?php echo $pg['total_beds']; ?> BEDS</h4>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h3 class="border-bottom border-4 border-dark d-inline-block pb-1 mb-3">BRIEFING</h3>
                            <p class="fs-5"><?php echo nl2br(htmlspecialchars($pg['description'])); ?></p>
                        </div>
                        
                        <div class="mb-5">
                            <h3 class="border-bottom border-4 border-dark d-inline-block pb-1 mb-3">AMENITIES</h3>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach(explode(',', $pg['amenities']) as $amenity): ?>
                                    <span class="badge bg-white text-black border-brutal px-3 py-2"><?php echo strtoupper(trim($amenity)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <h3 class="border-bottom border-4 border-dark d-inline-block pb-1 mb-3">RULES OF ENGAGEMENT</h3>
                            <div class="brutal-card bg-brutal-white shadow-none mt-2">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($pg['rules'])); ?></p>
                            </div>
                        </div>

                        <?php if ($pg['latitude'] && $pg['longitude']): ?>
                            <div class="mb-4">
                                <h3 class="border-bottom border-4 border-dark d-inline-block pb-1 mb-3">GEOLOCATION</h3>
                                <div id="map"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-md-4">
                <div class="brutal-card sticky-top" style="top: 100px;">
                    <h2 class="text-center mb-4 border-bottom border-4 border-dark pb-2">SECURE ROOM</h2>
                    
                    <div class="text-center mb-4">
                        <?php
                        $stmtCount = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE pg_id = ? AND status = 'confirmed'");
                        $stmtCount->execute([$pg['id']]);
                        $occupied = $stmtCount->fetchColumn();
                        if ($occupied >= $pg['total_beds']): ?>
                            <div class="brutal-card bg-brutal-red text-white py-3">
                                <h3 class="mb-0">HOUSEFULL</h3>
                            </div>
                            <p class="mt-3 text-muted">ZERO OPERATIONAL CAPACITY REMAINING.</p>
                        <?php else: ?>
                            <div class="mb-4">
                                <h1 class="display-3 text-brutal-red mb-0">₹<?php echo number_format($pg['rent']); ?></h1>
                                <p class="text-muted fw-bold">ALL INCLUSIVE</p>
                            </div>
                            <a href="book_pg.php?id=<?php echo $pg['id']; ?>" class="btn btn-brutal w-100 fs-4 mb-3">BOOK NOW</a>
                            <p class="small fw-bold text-muted">VISUAL BED SELECTION NEXT.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="border-top border-4 border-dark pt-4 mt-4">
                        <h5 class="mb-3">PROPERTY COMMANDER</h5>
                        <div class="d-flex align-items-center">
                            <div class="bg-brutal-black text-white rounded-0 p-3 me-3">
                                <i class="fa fa-user-secret fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="mb-0"><?php echo strtoupper(htmlspecialchars($pg['owner_name'])); ?></h4>
                                <span class="badge bg-success text-white py-1">VERIFIED</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer_brutal.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php if ($pg['latitude'] && $pg['longitude']): ?>
    <script>
        var lat = <?php echo $pg['latitude']; ?>;
        var lng = <?php echo $pg['longitude']; ?>;
        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("<b><?php echo strtoupper(htmlspecialchars($pg['name'])); ?></b>").openPopup();
    </script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
