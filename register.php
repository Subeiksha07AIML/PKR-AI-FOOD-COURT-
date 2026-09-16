<?php
session_start();
require_once 'db.php';

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reg_no = trim($_POST['register_number']);
    $name = trim($_POST['student_name']);
    $dob = trim($_POST['password']); // Format: DD/MM/YYYY
    $role = $_POST['role'];

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE register_number = ?");
    $stmt->execute([$reg_no]);
    if ($stmt->rowCount() > 0) {
        $error = "Register Number / Staff ID already registered!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (register_number, student_name, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$reg_no, $name, $dob, $role])) {
            $msg = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Register</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); width: 380px; border-top: 6px solid #003366; }
        h3 { color: #003366; margin-top: 0; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #333; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .btn { background: #003366; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 5px; }
        .btn:hover { background: #002244; }
        .msg { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .link { display: block; text-align: center; margin-top: 15px; font-size: 13px; text-decoration: none; color: #003366; font-weight: 600; }
    </style>
</head>
<body>
<div class="card">
    <h3>📝 Portal Registration</h3>
    <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
    <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST" autocomplete="off">
        <div class="form-group">
            <label>Register Number / Staff ID</label>
            <input type="text" name="register_number" required placeholder="e.g. 241AI024">
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="student_name" required placeholder="e.g. Subeiksha S.A">
        </div>
        <div class="form-group">
            <label>Password (Date of Birth: DD/MM/YYYY)</label>
            <input type="text" name="password" required placeholder="07/12/2006">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="student">Student</option>
                <option value="staff">Staff</option>
            </select>
        </div>
        <button type="submit" class="btn">Register Account</button>
    </form>
    <a href="login.php" class="link">Already have an account? Login here</a>
</div>
</body>
</html>
