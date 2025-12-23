<?php
require_once 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$first_name = $_SESSION['first_name'] ?? '';

// Fetch Categories with their min amount from price_rate
try {
    $stmt = $pdo->prepare("
        SELECT rc.*, MIN(pr.amount) as min_price 
        FROM RACE_CATEGORY rc 
        JOIN PRICE_RATE pr ON rc.category_id = pr.category_id 
        GROUP BY rc.category_id
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBM RUN 2026 | Let's Run Together</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff4d4d;
            --secondary-color: #2b2d42;
            --accent-color: #ef233c;
            --light-bg: #edf2f4;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.8);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light-bg);
            color: var(--secondary-color);
            overflow-x: hidden;
        }

        /* Navbar Styling */
        .navbar {
            background: var(--white);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--secondary-color) !important;
        }

        .navbar-brand span {
            color: var(--primary-color);
        }

        .nav-link {
            font-weight: 600;
            color: var(--secondary-color) !important;
            margin: 0 10px;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-link.active::after, .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(43, 45, 66, 0.9), rgba(239, 35, 60, 0.8)), url('https://images.unsplash.com/photo-1530549387074-d61f7d54944d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            color: var(--white);
            margin-bottom: 50px;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .hero-content p {
            font-size: 1.2rem;
            font-weight: 300;
            max-width: 600px;
        }

        /* Category Cards */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-weight: 800;
            font-size: 2.5rem;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .race-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: var(--white);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            height: 100%;
        }

        .race-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .race-card-img {
            height: 200px;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            position: relative;
        }

        .distance-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--primary-color);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .race-card-body {
            padding: 30px;
        }

        .race-card-body h3 {
            font-weight: 800;
            margin-bottom: 10px;
        }

        .race-card-body p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent-color);
        }

        .price-tag span {
            font-size: 0.9rem;
            color: #adb5bd;
            font-weight: 400;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 30px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: var(--secondary-color);
            color: var(--white);
            border: none;
            padding: 25px 30px;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .modal-body {
            padding: 40px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: rgba(239, 35, 60, 0.1);
            color: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1rem;
        }

        .btn-apply {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 15px 40px;
            border-radius: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
            display: block;
            text-align: center;
            text-decoration: none;
        }

        .btn-apply:hover {
            background: #d90429;
            transform: scale(1.02);
            color: var(--white);
        }

        /* Footer */
        footer {
            background: var(--secondary-color);
            color: var(--white);
            padding: 50px 0;
            margin-top: 100px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><span>CBM</span> RUN 2026</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" href="home.php"><i class="fas fa-home me-1"></i> หน้าแรก</a>
                </li>
                <?php if ($user_id): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="my_registrations.php"><i class="fas fa-history me-1"></i> ประวัติคำสั่งซื้อ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_registrations.php"><i class="fas fa-running me-1"></i> ตรวจสอบผลการวิ่ง</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle btn btn-outline-dark rounded-pill px-4" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($first_name) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-4">
                            <?php if ($_SESSION['username'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2 text-danger"></i> ออกจากระบบ</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-danger rounded-pill px-4" href="register.php">สมัครสมาชิก</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-dark rounded-pill px-4" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="animate__animated animate__fadeInUp">Let's Run <br>Beyond Limits</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">สัมผัสประสบการณ์การวิ่งครั้งยิ่งใหญ่ในปี 2026 พร้อมเส้นทางที่สวยงามและกิจกรรมมากมาย มาร่วมสร้างประวัติศาสตร์ไปด้วยกัน</p>
            <a href="#categories" class="btn btn-danger btn-lg rounded-pill px-5 py-3 mt-4 animate__animated animate__fadeInUp animate__delay-2s">สมัครเลยตอนนี้</a>
        </div>
    </div>
</section>

<!-- Race Categories -->
<section id="categories" class="container">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'applied'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-5" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>ลงทะเบียนสำเร็จ!</strong> กรุณารอรับอีเมลเพื่อยืนยันการชำระเงิน
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="section-title">
        <h2>เลือกรายการวิ่งที่คุณสนใจ</h2>
        <p class="text-muted">มีหลากหลายระยะทางให้คุณเลือกสรรตามความเหมาะสม</p>
    </div>

    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
            <div class="col-md-4">
                <div class="race-card" data-bs-toggle="modal" data-bs-target="#modal-<?= $cat['category_id'] ?>">
                    <div class="race-card-img">
                        <i class="fas fa-running"></i>
                        <span class="distance-badge"><?= $cat['distance_km'] ?> km</span>
                    </div>
                    <div class="race-card-body">
                        <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        <p>ท้าทายขีดจำกัดของคุณด้วยรายการ <?= htmlspecialchars($cat['name']) ?> เส้นทางมาตรฐานสากล</p>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="price-tag">
                                <span>เริ่มต้นที่</span><br>
                                ฿<?= number_format($cat['min_price'], 0) ?>
                            </div>
                            <button class="btn btn-outline-danger rounded-circle"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-<?= $cat['category_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-bold"><?= htmlspecialchars($cat['name']) ?> Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-road"></i></div>
                                <div>
                                    <small class="text-muted d-block">ระยะทาง</small>
                                    <strong><?= $cat['distance_km'] ?> กิโลเมตร</strong>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                                <div>
                                    <small class="text-muted d-block">เวลาปล่อยตัว</small>
                                    <strong><?= date('H:i', strtotime($cat['start_time'])) ?> น.</strong>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-hourglass-half"></i></div>
                                <div>
                                    <small class="text-muted d-block">เวลาจำกัด (Cut-off)</small>
                                    <strong><?= date('H', strtotime($cat['time_limit'])) ?> ชั่วโมง</strong>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-gift"></i></div>
                                <div>
                                    <small class="text-muted d-block">ของที่ระลึก</small>
                                    <strong><?= htmlspecialchars($cat['giveaway_type'] ?? 'เสื้อยืดแขนสั้น + เหรียญรางวัล') ?></strong>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <h6 class="font-weight-bold mb-3">อัตราค่าสมัคร</h6>
                            <?php
                            $stmtP = $pdo->prepare("SELECT * FROM PRICE_RATE WHERE category_id = ?");
                            $stmtP->execute([$cat['category_id']]);
                            $prices = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($prices as $p):
                            ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= htmlspecialchars($p['runner_type']) ?></span>
                                    <span class="text-danger font-weight-bold">฿<?= number_format($p['amount'], 0) ?></span>
                                </div>
                            <?php endforeach; ?>

                            <a href="apply_race.php?category=<?= $cat['category_id'] ?>" class="btn btn-apply">สมัครรายการนี้</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container text-center">
        <h3>CBM RUN 2026</h3>
        <p class="text-muted mb-4">Run with Heart, Finish with Pride.</p>
        <div class="social-links mb-4">
            <a href="#" class="btn btn-outline-light btn-sm rounded-circle mx-1"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn btn-outline-light btn-sm rounded-circle mx-1"><i class="fab fa-instagram"></i></a>
            <a href="#" class="btn btn-outline-light btn-sm rounded-circle mx-1"><i class="fab fa-line"></i></a>
        </div>
        <hr class="mt-5 mb-4 border-secondary">
        <small class="text-muted">&copy; 2025 CBM RUN 2026 TEAM. All rights reserved.</small>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
