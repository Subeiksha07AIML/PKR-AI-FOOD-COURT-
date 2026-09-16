<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Status Updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    if ($action === 'ready') {
        $pdo->prepare("UPDATE food_tokens SET status = 'Ready' WHERE token_id = ?")->execute([$id]);
    } elseif ($action === 'complete') {
        $pdo->prepare("UPDATE food_tokens SET status = 'Completed' WHERE token_id = ?")->execute([$id]);
    }
}

// Handle Inventory Toggle
if (isset($_GET['toggle_item'])) {
    $item_id = $_GET['toggle_item'];
    $pdo->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE id = ?")->execute([$item_id]);
    header("Location: admin_portal.php");
    exit();
}

$tokens = $pdo->query("SELECT ft.*, s.student_name, s.register_number FROM food_tokens ft JOIN students s ON ft.user_id = s.id ORDER BY ft.token_id DESC")->fetchAll();
$menu = $pdo->query("SELECT * FROM menu_items")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kitchen Admin Portal (KDS)</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        h2, h3 { color: #003366; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 13px; }
        th { background: #e2e8f0; }
        .btn { padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; font-size: 12px; text-decoration: none; }
        .btn-ready { background: #eab308; color: white; }
        .btn-done { background: #22c55e; color: white; }
        .btn-toggle { background: #64748b; color: white; }
        .logout { color: #dc2626; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>🛡️ Kitchen Admin & KDS Portal</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <h3>⚡ Live Kitchen Token Status (KDS)</h3>
    <table>
        <tr>
            <th>Token</th>
            <th>User / Register No</th>
            <th>Order Details</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach($tokens as $t): ?>
        <tr>
            <td><strong><?= $t['token_number'] ?></strong></td>
            <td><?= htmlspecialchars($t['student_name']) ?> (<?= $t['register_number'] ?>)</td>
            <td><?= htmlspecialchars($t['order_details']) ?></td>
            <td>₹<?= number_format($t['total_price'], 2) ?></td>
            <td><strong><?= $t['status'] ?></strong></td>
            <td>
                <?php if($t['status'] === 'Pending'): ?>
                    <a href="admin_portal.php?action=ready&id=<?= $t['token_id'] ?>" class="btn btn-ready">Mark Ready</a>
                <?php elseif($t['status'] === 'Ready'): ?>
                    <a href="admin_portal.php?action=complete&id=<?= $t['token_id'] ?>" class="btn btn-done">Complete</a>
                <?php else: ?>
                    <span>Finished</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>manage Menu Inventory Availability</h3>
    <table>
        <tr><th>Item Name</th><th>Price</th><th>Availability Status</th><th>Action</th></tr>
        <?php foreach($menu as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['item_name']) ?></td>
            <td>₹<?= number_format($m['price'], 2) ?></td>
            <td><?= $m['is_available'] ? '<span style="color:green">In Stock</span>' : '<span style="color:red">Out of Stock</span>' ?></td>
            <td><a href="admin_portal.php?toggle_item=<?= $m['id'] ?>" class="btn btn-toggle">Toggle Stock</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
