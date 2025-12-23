<?php
session_start();
require_once 'db.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reg_id = $_GET['reg_id'] ?? null;
if (!$reg_id) {
    header("Location: my_registrations.php");
    exit();
}

// Fetch Registration & Payment Info
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.reg_id,
            rc.name as category_name,
            p.total_amount,
            r.status as reg_status
        FROM REGISTRATION r
        JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id
        JOIN PAYMENT p ON r.reg_id = p.reg_id
        WHERE r.reg_id = ?
    ");
    $stmt->execute([$reg_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || $order['reg_status'] === 'Paid') {
        header("Location: my_registrations.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle Simulated Payment
if (isset($_POST['pay'])) {
    try {
        $pdo->beginTransaction();
        
        // Update Registration Status
        $stmt1 = $pdo->prepare("UPDATE REGISTRATION SET status = 'Paid' WHERE reg_id = ?");
        $stmt1->execute([$reg_id]);
        
        // Update Payment Status
        $stmt2 = $pdo->prepare("UPDATE PAYMENT SET status = 'Success', payment_time = NOW(), payment_method = 'QR Thai' WHERE reg_id = ?");
        $stmt2->execute([$reg_id]);
        
        $pdo->commit();
        header("Location: my_registrations.php?msg=paid");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Payment failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff4d4d; --secondary: #2b2d42; }
        body { font-family: 'Outfit', sans-serif; background-color: #f0f2f5; color: var(--secondary); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .payment-card { background: white; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); width: 100%; max-width: 500px; overflow: hidden; }
        .payment-header { background: var(--secondary); color: white; padding: 30px; text-align: center; }
        .payment-body { padding: 40px; }
        .qr-placeholder { background: #f8f9fa; border: 2px dashed #ddd; border-radius: 20px; padding: 30px; text-align: center; margin-bottom: 30px; }
        .qr-placeholder i { font-size: 5rem; color: var(--secondary); margin-bottom: 10px; }
        .total-amount { font-size: 2.5rem; font-weight: 800; color: var(--primary); text-align: center; margin-bottom: 10px; }
        .btn-pay { background: var(--primary); color: white; border: none; padding: 15px; border-radius: 15px; font-weight: 700; width: 100%; text-transform: uppercase; transition: 0.3s; }
        .btn-pay:hover { background: #d90429; transform: scale(1.02); }
    </style>
</head>
<body>

<div class="payment-card">
    <div class="payment-header">
        <h3 class="fw-800 mb-0">ชำระเงิน</h3>
        <p class="mb-0 opacity-75">Order #<?= $order['reg_id'] ?> - <?= htmlspecialchars($order['category_name']) ?></p>
    </div>
    <div class="payment-body">
        <div class="text-center mb-4">
            <small class="text-muted d-block">ยอดเงินที่ต้องชำระ</small>
            <div class="total-amount">฿<?= number_format($order['total_amount'], 2) ?></div>
        </div>

        <div class="qr-placeholder">
            <i class="fas fa-qrcode"></i>
            <p class="mb-0 fw-bold">Scan to Pay with Thai QR</p>
            <small class="text-muted">(Simulated QR Code)</small>
        </div>

        <div class="alert alert-info rounded-4 mb-4" style="font-size: 0.9rem;">
            <i class="fas fa-info-circle me-2"></i> <strong>ขั้นตอน:</strong> สแกน QR Code ผ่านแอปธนาคารของท่าน และกดยกยันการชำระเงินด้านล่าง
        </div>

        <form method="POST">
            <button type="submit" name="pay" class="btn btn-pay">ยืนยันการชำระเงินเรียบร้อยแล้ว</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="my_registrations.php" class="text-muted text-decoration-none"><i class="fas fa-arrow-left me-1"></i> กลับไปภายหลัง</a>
        </div>
    </div>
</div>

</body>
</html>
