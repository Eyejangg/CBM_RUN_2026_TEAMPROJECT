<?php
require_once 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$first_name = $_SESSION['first_name'] ?? '';

// Fetch Age Groups grouped by Category
try {
    $stmt = $pdo->query("
        SELECT 
            rc.name as category_name, 
            rc.distance_km,
            ag.gender,
            ag.min_age,
            ag.max_age
        FROM AGE_GROUP ag
        JOIN RACE_CATEGORY rc ON ag.category_id = rc.category_id
        ORDER BY rc.distance_km DESC, ag.gender DESC, ag.min_age ASC
    ");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by Category for easier display
    $displayData = [];
    foreach ($groups as $g) {
        $displayData[$g['category_name']][] = $g;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBM RUN 2026 | Age Groups</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         :root { --primary: #ff4d4d; --secondary: #2b2d42; }
        body { font-family: 'Outfit', sans-serif; background-color: #edf2f4; color: var(--secondary); }
        .hero-mini { background: linear-gradient(135deg, var(--secondary), #1a1b29); color: white; padding: 60px 0; text-align: center; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; overflow: hidden; }
        .card-header-custom { background: var(--primary); color: white; padding: 15px 25px; font-weight: 700; font-size: 1.2rem; }
        .table-custom th { background-color: #f8f9fa; color: #666; font-weight: 600; }
    </style>
</head>
<body>

<!-- Navbar (Simplified) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="home.php"><span class="text-danger">CBM</span> RUN 2026</a>
        <a href="home.php" class="btn btn-outline-light btn-sm rounded-pill px-4">กลับหน้าหลัก</a>
    </div>
</nav>

<header class="hero-mini">
    <div class="container">
        <h1 class="fw-800">การแบ่งกลุ่มอายุ (Age Groups)</h1>
        <p class="opacity-75">ตรวจสอบรุ่นอายุของคุณเพื่อเตรียมความพร้อมสู่ชัยชนะ</p>
    </div>
</header>

<div class="container py-5">
    <?php if (empty($displayData)): ?>
        <div class="alert alert-warning text-center">ยังไม่มีข้อมูลกลุ่มอายุในระบบ</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($displayData as $catName => $items): ?>
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($catName) ?></span>
                            <span class="badge bg-white text-danger rounded-pill"><?= $items[0]['distance_km'] ?> KM</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">เพศ (Gender)</th>
                                        <th>ช่วงอายุ (Age Range)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">
                                                <?php if($item['gender'] == 'Male'): ?>
                                                    <i class="fas fa-mars text-primary me-2"></i> ชาย (Male)
                                                <?php else: ?>
                                                    <i class="fas fa-venus text-danger me-2"></i> หญิง (Female)
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item['min_age'] ?> - <?= $item['max_age'] >= 90 ? 'ปีขึ้นไป' : $item['max_age'] . ' ปี' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
