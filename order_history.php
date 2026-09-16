<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM food_tokens WHERE user_id = ? ORDER BY token_id DESC");
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - My Orders</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f8; margin: 0; }
        header { background: #004080; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; }
        .panel { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
        th { background: #004080; color: white; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 7px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .time-tag { font-size: 11px; color: #555; display: block; }
    </style>
</head>
<body>

<header>
    <h2>PKR Food Court - My Order Timelines</h2>
    <a href="student_portal.php" class="back-btn">⬅ Back to Menu</a>
</header>

<div class="container">
    <div class="panel">
        <h3>Your Order Progress & Timestamps</h3>
        <table>
            <tr>
                <th>Token No</th>
                <th>Order Details</th>
                <th>Status</th>
                <th>Order Time</th>
                <th>Prepare Time</th>
                <th>Receive Time</th>
            </tr>
            <?php if(empty($my_orders)): ?>
                <tr><td colspan="6" style="text-align:center; color:#777;">You haven't placed any orders yet.</td></tr>
            <?php else: ?>
                <?php foreach($my_orders as $o): ?>
                <tr>
                    <td><b><?= htmlspecialchars($o['token_number']) ?></b></td>
                    <td><?= htmlspecialchars($o['order_details']) ?><br><b>₹<?= htmlspecialchars($o['total_price']) ?></b></td>
                    <td><b><?= htmlspecialchars($o['status']) ?></b></td>
                    <td><span class="time-tag">📅 <?= htmlspecialchars($o['created_at']) ?></span></td>
                    <td><span class="time-tag" style="color:#d97706;"><?= $o['prepare_time'] ? '👨‍🍳 ' . htmlspecialchars($o['prepare_time']) : 'Pending' ?></span></td>
                    <td><span class="time-tag" style="color:#16a34a;"><?= $o['receive_time'] ? '✅ ' . htmlspecialchars($o['receive_time']) : 'Pending' ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
