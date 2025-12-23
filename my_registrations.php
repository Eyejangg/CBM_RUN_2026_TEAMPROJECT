<?php
session_start();
require_once 'db.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch registrations for this user
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.reg_id,
            rc.name as category_name,
            rc.distance_km,
            r.bib_number,
            r.reg_date,
            r.status as reg_status,
            p.total_amount,
            p.status as pay_status,
            run.email
        FROM REGISTRATION r
        JOIN RUNNER run ON r.runner_id = run.runner_id
        JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id
        LEFT JOIN PAYMENT p ON r.reg_id = p.reg_id
        WHERE run.user_id = ?
        ORDER BY r.reg_id DESC
    ");
    $stmt->execute([$user_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $registrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff4d4d; --secondary: #2b2d42; }
        body { font-family: 'Outfit', sans-serif; background-color: #f8f9fa; color: var(--secondary); }
        .navbar { background: var(--secondary); padding: 15px 0; }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: white !important; }
        .navbar-brand span { color: var(--primary); }
        .header-section { background: var(--secondary); color: white; padding: 60px 0; margin-bottom: 40px; }
        .reg-card { background: white; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: none; overflow: hidden; margin-bottom: 20px; transition: 0.3s; }
        .reg-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .status-badge { padding: 5px 15px; border-radius: 30px; font-weight: 600; font-size: 0.85rem; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .empty-state { text-align: center; padding: 100px 0; }
        .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><span>CBM</span> RUN 2026</a>
        <div class="text-white">
            <a href="home.php" class="btn btn-outline-light btn-sm rounded-pill px-3">กลับหน้าหลัก</a>
        </div>
    </div>
</nav>

<div class="header-section">
    <div class="container">
        <h1 class="fw-800">ตรวจสอบผลการสมัคร</h1>
        <p class="opacity-75">รายชื่อรายการวิ่งที่ท่านได้ลงทะเบียนไว้</p>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'paid'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <strong>ชำระเงินสำเร็จ!</strong> สถานะการสมัครของท่านถูกอัพเดทเรียบร้อยแล้วครับ
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (empty($registrations)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>ไม่พบข้อมูลการสมัคร</h3>
            <p class="text-muted">ท่านยังไม่ได้ลงทะเบียนรายการวิ่งใดๆ</p>
            <a href="home.php#categories" class="btn btn-danger rounded-pill px-4 py-2 mt-3">สมัครวิ่งเลย</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($registrations as $reg): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="reg-card">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="fw-800 mb-1"><?= htmlspecialchars($reg['category_name']) ?></h4>
                                    <span class="text-muted"><?= $reg['distance_km'] ?> KM</span>
                                </div>
                                <?php
                                $statusClass = '';
                                $statusText = $reg['reg_status'];
                                switch($reg['reg_status']) {
                                    case 'Pending': $statusClass = 'status-pending'; break;
                                    case 'Paid': $statusClass = 'status-paid'; break;
                                    case 'Cancelled': $statusClass = 'status-cancelled'; break;
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">BIB Number</small>
                                    <strong class="text-danger"><?= $reg['bib_number'] ?: '-' ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">วันที่สมัคร</small>
                                    <strong><?= date('d M Y', strtotime($reg['reg_date'])) ?></strong>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">อีเมลที่ใช้สมัคร</small>
                                    <strong><?= htmlspecialchars($reg['email']) ?></strong>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">ยอดเงินรวม</small>
                                    <strong class="fs-5">฿<?= number_format($reg['total_amount'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php if ($reg['reg_status'] == 'Pending'): ?>
                            <div class="bg-light p-3 text-center border-top">
                                <a href="payment.php?reg_id=<?= $reg['reg_id'] ?>" class="btn btn-primary btn-sm rounded-pill w-100">ไปที่หน้าชำระเงิน</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
