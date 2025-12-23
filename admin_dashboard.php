<?php
require_once 'auth.php';
require_once 'db.php';

// Check if user is admin
if (($_SESSION['username'] ?? '') !== 'admin') {
    header("Location: home.php");
    exit();
}

// Handle Cancel Request (Archive)
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $regId = $_GET['id'];
    try {
        $pdo->prepare("UPDATE REGISTRATION SET status = 'Cancelled' WHERE reg_id = ?")->execute([$regId]);
        echo "<script>window.location.href='admin_dashboard.php';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Update Failed: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Fetch Stats
$totalRunners = $pdo->query("SELECT COUNT(*) FROM RUNNER")->fetchColumn();
$paidRunners = $pdo->query("SELECT COUNT(*) FROM REGISTRATION WHERE status = 'Paid'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM PAYMENT WHERE status = 'Success'")->fetchColumn() ?: 0;
$pendingAmount = $pdo->query("SELECT SUM(total_amount) FROM PAYMENT WHERE status = 'Failed'")->fetchColumn() ?: 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff4d4d; --secondary: #2b2d42; --bg: #f8f9fa; --card-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg); color: var(--secondary); }
        .navbar { background: var(--secondary); padding: 1.5rem 0; }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: white !important; }
        .navbar-brand span { color: var(--primary); }
        .stat-card { background: white; border-radius: 20px; padding: 1.5rem; border: none; box-shadow: var(--card-shadow); transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
        .bg-primary-soft { background: rgba(255, 77, 77, 0.1); color: var(--primary); }
        .bg-success-soft { background: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-warning-soft { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-info-soft { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
        .stat-value { font-weight: 800; font-size: 1.8rem; margin-bottom: 0.2rem; }
        .table-card { background: white; border-radius: 20px; padding: 2rem; box-shadow: var(--card-shadow); border: none; }
        .table thead th { background: #fdfdfd; color: #6c757d; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; padding: 1rem; border-bottom: 2px solid #f8f9fa; }
        .table tbody td { padding: 1.2rem 1rem; vertical-align: middle; border-bottom: 1px solid #f8f9fa; }
        .badge-custom { padding: 0.5rem 1rem; border-radius: 30px; font-weight: 700; font-size: 0.75rem; }
        .btn-action { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: all 0.2s; margin: 0 2px; }
        .btn-edit { background: #fff3cd; color: #856404; }
        .btn-cancel { background: #f8d7da; color: #721c24; }
        .search-input { border-radius: 15px; padding: 0.7rem 1rem 0.7rem 2.8rem; border: 2px solid #eee; background: #fafafa; transition: all 0.3s; }
        .search-input:focus { border-color: var(--primary); box-shadow: none; background: white; }
        .search-icon { position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: #adb5bd; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><span>CBM</span> RUN 2026 Admin</a>
        <div class="d-flex align-items-center">
            <span class="text-white opacity-75 me-3">Logged in as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <!-- Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="fw-800">Admin Dashboard</h1>
            <p class="text-muted mb-0">ยินดีต้อนรับสู่ระบบจัดการข้อมูลการแข่งขัน</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="home.php" class="btn btn-outline-dark rounded-pill me-2"><i class="fas fa-home me-2"></i>หน้าเว็บไซต์</a>
            <a href="form_runner.php" class="btn btn-danger rounded-pill px-4"><i class="fas fa-plus me-2"></i>ลงทะเบียนใหม่</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info-soft"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= number_format($totalRunners) ?></div>
                <div class="stat-label">จำนวนนักวิ่งทั้งหมด (Total)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-soft"><i class="fas fa-user-check"></i></div>
                <div class="stat-value"><?= number_format($paidRunners) ?></div>
                <div class="stat-label">ชำระเงินแล้ว (Paid)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value">฿<?= number_format($totalRevenue) ?></div>
                <div class="stat-label">รายได้รวม (Revenue)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning-soft"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-value">฿<?= number_format($pendingAmount) ?></div>
                <div class="stat-label">รอชำระเงิน (Pending)</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <h4 class="fw-800 mb-3 mb-md-0">รายการลงทะเบียน (Registration List)</h4>
            <form method="GET" class="position-relative w-100" style="max-width: 400px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="ค้นหาชื่อ, BIB, หรือ ประเภทวิ่ง..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>

        <?php
        try {
            $search = $_GET['search'] ?? '';
            $view = $_GET['view'] ?? 'active'; // Default to active view
            $params = [];
            
            $sql = "SELECT 
                        r.reg_id,
                        CONCAT(run.first_name, ' ', run.last_name) AS runner_name,
                        rc.name AS category_name,
                        r.bib_number,
                        p.total_amount,
                        r.status AS reg_status
                    FROM REGISTRATION r
                    JOIN RUNNER run ON r.runner_id = run.runner_id
                    JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id
                    LEFT JOIN PAYMENT p ON r.reg_id = p.reg_id
                    WHERE 1=1 ";
            
            // View Filter
            if ($view == 'cancelled') {
                 $sql .= " AND r.status = 'Cancelled' ";
            } else {
                 $sql .= " AND r.status != 'Cancelled' ";
            }

            if (!empty($search)) {
                $sql .= " AND (run.first_name LIKE ? OR run.last_name LIKE ? OR r.bib_number LIKE ? OR rc.name LIKE ?) ";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY r.reg_id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tabs for View Selection
            ?>
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $view == 'active' ? 'active bg-danger' : 'text-secondary' ?>" href="admin_dashboard.php?view=active">
                        <i class="fas fa-list me-2"></i>รายการปกติ (Active)
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link <?= $view == 'cancelled' ? 'active bg-secondary' : 'text-secondary' ?>" href="admin_dashboard.php?view=cancelled">
                        <i class="fas fa-archive me-2"></i>รายการที่ยกเลิก (Cancelled)
                    </a>
                </li>
            </ul>

            <?php if (count($registrations) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>ชื่อ-นามสกุล (Name)</th>
                                <th>ประเภท (Category)</th>
                                <th>BIB</th>
                                <th class="text-end">ยอดเงิน</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $row): 
                                $statusClass = '';
                                switch ($row['reg_status']) {
                                    case 'Paid': $statusClass = 'bg-success'; break;
                                    case 'Pending': $statusClass = 'bg-warning text-dark'; break;
                                    case 'Cancelled': $statusClass = 'bg-danger'; break;
                                    default: $statusClass = 'bg-secondary';
                                }
                                ?>
                                <tr>
                                    <td><span class="text-muted fw-bold">#<?= $row['reg_id'] ?></span></td>
                                    <td><div class="fw-bold text-dark"><?= htmlspecialchars($row['runner_name']) ?></div></td>
                                    <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($row['category_name']) ?></span></td>
                                    <td><span class="fw-bold text-primary"><?= htmlspecialchars($row['bib_number'] ?: 'รอชำระ') ?></span></td>
                                    <td class="text-end fw-bold text-dark">฿<?= number_format($row['total_amount'] ?? 0, 2) ?></td>
                                    <td class="text-center"><span class="badge badge-custom <?= $statusClass ?>"><?= $row['reg_status'] ?></span></td>
                                    <td class="text-center">
                                        <a href="form_runner.php?reg_id=<?= $row['reg_id'] ?>" class="btn-action btn-edit" title="แก้ไข"><i class="fas fa-edit"></i></a>
                                        <?php if ($row['reg_status'] !== 'Cancelled'): ?>
                                            <a href="admin_dashboard.php?action=cancel&id=<?= $row['reg_id'] ?>" class="btn-action btn-cancel" title="ยกเลิก/Archive" onclick="return confirm('ยืนยันการเปลี่ยนสถานะเป็น Cancelled?')"><i class="fas fa-ban"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 opacity-50">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5>ไม่พบข้อมูลที่ค้นหา</h5>
                </div>
            <?php endif;
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
