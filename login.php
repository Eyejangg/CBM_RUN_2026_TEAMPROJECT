<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, password, first_name, last_name FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // If admin, go to admin dashboard, else go to home
                if ($username === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CBM RUN 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4d4d;
            --secondary: #2b2d42;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #2b2d42 0%, #1a1b29 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 50px;
        }
        .login-header { text-align: center; margin-bottom: 40px; }
        .login-header h2 { font-weight: 800; color: var(--secondary); margin-bottom: 10px; }
        .login-header p { color: #6c757d; font-size: 0.95rem; }
        .form-label { font-weight: 600; color: var(--secondary); font-size: 0.9rem; }
        .form-control {
            border-radius: 12px;
            padding: 12px 20px;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: none;
        }
        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #d90429;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 77, 77, 0.3);
        }
        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 30px;
            display: block;
        }
        .brand-logo span { color: var(--primary); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <a href="home.php" class="brand-logo text-decoration-none"><span>CBM</span> RUN 2026</a>
        <h2>ยินดีต้อนรับ</h2>
        <p>เข้าสู่ระบบเพื่อจัดการการวิ่งของคุณ</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-4 mb-4" style="font-size: 0.9rem;"><i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">ชื่อผู้ใช้งาน</label>
            <div class="input-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">รหัสผ่าน</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-login">เข้าสู่ระบบ</button>
        
        <div class="text-center mt-4 text-muted" style="font-size: 0.9rem;">
            ยังไม่มีบัญชี? <a href="register.php" class="text-danger fw-bold text-decoration-none">สมัครสมาชิกที่นี่</a>
        </div>
    </form>
</div>

</body>
</html>