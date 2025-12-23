<?php
require_once 'db.php';

try {
    echo "Starting Data Seeding...<br>";

    // 1. Clear existing data (optional, but good for re-running)
    // 2. Drop Tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['PAYMENT', 'REGISTRATION', 'AGE_GROUP', 'PRICE_RATE', 'SHIPPING_OPTION', 'RACE_CATEGORY', 'RUNNER', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Dropped old tables.<br>";

    // 3. Create Tables
    $sqlSchema = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE RUNNER (
        runner_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('Male', 'Female') NOT NULL,
        citizen_id VARCHAR(13),
        phone VARCHAR(20),
        email VARCHAR(100),
        address TEXT,
        disabled BOOLEAN DEFAULT FALSE COMMENT 'สถานะผู้พิการ'
    );

    CREATE TABLE RACE_CATEGORY (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        distance_km FLOAT NOT NULL,
        start_time TIME,
        time_limit TIME,
        giveaway_type VARCHAR(50)
    );

    CREATE TABLE SHIPPING_OPTION (
        shipping_id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        cost DECIMAL(10, 2) DEFAULT 0.00,
        detail VARCHAR(255)
    );

    CREATE TABLE PRICE_RATE (
        price_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        runner_type VARCHAR(50) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id)
    );

    CREATE TABLE AGE_GROUP (
        group_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        gender ENUM('Male', 'Female') NOT NULL,
        min_age INT,
        max_age INT,
        FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id)
    );

    CREATE TABLE REGISTRATION (
        reg_id INT AUTO_INCREMENT PRIMARY KEY,
        runner_id INT NOT NULL,
        category_id INT NOT NULL,
        price_id INT NOT NULL,
        shipping_id INT NOT NULL,
        reg_date DATE NOT NULL,
        shirt_size VARCHAR(10),
        bib_number VARCHAR(20),
        status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
        FOREIGN KEY (runner_id) REFERENCES RUNNER(runner_id),
        FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id),
        FOREIGN KEY (price_id) REFERENCES PRICE_RATE(price_id),
        FOREIGN KEY (shipping_id) REFERENCES SHIPPING_OPTION(shipping_id)
    );

    CREATE TABLE PAYMENT (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        reg_id INT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        payment_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        payment_method VARCHAR(50),
        status ENUM('Success', 'Failed') NOT NULL,
        FOREIGN KEY (reg_id) REFERENCES REGISTRATION(reg_id) ON DELETE CASCADE
    );
    ";
    
    $pdo->exec($sqlSchema);
    echo "Created tables.<br>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Cleared old data.<br>";

    // Start Transaction for Inserts
    $pdo->beginTransaction();

    // 1.5 Insert Default Admin
    $adminUser = 'admin';
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmtAdmin = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name) VALUES (?, ?, ?, ?)");
    $stmtAdmin->execute([$adminUser, $adminPass, 'Admin', 'User']);
    echo "Inserted Default Admin (user: admin, pass: admin123).<br>";


    // 2. Insert Race Categories
    $categories = [
        ['Marathon', 42.195, '03:00:00', '07:00:00', 'Finisher Tee'],
        ['Half Marathon', 21.1, '04:00:00', '03:30:00', 'Finisher Tee'],
        ['Mini Marathon', 10.5, '05:00:00', '02:00:00', 'None']
    ];
    $stmtCategory = $pdo->prepare("INSERT INTO RACE_CATEGORY (name, distance_km, start_time, time_limit, giveaway_type) VALUES (?, ?, ?, ?, ?)");
    
    $categoryIds = [];
    foreach ($categories as $cat) {
        $stmtCategory->execute($cat);
        $categoryIds[$cat[0]] = $pdo->lastInsertId();
    }
    echo "Inserted Categories.<br>";

    // 3. Insert Shipping Options
    $shippings = [
        ['EMS', 50.00, 'Deliver to address'],
        ['Pickup', 0.00, 'Pick up at Expo']
    ];
    $stmtShipping = $pdo->prepare("INSERT INTO SHIPPING_OPTION (type, cost, detail) VALUES (?, ?, ?)");
    
    $shippingIds = [];
    foreach ($shippings as $ship) {
        $stmtShipping->execute($ship);
        $shippingIds[$ship[0]] = $pdo->lastInsertId();
    }
    echo "Inserted Shipping Options.<br>";

    // 4. Insert Price Rates (Linked to Category)
    // Map: Category Name -> [ [Runner Type, Amount] ]
    $pricesData = [
        'Marathon' => [['Standard', 1200], ['Senior 70+', 600]],
        'Half Marathon' => [['Standard', 900], ['Senior 70+', 450]],
        'Mini Marathon' => [['Standard', 600], ['Senior 70+', 300]]
    ];
    $stmtPrice = $pdo->prepare("INSERT INTO PRICE_RATE (category_id, runner_type, amount) VALUES (?, ?, ?)");
    
    $priceIds = []; // Key: "CatID_Type" -> PriceID
    foreach ($pricesData as $catName => $rates) {
        $catId = $categoryIds[$catName];
        foreach ($rates as $rate) {
            $stmtPrice->execute([$catId, $rate[0], $rate[1]]);
            $priceIds[$catId . '_' . $rate[0]] = $pdo->lastInsertId();
        }
    }
    echo "Inserted Price Rates.<br>";

    // 5. Insert Runners (Thai Names)
    $runners = [
        ['Somsak', 'Jaidee', '1985-05-20', 'Male', '1100000000001', '0812345678', 'somsak@email.com', 'Bangkok', 0],
        ['Manee', 'Meela', '1990-11-15', 'Female', '1200000000002', '0898765432', 'manee@email.com', 'Chiang Mai', 0],
        ['Piti', 'Rakchart', '1975-03-10', 'Male', '1300000000003', '0865432109', 'piti@email.com', 'Phuket', 0],
        ['Chujai', 'Yindee', '1995-08-25', 'Female', '1400000000004', '0876543210', 'chujai@email.com', 'Khon Kaen', 0],
        ['Mana', 'Kla-harn', '1950-01-01', 'Male', '1500000000005', '0855555555', 'mana@email.com', 'Bangkok', 0], // Senior
        ['Apassara', 'Hongsakul', '1988-02-14', 'Female', '1600000000006', '0811111111', 'apassara@email.com', 'Pattaya', 0]
    ];
    $stmtRunner = $pdo->prepare("INSERT INTO RUNNER (first_name, last_name, date_of_birth, gender, citizen_id, phone, email, address, disabled) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $runnerIds = [];
    foreach ($runners as $runner) {
        $stmtRunner->execute($runner);
        $runnerIds[] = $pdo->lastInsertId();
    }
    echo "Inserted Runners.<br>";

    // 6. Register Runners and Payments
    // We will randomly assign them to categories and create payments
    $stmtReg = $pdo->prepare("INSERT INTO REGISTRATION (runner_id, category_id, price_id, shipping_id, reg_date, shirt_size, bib_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtPay = $pdo->prepare("INSERT INTO PAYMENT (reg_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?)");

    // Define some scenarios
    $scenarios = [
        // Runner 0: Somsak -> Marathon, Paid
        ['runner_idx' => 0, 'cat' => 'Marathon', 'type' => 'Standard', 'ship' => 'EMS', 'status' => 'Paid', 'pay_status' => 'Success'],
        // Runner 1: Manee -> Half, Paid
        ['runner_idx' => 1, 'cat' => 'Half Marathon', 'type' => 'Standard', 'ship' => 'Pickup', 'status' => 'Paid', 'pay_status' => 'Success'],
        // Runner 2: Piti -> Mini, Pending
        ['runner_idx' => 2, 'cat' => 'Mini Marathon', 'type' => 'Standard', 'ship' => 'EMS', 'status' => 'Pending', 'pay_status' => 'Failed'], // Tried to pay but failed
        // Runner 3: Chujai -> Half, Paid
        ['runner_idx' => 3, 'cat' => 'Half Marathon', 'type' => 'Standard', 'ship' => 'EMS', 'status' => 'Paid', 'pay_status' => 'Success'],
        // Runner 4: Mana (Senior) -> Mini, Paid
        ['runner_idx' => 4, 'cat' => 'Mini Marathon', 'type' => 'Senior 70+', 'ship' => 'Pickup', 'status' => 'Paid', 'pay_status' => 'Success'],
         // Runner 5: Apassara -> Marathon, Cancelled
        ['runner_idx' => 5, 'cat' => 'Marathon', 'type' => 'Standard', 'ship' => 'EMS', 'status' => 'Cancelled', 'pay_status' => null]
    ];

    foreach ($scenarios as $s) {
        $runnerId = $runnerIds[$s['runner_idx']];
        $catId = $categoryIds[$s['cat']];
        $priceId = $priceIds[$catId . '_' . $s['type']]; // Look up price ID
        $shippingId = $shippingIds[$s['ship']];
        
        // Calculate Amount for Payment
        // Re-query amount from DB or just hardcode based on data above for simplicity? 
        // Let's use the array data.
        $basePrice = 0;
        foreach ($pricesData[$s['cat']] as $rate) {
            if ($rate[0] == $s['type']) $basePrice = $rate[1];
        }
        $shipCost = ($s['ship'] == 'EMS') ? 50 : 0;
        $totalAmount = $basePrice + $shipCost;

        $bib = rand(1000, 9999);
        $regDate = date('Y-m-d');
        
        $stmtReg->execute([$runnerId, $catId, $priceId, $shippingId, $regDate, 'L', $bib, $s['status']]);
        $regId = $pdo->lastInsertId();

        if ($s['pay_status'] !== null) {
            $stmtPay->execute([$regId, $totalAmount, 'Credit Card', $s['pay_status']]);
        }
    }
    echo "Inserted Registrations and Payments.<br>";

    $pdo->commit();
    echo "<strong>Data Setup Completed Successfully!</strong>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?>