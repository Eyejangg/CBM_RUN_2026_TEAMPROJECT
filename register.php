<?php
require_once 'db.php';
session_start();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $citizenId = trim($_POST['citizen_id']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!empty($username) && !empty($password) && !empty($firstName) && !empty($lastName)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, date_of_birth, gender, citizen_id, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $firstName, $lastName, $dob, $gender, $citizenId, $phone, $address]);
                $success = 'Registration successful! <a href="login.php" class="alert-link">Login here</a>';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4d4d;
            --secondary: #2b2d42;
            --bg: #f8f9fa;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #2b2d42 0%, #1a1b29 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .reg-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .reg-header {
            background: var(--secondary);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .reg-header h2 { font-weight: 800; margin: 0; }
        .reg-header p { opacity: 0.7; margin: 10px 0 0; }
        .reg-body { padding: 50px; }
        .form-label { font-weight: 600; color: var(--secondary); font-size: 0.9rem; }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 20px;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: none;
        }
        .btn-reg {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-reg:hover {
            background: #d90429;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 77, 77, 0.3);
        }
        .icon-box {
            width: 40px;
            height: 40px;
            background: rgba(255, 77, 77, 0.1);
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="reg-card mx-auto animate__animated animate__fadeInUp">
        <div class="reg-header">
            <div class="d-inline-block px-3 py-1 bg-danger rounded-pill mb-3" style="font-size: 0.8rem; font-weight: 700;">NEW MEMBER</div>
            <h2>สมัครสมาชิกนักวิ่ง</h2>
            <p>ร่วมเป็นส่วนหนึ่งของ CBM RUN 2026 เพื่อสิทธิประโยชน์มากมาย</p>
        </div>
        <div class="reg-body">
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-4 mb-4"><i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success rounded-4 mb-4"><i class="fas fa-check-circle me-2"></i> <?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">
                    <!-- Account Section -->
                    <div class="col-12"><h5 class="fw-bold mb-0 border-start border-danger border-4 ps-3">ข้อมูลบัญชีผู้ใช้</h5></div>
                    <div class="col-md-6">
                        <label class="form-label">ชื่อผู้ใช้งาน (Username) <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="เช่น runner01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">รหัสผ่าน (Password) <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <!-- Profile Section -->
                    <div class="col-12 mt-5"><h5 class="fw-bold mb-0 border-start border-danger border-4 ps-3">ข้อมูลส่วนตัว</h5></div>
                    <div class="col-md-6">
                        <label class="form-label">ชื่อจริง (First Name) <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" placeholder="ภาษาอังกฤษ" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">นามสุกล (Last Name) <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" placeholder="ภาษาอังกฤษ" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วัน/เดือน/ปี เกิด <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เพศ <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">เลือกเพศ</option>
                            <option value="Male">ชาย (Male)</option>
                            <option value="Female">หญิง (Female)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เลขบัตรประชาชน (13 หลัก)</label>
                        <input type="text" name="citizen_id" class="form-control" maxlength="13" placeholder="1234567890123">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="08XXXXXXXX" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ที่อยู่ปัจจุบัน</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="บ้านเลขที่, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-reg hvr-grow">ยืนยันการสมัครสมาชิก</button>
                
                <div class="text-center mt-4 text-muted">
                    มีบัญชีอยู่แล้ว? <a href="login.php" class="text-danger fw-bold text-decoration-none">เข้าสู่ระบบ</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>