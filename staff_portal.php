<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$menu = $pdo->query("SELECT * FROM menu_items")->fetchAll();
$tokens = $pdo->prepare("SELECT * FROM food_tokens WHERE user_id = ? ORDER BY token_id DESC");
$tokens->execute([$_SESSION['user_id']]);
$my_tokens = $tokens->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Portal - PKR Food Court</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        h2, h3 { color: #003366; }
        .btn { background: #003366; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .logout { color: #dc2626; text-decoration: none; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> (Staff Portal)</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <h3>🎟️ Your Order History & Quick Tokens</h3>
    <table>
        <tr><th>Token No</th><th>Details</th><th>Price</th><th>Status</th><th>Time</th></tr>
        <?php foreach($my_tokens as $t): ?>
        <tr>
            <td><?= $t['token_number'] ?></td>
            <td><?= htmlspecialchars($t['order_details']) ?></td>
            <td>₹<?= number_format($t['total_price'], 2) ?></td>
            <td><strong><?= $t['status'] ?></strong></td>
            <td><?= $t['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
