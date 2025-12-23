<?php
require_once 'auth.php';
require_once 'db.php';

// Check if user is admin
if (($_SESSION['username'] ?? '') !== 'admin') {
    header("Location: home.php");
    exit();
}

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $regId = $_GET['id'];
    try {
        $pdo->beginTransaction();
        
        // Get Runner ID first
        $stmtR = $pdo->prepare("SELECT runner_id FROM REGISTRATION WHERE reg_id = ?");
        $stmtR->execute([$regId]);
        $runnerId = $stmtR->fetchColumn();

        // Delete Payment
        $pdo->prepare("DELETE FROM PAYMENT WHERE reg_id = ?")->execute([$regId]);
        // Delete Registration
        $pdo->prepare("DELETE FROM REGISTRATION WHERE reg_id = ?")->execute([$regId]);
        
        // Delete Runner if they exist
        if ($runnerId) {
            $pdo->prepare("DELETE FROM RUNNER WHERE runner_id = ?")->execute([$runnerId]);
        }
        
        $pdo->commit();
        echo "<script>alert('Deleted Successfully!'); window.location.href='admin_dashboard.php';</script>";
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "<script>alert('Delete Failed: " . addslashes($e->getMessage()) . "');</script>";
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4d4d;
            --secondary: #2b2d42;
            --accent: #ef233c;
            --bg: #f8f9fa;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--secondary);
        }

        /* Sidebar & Navbar */
        .navbar {
            background: var(--secondary);
            padding: 1.5rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-brand span {
            color: var(--primary);
        }

        /* Content Area */
        .content-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .page-title {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: none;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .bg-primary-soft { background: rgba(255, 77, 77, 0.1); color: var(--primary); }
        .bg-success-soft { background: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-warning-soft { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-info-soft { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }

        .stat-value {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Table Container */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: none;
        }

        .table thead th {
            background: #fdfdfd;
            color: #6c757d;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .table tbody td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f8f9fa;
        }

        .runner-name {
            font-weight: 700;
            color: var(--secondary);
        }

        .category-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Badges */
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
            margin: 0 2px;
        }

        .btn-edit { background: #fff3cd; color: #856404; }
        .btn-edit:hover { background: #ffe69c; }
        .btn-delete { background: #f8d7da; color: #721c24; }
        .btn-delete:hover { background: #f1b0b7; }

        /* Search Bar */
        .search-container {
            position: relative;
            max-width: 400px;
        }

        .search-input {
            border-radius: 15px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            border: 2px solid #eee;
            background: #fafafa;
            transition: all 0.3s;
        }

        .search-input:focus {
            box-shadow: none;
            border-color: var(--primary);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }
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

<div class="content-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Admin Dashboard</h1>
                <p class="text-muted mb-0">ยินดีต้อนรับสู่ระบบจัดการข้อมูลการแข่งขัน</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="home.php" class="btn btn-outline-dark rounded-pill me-2"><i class="fas fa-home me-2"></i>View Site</a>
                <a href="form_runner.php" class="btn btn-danger rounded-pill px-4"><i class="fas fa-plus me-2"></i>Add New Runner</a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Statistics Section -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info-soft"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= number_format($totalRunners) ?></div>
                <div class="stat-label">Total Runners</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-soft"><i class="fas fa-user-check"></i></div>
                <div class="stat-value"><?= number_format($paidRunners) ?></div>
                <div class="stat-label">Paid Registrations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value">฿<?= number_format($totalRevenue) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning-soft"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-value">฿<?= number_format($pendingAmount) ?></div>
                <div class="stat-label">Pending Amount</div>
            </div>
        </div>
    </div>

    <!-- Registration List Section -->
    <div class="table-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <h4 class="fw-800 mb-3 mb-md-0">Registration List</h4>
            
            <form method="GET" class="search-container w-100">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Search by name, BIB, or ID..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>

        <?php
        try {
            $search = $_GET['search'] ?? '';
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
            
            if (!empty($search)) {
                $sql .= " AND (run.first_name LIKE ? OR run.last_name LIKE ? OR r.bib_number LIKE ? OR r.reg_id LIKE ?) ";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "$search";
            }
            
            $sql .= " ORDER BY r.reg_id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($registrations) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Runner Name</th>
                                <th>Category</th>
                                <th>BIB</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
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
                                    <td>
                                        <div class="runner-name"><?= htmlspecialchars($row['runner_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="category-label"><?= htmlspecialchars($row['category_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['bib_number'] ?? '-') ?></div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-dark">฿<?= $row['total_amount'] ? number_format($row['total_amount'], 2) : '0.00' ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-custom <?= $statusClass ?>"><?= $row['reg_status'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="form_runner.php?reg_id=<?= $row['reg_id'] ?>" class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit fa-sm"></i>
                                        </a>
                                        <a href="admin_dashboard.php?action=delete&id=<?= $row['reg_id'] ?>" class="btn-action btn-delete" title="Delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?')">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/no-data-found-8867280-7247567.png" alt="No data" style="max-width: 200px;" class="mb-3">
                    <h5 class="text-muted">ไม่พบข้อมูลผู้สมัครที่ค้นหา</h5>
                    <a href="admin_dashboard.php" class="btn btn-link text-danger text-decoration-none">Clear search</a>
                </div>
            <?php endif;

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger rounded-4">Error fetching data: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>