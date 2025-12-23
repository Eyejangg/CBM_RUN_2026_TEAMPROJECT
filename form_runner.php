<?php
require_once 'auth.php';
require_once 'db.php';

// Check if user is admin
if (($_SESSION['username'] ?? '') !== 'admin') {
    header("Location: home.php");
    exit();
}

// Generate Random Citizen ID (13 digits)
$randCitizenId = '';
for ($i = 0; $i < 13; $i++) {
    $randCitizenId .= mt_rand(0, 9);
}

// Generate Random BIB (4 digits)
$randBib = mt_rand(1001, 9999);

$runner = [
    'reg_id' => '', 
    'first_name' => '', 
    'last_name' => '', 
    'date_of_birth' => '',
    'gender' => 'Male', 
    'citizen_id' => $randCitizenId, 
    'phone' => '', 
    'email' => '', 
    'address' => '',
    'category_id' => '', 
    'pay_status' => 'Pending', 
    'shipping_id' => '', 
    'status' => 'Pending', 
    'bib_number' => $randBib
];
$isEdit = false;

// Fetch Categories and Shipping Options
$categories = $pdo->query("
    SELECT c.*, p.amount as standard_price 
    FROM RACE_CATEGORY c 
    LEFT JOIN PRICE_RATE p ON c.category_id = p.category_id AND p.runner_type = 'Standard'
")->fetchAll(PDO::FETCH_ASSOC);

$shippings = $pdo->query("SELECT * FROM SHIPPING_OPTION")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['reg_id'])) {
    $isEdit = true;
    $reg_id = $_GET['reg_id'];
    
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'แก้ไขข้อมูลนักวิ่ง' : 'เพิ่มนักวิ่งใหม่' ?> | CBM RUN 2026 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff4d4d; --secondary: #2b2d42; }
        body { font-family: 'Outfit', sans-serif; background-color: #f8f9fa; color: var(--secondary); padding: 40px 0; }
        .form-card { background: white; border-radius: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); border: none; overflow: hidden; max-width: 900px; margin: 0 auto; }
        .card-header { background: var(--secondary); color: white; padding: 40px; border: none; }
        .card-body { padding: 50px; }
        .form-label { font-weight: 700; font-size: 0.9rem; color: #495057; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 20px; border: 2px solid #f1f1f1; transition: 0.3s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: none; background: white; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 15px 40px; border-radius: 15px; font-weight: 700; transition: 0.3s; }
        .btn-save:hover { background: #d90429; transform: scale(1.02); color: white; }
        .btn-cancel { background: #eee; color: #666; border: none; padding: 15px 40px; border-radius: 15px; font-weight: 700; transition: 0.3s; text-decoration: none; }
        .btn-cancel:hover { background: #ddd; color: #333; }
        .section-title { font-weight: 800; border-left: 5px solid var(--primary); padding-left: 15px; margin: 40px 0 25px 0; }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="card-header text-center">
            <h2 class="fw-800 mb-0"><?= $isEdit ? 'แก้ไขข้อมูลนักวิ่ง' : 'เพิ่มนักวิ่งใหม่' ?></h2>
            <p class="opacity-75 mb-0 mt-2"><?= $isEdit ? "กำลังแก้ไขรายการสมัคร #".$runner['reg_id'] : "กรอกข้อมูลเพื่อลงทะเบียนนักวิ่งเข้าสู่ระบบ" ?></p>
        </div>
        <div class="card-body">
            <form action="save_runner.php" method="POST">
                <input type="hidden" name="reg_id" value="<?= htmlspecialchars($runner['reg_id']) ?>">
                <input type="hidden" name="runner_id" value="<?= htmlspecialchars($runner['runner_id'] ?? '') ?>">

                <h5 class="section-title">ข้อมูลส่วนตัว (Personal Info)</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อจริง (First Name)</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($runner['first_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">นามสกุล (Last Name)</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($runner['last_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">วันเดือนปีเกิด</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($runner['date_of_birth']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เพศ</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?= $runner['gender'] == 'Male' ? 'selected' : '' ?>>ชาย (Male)</option>
                            <option value="Female" <?= $runner['gender'] == 'Female' ? 'selected' : '' ?>>หญิง (Female)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เลขบัตรประชาชน</label>
                        <input type="text" name="citizen_id" class="form-control" value="<?= htmlspecialchars($runner['citizen_id']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($runner['phone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($runner['email']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">ที่อยู่จัดส่ง</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($runner['address']) ?></textarea>
                    </div>
                </div>

                <h5 class="section-title">ข้อมูลการสมัคร (Registration Info)</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">รายการวิ่ง (Race Category)</label>
                        <select name="category_id" id="category_id" class="form-select" required onchange="calculateTotal()">
                            <option value="" data-price="0">-- เลือกรายการวิ่ง --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>" 
                                        data-price="<?= $cat['standard_price'] ?>"
                                        <?= $runner['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?> (<?= $cat['distance_km'] ?> km)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">การจัดส่ง (Shipping)</label>
                        <select name="shipping_id" id="shipping_id" class="form-select" required onchange="calculateTotal()">
                            <?php foreach ($shippings as $ship): ?>
                                <option value="<?= $ship['shipping_id'] ?>" 
                                        data-cost="<?= $ship['cost'] ?>"
                                        <?= $runner['shipping_id'] == $ship['shipping_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ship['type']) ?> (+฿<?= number_format($ship['cost'], 0) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                         <label class="form-label">สถานะการชำระเงิน</label>
                         <select name="status" class="form-select">
                            <option value="Pending" <?= $runner['status'] == 'Pending' ? 'selected' : '' ?>>ยังไม่ชำระ (Pending)</option>
                            <option value="Paid" <?= $runner['status'] == 'Paid' ? 'selected' : '' ?>>ชำระเงินแล้ว (Paid)</option>
                            <option value="Cancelled" <?= $runner['status'] == 'Cancelled' ? 'selected' : '' ?>>ยกเลิก (Cancelled)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">BIB Number (ถ้ามี)</label>
                        <input type="text" name="bib_number" class="form-control" value="<?= htmlspecialchars($runner['bib_number']) ?>" placeholder="เช่น CBM-001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ยอดเงินรวมโดยประมาณ</label>
                        <div id="total_display" class="fs-4 fw-800 text-danger">฿0</div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-md-6">
                        <a href="admin_dashboard.php" class="btn-cancel d-block text-center">ยกเลิก (Cancel)</a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn-save w-100">บันทึกข้อมูล (Save)</button>
                    </div>
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
        document.getElementById('total_display').innerText = '฿' + total.toLocaleString();
    }
    
    // Run on load
    window.addEventListener('DOMContentLoaded', calculateTotal);
</script>

</body>
</html>