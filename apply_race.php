<?php
session_start();
require_once 'db.php';

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Data to Pre-fill
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// 3. Fetch Race Categories & Shipping Options
$categories = $pdo->query("
    SELECT c.*, p.amount as standard_price 
    FROM RACE_CATEGORY c 
    LEFT JOIN PRICE_RATE p ON c.category_id = p.category_id AND p.runner_type = 'Standard'
")->fetchAll(PDO::FETCH_ASSOC);

$shippings = $pdo->query("SELECT * FROM SHIPPING_OPTION")->fetchAll(PDO::FETCH_ASSOC);

// Selected Category from GET (if any)
$selected_cat_id = $_GET['category'] ?? '';

// 4. Handle Registration POST
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $dob = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $citizenId = $_POST['citizen_id'];
        $phone = $_POST['phone'];
        $email = $_POST['email'] ?? $user['username'] . '@email.com'; // Fallback
        $address = $_POST['address'];
        $categoryId = $_POST['category_id'];
        $shirtSize = $_POST['shirt_size'];
        $shippingId = $_POST['shipping_id'];

        // Find Price ID
        $stmtPrice = $pdo->prepare("SELECT price_id, amount FROM PRICE_RATE WHERE category_id = ? AND runner_type = 'Standard' LIMIT 1");
        $stmtPrice->execute([$categoryId]);
        $priceRow = $stmtPrice->fetch();
        $priceId = $priceRow['price_id'];
        $baseAmount = $priceRow['amount'];

        // Shipping Cost
        $stmtShip = $pdo->prepare("SELECT cost FROM SHIPPING_OPTION WHERE shipping_id = ?");
        $stmtShip->execute([$shippingId]);
        $shipCost = $stmtShip->fetchColumn();

        $totalAmount = $baseAmount + $shipCost;

        // A. Insert/Get Runner
        // Link the runner to the user_id
        $stmtRunner = $pdo->prepare("INSERT INTO RUNNER (first_name, last_name, date_of_birth, gender, citizen_id, phone, email, address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRunner->execute([$firstName, $lastName, $dob, $gender, $citizenId, $phone, $email, $address, $user_id]);
        $runnerId = $pdo->lastInsertId();

        // B. Insert Registration
        // Generate Random BIB
        $bib = mt_rand(1000, 9999);
        // Ensure BIB is unique? For now, just random is fine as per request.
        
        $regDate = date('Y-m-d');
        $stmtReg = $pdo->prepare("INSERT INTO REGISTRATION (runner_id, category_id, price_id, shipping_id, reg_date, shirt_size, status, bib_number) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
        $stmtReg->execute([$runnerId, $categoryId, $priceId, $shippingId, $regDate, $shirtSize, $bib]);
        $regId = $pdo->lastInsertId();

        // C. Insert Payment (Status: Failed/Pending until paid)
        $stmtPay = $pdo->prepare("INSERT INTO PAYMENT (reg_id, total_amount, payment_method, status) VALUES (?, ?, 'Pending', 'Failed')");
        $stmtPay->execute([$regId, $totalAmount]);

        $pdo->commit();
        header("Location: payment.php?reg_id=" . $regId);
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff4d4d; --secondary: #2b2d42; }
        body { font-family: 'Outfit', sans-serif; background-color: #f8f9fa; color: var(--secondary); }
        .navbar { background: var(--secondary); padding: 15px 0; }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: white !important; }
        .navbar-brand span { color: var(--primary); }
        .form-card { background: white; border-radius: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: none; overflow: hidden; }
        .form-header { background: linear-gradient(135deg, #ff4d4d, #d90429); color: white; padding: 40px; text-align: center; }
        .form-body { padding: 40px; }
        .form-label { font-weight: 600; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 2px solid #eee; }
        .form-control:focus { border-color: var(--primary); box-shadow: none; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 15px 30px; border-radius: 15px; font-weight: 700; width: 100%; text-transform: uppercase; margin-top: 20px; transition: 0.3s; }
        .btn-submit:hover { background: #d90429; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255, 77, 77, 0.3); }
        .summary-box { background: #fdf2f2; border-radius: 20px; padding: 25px; border: 1px dashed var(--primary); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><span>CBM</span> RUN 2026</a>
        <div class="text-white d-flex align-items-center">
             <i class="fas fa-user-circle me-2"></i> <?= htmlspecialchars($user['first_name']) ?>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="form-header">
                    <h2 class="fw-800 mb-0">ใบสมัครรายการวิ่ง</h2>
                    <p class="mb-0 opacity-75">กรุณาตรวจสอบข้อมูลและเลือกประเภทการวิ่ง</p>
                </div>
                <div class="form-body">
                    <?php if ($msg): ?>
                        <div class="alert alert-danger rounded-4 mb-4"><?= $msg ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-4">
                            <!-- Pre-filled Profile -->
                            <div class="col-12"><h5 class="fw-bold border-start border-danger border-4 ps-3">ข้อมูลส่วนตัว (ดึงจากโปรไฟล์ของคุณ)</h5></div>
                            <div class="col-md-6">
                                <label class="form-label">ชื่อจริง (First Name)</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">นามสกุล (Last Name)</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">วัน/เดือน/ปี เกิด</label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?= $user['date_of_birth'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เพศ</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>ชาย (Male)</option>
                                    <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>หญิง (Female)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เลขบัตรประชาชน</label>
                                <input type="text" name="citizen_id" class="form-control" value="<?= htmlspecialchars($user['citizen_id']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ที่อยู่จัดส่ง</label>
                                <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>

                            <!-- Race Details -->
                            <div class="col-12 mt-5"><h5 class="fw-bold border-start border-danger border-4 ps-3">เลือกรายการและไซส์เสื้อ</h5></div>
                            <div class="col-md-6">
                                <label class="form-label">รายการวิ่ง</label>
                                <select name="category_id" id="category_id" class="form-select" required onchange="calculate()">
                                    <option value="">เลือกรายการ</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" 
                                                data-price="<?= $cat['standard_price'] ?>"
                                                <?= $selected_cat_id == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?> (฿<?= number_format($cat['standard_price']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ไซส์เสื้อ</label>
                                <select name="shirt_size" class="form-select" required>
                                    <option value="">เลือกไซส์</option>
                                    <option value="XS">XS (รอบอก 34")</option>
                                    <option value="S">S (รอบอก 36")</option>
                                    <option value="M">M (รอบอก 38")</option>
                                    <option value="L">L (รอบอก 40")</option>
                                    <option value="XL">XL (รอบอก 42")</option>
                                    <option value="2XL">2XL (รอบอก 44")</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">วิธีการรับเสื้อและเบอร์วิ่ง</label>
                                <select name="shipping_id" id="shipping_id" class="form-select" required onchange="calculate()">
                                    <?php foreach ($shippings as $ship): ?>
                                        <option value="<?= $ship['shipping_id'] ?>" data-cost="<?= $ship['cost'] ?>">
                                            <?= htmlspecialchars($ship['type']) ?> (฿<?= number_format($ship['cost']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="summary-box">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">ยอดชำระสุทธิ</span>
                                        <h3 class="fw-800 text-danger mb-0" id="total_display">฿0</h3>
                                    </div>
                                    <small class="text-muted">* ราคารวมค่าสมัครและค่าจัดส่ง (ถ้ามี)</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-submit">ยืนยันการสมัครและไปหน้าชำระเงิน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calculate() {
        const cat = document.getElementById('category_id');
        const ship = document.getElementById('shipping_id');
        const p = parseFloat(cat.options[cat.selectedIndex].dataset.price || 0);
        const s = parseFloat(ship.options[ship.selectedIndex].dataset.cost || 0);
        document.getElementById('total_display').innerText = '฿' + (p + s).toLocaleString();
    }
    window.onload = calculate;
</script>

</body>
</html>
