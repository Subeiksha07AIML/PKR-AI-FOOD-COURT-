<?php
session_start();
require_once 'db.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 1. Check Kitchen Admin Login
    if ($username === 'pkr@admin' && $password === 'pkr@admin') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'pkr@admin';
        $_SESSION['full_name'] = 'Kitchen Admin';
        $_SESSION['role'] = 'admin';
        header("Location: admin_portal.php");
        exit();
    }

    // 2. Check Student / Staff Login
    $stmt = $pdo->prepare("SELECT * FROM students WHERE register_number = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['register_number'];
        $_SESSION['full_name'] = $user['student_name'];
        $_SESSION['role'] = $user['role']; // 'student' or 'staff'
        
        if ($user['role'] === 'staff') {
            header("Location: staff_portal.php");
        } else {
            header("Location: student_portal.php");
        }
        exit();
    } else {
        $error_msg = "Invalid ID or Password (Format: DD/MM/YYYY)!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Login</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); width: 380px; border-top: 6px solid #003366; }
        h3 { color: #003366; margin-top: 0; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #333; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .password-container { position: relative; display: flex; align-items: center; }
        .password-container input { width: 100%; padding-right: 40px; }
        .toggle-eye { position: absolute; right: 12px; cursor: pointer; font-size: 16px; }
        .btn-primary { background: #003366; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 5px; }
        .btn-primary:hover { background: #002244; }
        .link { display: block; text-align: center; margin-top: 15px; font-size: 13px; text-decoration: none; color: #003366; font-weight: 600; }
        .error { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .helper-text { font-size: 11px; color: #64748b; margin-top: 4px; display: block; }
    </style>
</head>
<body>
<div class="card">
    <h3>🎓 PKR Food Court Login</h3>
    <?php if($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>
    <form method="POST" autocomplete="off">
        <div class="form-group">
            <label>Register Number / Staff ID / Admin</label>
            <input type="text" name="username" required placeholder="e.g. 241AI024 or pkr@admin" autocomplete="off">
        </div>
        <div class="form-group">
            <label>Password (DOB: DD/MM/YYYY)</label>
            <div class="password-container">
                <input type="password" name="password" id="passwordField" required placeholder="DD/MM/YYYY" autocomplete="new-password">
                <span class="toggle-eye" onclick="togglePassword()">👁️</span>
            </div>
            <span class="helper-text">Format: DD/MM/YYYY (e.g., 07/12/2006)</span>
        </div>
        <button type="submit" class="btn-primary">Login to Portal</button>
    </form>
    <a href="register.php" class="link">Don't have an account? Register here</a>
</div>
<script>
function togglePassword() {
    const pf = document.getElementById('passwordField');
    pf.type = pf.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
