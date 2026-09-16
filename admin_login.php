<?php
session_start();
require_once 'db.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Admin Authentication Verification
    if ($username === 'admin@PKR' && $password === 'password') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin@PKR';
        $_SESSION['role'] = 'admin';
        header("Location: admin_portal.php");
        exit();
    } else {
        $error_msg = "Invalid Administrator Credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Admin Login</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #1e293b; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 350px; border-top: 6px solid #dc2626; }
        h3 { text-align: center; color: #dc2626; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #333; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .btn { background: #dc2626; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 15px; }
        .link { display: block; text-align: center; margin-top: 20px; font-size: 13px; text-decoration: none; color: #64748b; font-weight: 600; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <h3>🛡️ Canteen Admin Login</h3>
    <?php if($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Admin ID</label>
            <input type="text" name="username" required placeholder="admin@PKR">
        </div>
        <div class="form-group">
            <label>Master Password</label>
            <input type="password" name="password" required placeholder="Enter admin password">
        </div>
        <button type="submit" class="btn">Login to Dashboard</button>
    </form>
    <a href="login.php" class="link">← Back to Student/Staff Portal</a>
</div>
</body>
</html>
