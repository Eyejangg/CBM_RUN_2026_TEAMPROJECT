<?php
require_once 'auth.php';
require_once 'db.php';

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
        echo "<script>alert('Deleted Successfully!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "<script>alert('Delete Failed: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runner Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 30px; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏃‍♂️ CBM Run 2026 Admin</a>
    <div class="d-flex text-white align-items-center">
        <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">

    <?php
    // Saved Message
    if (isset($_GET['msg']) && $_GET['msg'] == 'saved') {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                Runner Saved Successfully!
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
    ?>

    <div class="table-container">
        
        <div class="row mb-3">
            <div class="col-md-6">
                 <h2>Registration List</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="form_runner.php" class="btn btn-primary">+ Add New Runner</a>
            </div>
        </div>
        
        <!-- Search Bar -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="Search Name or BIB..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Search</button>
            </div>
             <div class="col-auto">
                <a href="index.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

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
                $sql .= " AND (run.first_name LIKE ? OR run.last_name LIKE ? OR r.bib_number LIKE ?) ";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY r.reg_id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($registrations) > 0) {
                echo '<table class="table table-hover table-bordered table-striped">';
                echo '<thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Runner Name</th>
                            <th>Category</th>
                            <th>BIB</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                      </thead>';
                echo '<tbody>';

                foreach ($registrations as $row) {
                    $statusBadge = '';
                    switch ($row['reg_status']) {
                        case 'Paid': $statusBadge = '<span class="badge bg-success">Paid</span>'; break;
                        case 'Pending': $statusBadge = '<span class="badge bg-warning text-dark">Pending</span>'; break;
                        case 'Cancelled': $statusBadge = '<span class="badge bg-danger">Cancelled</span>'; break;
                        default: $statusBadge = '<span class="badge bg-secondary">'.$row['reg_status'].'</span>';
                    }
                    
                    $amount = $row['total_amount'] ? number_format($row['total_amount'], 2) : '-';

                    echo "<tr>
                            <td>{$row['reg_id']}</td>
                            <td>" . htmlspecialchars($row['runner_name']) . "</td>
                            <td>" . htmlspecialchars($row['category_name']) . "</td>
                            <td>" . htmlspecialchars($row['bib_number'] ?? '-') . "</td>
                            <td class='text-end'>{$amount}</td>
                            <td class='text-center'>{$statusBadge}</td>
                            <td class='text-center'>
                                <a href='form_runner.php?reg_id={$row['reg_id']}' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='index.php?action=delete&id={$row['reg_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete?\")'>Delete</a>
                            </td>
                          </tr>";
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<div class="alert alert-info text-center">No registrations found.</div>';
            }

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error fetching data: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>