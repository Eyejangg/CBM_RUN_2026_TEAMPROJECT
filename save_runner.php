<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $reg_id = $_POST['reg_id'];
        $runner_id = $_POST['runner_id'];

        // Runner Data
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $dob = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $citizenId = $_POST['citizen_id'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        // Registration Data
        $categoryId = $_POST['category_id'];
        $shippingId = $_POST['shipping_id'];
        $status = $_POST['status'];
        $bib = $_POST['bib_number'];

        // Determine Price ID (Simple logic: Default to Standard type for now)
        // In a real app, we might select 'Senior' based on Age, or have a dropdown.
        // Let's find the 'Standard' price for this category.
        $stmtPrice = $pdo->prepare("SELECT price_id FROM PRICE_RATE WHERE category_id = ? AND runner_type = 'Standard' LIMIT 1");
        $stmtPrice->execute([$categoryId]);
        $priceRow = $stmtPrice->fetch();
        // Fallback or use existing logic if price not found? Assuming seeding created it.
        $priceId = $priceRow['price_id'] ?? 1; 

        if (empty($reg_id)) {
            // INSERT NEW
            
            // 1. Insert Runner
            $stmtRunner = $pdo->prepare("INSERT INTO RUNNER (first_name, last_name, date_of_birth, gender, citizen_id, phone, email, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtRunner->execute([$firstName, $lastName, $dob, $gender, $citizenId, $phone, $email, $address]);
            $runner_id = $pdo->lastInsertId();

            // 2. Insert Registration
            $regDate = date('Y-m-d');
            $stmtReg = $pdo->prepare("INSERT INTO REGISTRATION (runner_id, category_id, price_id, shipping_id, reg_date, status, bib_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtReg->execute([$runner_id, $categoryId, $priceId, $shippingId, $regDate, $status, $bib]);
            $reg_id = $pdo->lastInsertId();

            // 3. Insert Payment (Placeholder amount logic)
            // Ideally calculate correct amount.
            // Let's check amount
            $stmtAmount = $pdo->prepare("SELECT amount FROM PRICE_RATE WHERE price_id = ?");
            $stmtAmount->execute([$priceId]);
            $amount = $stmtAmount->fetchColumn();
            
            $stmtShip = $pdo->prepare("SELECT cost FROM SHIPPING_OPTION WHERE shipping_id = ?");
            $stmtShip->execute([$shippingId]);
            $shipCost = $stmtShip->fetchColumn();
            
            $total = $amount + $shipCost;
            
            $payStatus = ($status == 'Paid') ? 'Success' : 'Failed'; 
            // Only create payment record if Paid? Or always? Schema has payment_id PK.
            // Let's create one.
            $stmtPay = $pdo->prepare("INSERT INTO PAYMENT (reg_id, total_amount, payment_method, status) VALUES (?, ?, 'Cash', ?)");
            $stmtPay->execute([$reg_id, $total, $payStatus]);

        } else {
            // UPDATE EXISTING

            // 1. Update Runner
            $stmtRunner = $pdo->prepare("UPDATE RUNNER SET first_name=?, last_name=?, date_of_birth=?, gender=?, citizen_id=?, phone=?, email=?, address=? WHERE runner_id=?");
            $stmtRunner->execute([$firstName, $lastName, $dob, $gender, $citizenId, $phone, $email, $address, $runner_id]);

            // 2. Update Registration
            // Note: If category changes, price_id should change too.
             // Recalculate Price ID just in case category changed
            $stmtReg = $pdo->prepare("UPDATE REGISTRATION SET category_id=?, price_id=?, shipping_id=?, status=?, bib_number=? WHERE reg_id=?");
            $stmtReg->execute([$categoryId, $priceId, $shippingId, $status, $bib, $reg_id]);
            
            // 3. Update Payment Status as well if Registration Status is Paid
            // Simplified logic: Update the payment record associated with this reg_id
             $payStatus = ($status == 'Paid') ? 'Success' : 'Failed'; 
             $stmtPay = $pdo->prepare("UPDATE PAYMENT SET status=? WHERE reg_id=?");
             $stmtPay->execute([$payStatus, $reg_id]);
        }

        $pdo->commit();
        header("Location: index.php?msg=saved");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Error saving data: " . $e->getMessage());
    }
}
?>