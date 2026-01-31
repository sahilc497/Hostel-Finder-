<?php
session_start();
require_once 'config/database.php';

try {
    // Fetch Top 3 PGs for Featured Section
    $stmt = $conn->query("SELECT * FROM pg_listings 
        WHERE status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT 3");
    $featured_pgs = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_pgs = [];
    $error_msg = "INTEL DATABASE OFFLINE.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>STUDENTNEST - MISSION CONTROL</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/brutalism.css">
    <style>
        .hero-video-frame {
            position: relative;
            height: 80vh;
            width: 100%;
            overflow: hidden;
            border: 10px solid var(--brutal-black);
            box-shadow: 15px 15px 0px var(--brutal-black);
            margin-top: 20px;
        }
        .hero-video-frame video {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            filter: grayscale(1);
        }
        .hero-overlay-brutal {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 229, 0, 0.4);
            mix-blend-mode: multiply;
        }
        .hero-text-block {
            position: absolute;
            bottom: 40px;
            left: 40px;
            background: rgba(17, 17, 17, 0.85); /* Semi-transparent black */
            color: var(--brutal-yellow);
            padding: 15px 30px;
            border: 4px solid var(--brutal-white);
            max-width: 600px;
            backdrop-filter: blur(5px); /* Modern touch to improve readability */
        }
        .hero-text-block h1 { font-size: 3.5rem !important; }
        .hero-text-block h3 { font-size: 1.5rem !important; }
        .hero-text-block p { font-size: 1.1rem !important; }
    </style>
</head>
<body>

    <?php include 'includes/header_brutal.php'; ?>

    <div class="container brutal-container">
        <!-- HERO -->
        <div class="hero-video-frame">
            <video autoplay muted loop playsinline>
                <source src="assets/images/video2.mp4" type="video/mp4">
            </video>
            <div class="hero-overlay-brutal"></div>
            <div class="hero-text-block">
                <h1 class="mb-0">STUDENTNEST</h1>
                <h3>ALPHA SECTOR: DYPCET</h3>
                <p class="fw-bold mt-2">DECODE THE HOUSING MARKET. SECURE YOUR BASE.</p>
                <div class="mt-3">
                    <a href="user/dashboard.php" class="btn btn-brutal bg-brutal-yellow text-black px-4">INITIALIZE SEARCH</a>
                </div>
            </div>
        </div>

        <?php if (isset($error_msg)): ?>
            <div class="brutal-card bg-brutal-red text-white mt-5">
                <h2 class="mb-0">ERROR: <?php echo $error_msg; ?></h2>
            </div>
        <?php endif; ?>

        <!-- ABOUT -->
        <div class="row g-5 mt-5 align-items-center" id="about">
            <div class="col-md-7">
                <h2 class="display-3 border-bottom border-5 border-dark pb-2 mb-4">BRIEFING</h2>
                <p class="fs-4 fw-bold">WE ELIMINATE THE FRICTION IN STUDENT LIVING.</p>
                <p class="fs-5">STUDENTNEST CONNECTS OPERATORS (OWNERS) WITH TARGETS (STUDENTS) THROUGH A VERIFIED INTEL NETWORK. NO MIDDLEMEN. NO TRAPS.</p>
                
                <div class="row g-4 mt-2">
                    <div class="col-6">
                        <div class="brutal-card p-3 mb-0 bg-white">
                            <h4 class="mb-0"><i class="fa-solid fa-check-double me-2"></i>ZERO HIDDEN FEES</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="brutal-card p-3 mb-0 bg-white">
                            <h4 class="mb-0"><i class="fa-solid fa-lock me-2"></i>SECURE DEPOSITS</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="brutal-card p-3 mb-0 bg-white">
                            <h4 class="mb-0"><i class="fa-solid fa-bolt me-2"></i>INSTANT ACCESS</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="brutal-card p-3 mb-0 bg-white">
                            <h4 class="mb-0"><i class="fa-solid fa-eye me-2"></i>100% VERIFIED</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="brutal-card p-0 overflow-hidden" style="transform: rotate(2deg);">
                    <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80" class="img-fluid w-100">
                </div>
            </div>
        </div>

        <!-- OPERATIONS (Services) -->
        <div class="mt-5 pt-5 text-center">
            <h2 class="display-3 border-bottom border-5 border-dark d-inline-block pb-1 mb-5">OPERATIONS</h2>
            <div class="row g-4 text-start">
                <div class="col-md-4">
                    <div class="brutal-card h-100">
                        <div class="bg-brutal-black text-white p-3 d-inline-block mb-3">
                            <i class="fa-solid fa-location-crosshairs fa-3x"></i>
                        </div>
                        <h3>PRIME SECTORS</h3>
                        <p class="fw-bold">PROPERTIES STRATEGICALLY LOCATED NEAR DYPCET HQ.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="brutal-card h-100">
                        <div class="bg-brutal-black text-white p-3 d-inline-block mb-3">
                            <i class="fa-solid fa-wifi fa-3x"></i>
                        </div>
                        <h3>TECH OPS</h3>
                        <p class="fw-bold">GIGABIT WIFI. CLIMATE CONTROL. POWER REDUNDANCY.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="brutal-card h-100">
                        <div class="bg-brutal-black text-white p-3 d-inline-block mb-3">
                            <i class="fa-solid fa-shield-halved fa-3x"></i>
                        </div>
                        <h3>DEFENSE</h3>
                        <p class="fw-bold">CCTV GRID. BIOMETRIC LOCKS. 24/7 COMMAND STAFF.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FEATURED TARGETS -->
        <div class="mt-5 pt-5" id="featured">
            <h2 class="display-3 border-bottom border-5 border-dark pb-1 mb-5">HOT TARGETS</h2>
            <div class="row">
                <?php if (empty($featured_pgs)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="brutal-card bg-brutal-white">
                            <h3>ZERO TARGETS DETECTED.</h3>
                            <p>WAITING FOR LISTING DEPLOYMENT.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_pgs as $pg): ?>
                        <div class="col-md-4 mb-5">
                            <div class="brutal-card p-0 overflow-hidden h-100">
                                <div style="height:250px; overflow:hidden; border-bottom: var(--brutal-border);">
                                    <img src="<?php echo !empty($pg['image']) ? $pg['image'] : 'https://via.placeholder.com/400x300?text=PROPERTY+LOG'; ?>" class="w-100 h-100 object-fit-cover" style="filter: contrast(1.2);">
                                </div>
                                <div class="p-4">
                                    <span class="badge bg-brutal-black text-warning mb-3">SECTOR: <?php echo strtoupper($pg['gender_type']); ?></span>
                                    <h2 class="mb-1"><?php echo strtoupper(htmlspecialchars($pg['name'])); ?></h2>
                                    <p class="fw-bold text-muted mb-4"><i class="fa-solid fa-map-pin me-2"></i> <?php echo strtoupper(htmlspecialchars($pg['city'])); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="text-brutal-red mb-0">₹<?php echo number_format($pg['rent']); ?>/MO</h3>
                                        <a href="user/pg_details.php?id=<?php echo $pg['id']; ?>" class="btn btn-brutal btn-sm">VIEW INTEL</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer_brutal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
