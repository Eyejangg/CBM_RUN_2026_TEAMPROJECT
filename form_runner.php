<?php
require_once 'auth.php';
require_once 'db.php';

// Auto-fill defaults from Admin Session for new entries
$defaultFirstName = $_SESSION['first_name'] ?? '';
$defaultLastName = $_SESSION['last_name'] ?? '';

$runner = [
    'reg_id' => '', 
    'first_name' => $defaultFirstName, 
    'last_name' => $defaultLastName, 
    'date_of_birth' => '',
    'gender' => 'Male', 'citizen_id' => '', 'phone' => '', 'email' => '', 'address' => '',
    'category_id' => '', 'pay_status' => 'Pending', 'shipping_id' => '', 'status' => 'Pending', 'bib_number' => ''
];
$isEdit = false;

// Fetch Categories and Shipping Options
// We need Price rates to be available for JS.
// Simple approach: Get ONE standard price per category for the JS estimate.
$categories = $pdo->query("
    SELECT c.*, p.amount as standard_price 
    FROM RACE_CATEGORY c 
    LEFT JOIN PRICE_RATE p ON c.category_id = p.category_id AND p.runner_type = 'Standard'
")->fetchAll(PDO::FETCH_ASSOC);

$shippings = $pdo->query("SELECT * FROM SHIPPING_OPTION")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['reg_id'])) {
    $isEdit = true;
    $reg_id = $_GET['reg_id'];
    
    // Join tables to get all data
    $sql = "SELECT r.*, run.*, p.status as pay_status 
            FROM REGISTRATION r
            JOIN RUNNER run ON r.runner_id = run.runner_id
            LEFT JOIN PAYMENT p ON r.reg_id = p.reg_id
            WHERE r.reg_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reg_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        $runner = $data;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit Runner' : 'Add New Runner' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4><?= $isEdit ? 'Edit Runner' : 'Add New Runner' ?></h4>
            </div>
            <div class="card-body">
                <form action="save_runner.php" method="POST">
                    <input type="hidden" name="reg_id" value="<?= htmlspecialchars($runner['reg_id']) ?>">
                    <input type="hidden" name="runner_id" value="<?= htmlspecialchars($runner['runner_id'] ?? '') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($runner['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($runner['last_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($runner['date_of_birth']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male" <?= $runner['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $runner['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Citizen ID</label>
                            <input type="text" name="citizen_id" class="form-control" value="<?= htmlspecialchars($runner['citizen_id']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($runner['phone']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($runner['email']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($runner['address']) ?></textarea>
                    </div>

                    <hr>
                    <h5>Registration Details</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Race Category</label>
                            <select name="category_id" id="category_id" class="form-select" required onchange="calculateTotal()">
                                <option value="" data-price="0">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" 
                                            data-price="<?= $cat['standard_price'] ?>"
                                            <?= $runner['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?> (<?= $cat['distance_km'] ?> km) - <?= number_format($cat['standard_price']) ?> THB
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Shipping Option</label>
                            <select name="shipping_id" id="shipping_id" class="form-select" required onchange="calculateTotal()">
                                <?php foreach ($shippings as $ship): ?>
                                    <option value="<?= $ship['shipping_id'] ?>" 
                                            data-cost="<?= $ship['cost'] ?>"
                                            <?= $runner['shipping_id'] == $ship['shipping_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ship['type']) ?> (+<?= number_format($ship['cost'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                             <label class="form-label">Estimated Total Payment</label>
                             <input type="text" id="total_amount" class="form-control fw-bold text-success" readonly value="0.00 THB">
                        </div>
                    </div>
                    
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label class="form-label">Registration Status</label>
                            <select name="status" class="form-select">
                                <option value="Pending" <?= $runner['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Paid" <?= $runner['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="Cancelled" <?= $runner['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label class="form-label">BIB Number (Optional)</label>
                            <input type="text" name="bib_number" class="form-control" value="<?= htmlspecialchars($runner['bib_number']) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Runner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function calculateTotal() {
            const catSelect = document.getElementById('category_id');
            const shipSelect = document.getElementById('shipping_id');
            
            const price = parseFloat(catSelect.options[catSelect.selectedIndex].dataset.price || 0);
            const shipping = parseFloat(shipSelect.options[shipSelect.selectedIndex].dataset.cost || 0);
            
            const total = price + shipping;
            document.getElementById('total_amount').value = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' THB';
        }
        
        // Run on load
        window.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
</body>
</html>