<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$step = 'menu';
$selected_item = null;

// Step 1: User clicked "Order Now" -> Show Payment Gateway Page
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proceed_to_pay'])) {
    $item_id = $_POST['item_id'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $selected_item = $stmt->fetch();
    if ($selected_item) {
        $step = 'payment';
    }
}

// Step 2: User clicked "Pay Now" on Payment Gateway -> Generate Token & Receipt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    $item_id = $_POST['item_id'];
    $payment_method = $_POST['payment_method'];
    
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item) {
        $token_no = 'PKR-' . rand(100, 999);
        $insert = $pdo->prepare("INSERT INTO food_tokens (user_id, token_number, order_details, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $insert->execute([$_SESSION['user_id'], $token_no, $item['item_name'], $item['price'], $payment_method]);
        
        $success_token = $token_no;
        $step = 'receipt';
    }
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
    <title>PKR Food Court - Student Portal & Secure Payment</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        h2, h3 { color: #003366; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .menu-card { border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; background: #fff; }
        .btn { background: #003366; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn:hover { background: #002244; }
        .logout { color: #dc2626; text-decoration: none; font-weight: 600; }
        
        /* Payment Gateway Layout Styles (Matching SBIePay design) */
        .gateway-container { display: flex; gap: 20px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .gateway-left { flex: 2; border-right: 1px solid #cbd5e1; padding-right: 20px; }
        .gateway-right { flex: 1; background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .pay-option { padding: 10px; margin-bottom: 8px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .pay-option:hover { background: #f1f5f9; }
        .summary-title { font-weight: bold; color: #003366; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; color: #333; }
        .total-row { font-weight: bold; font-size: 15px; border-top: 1px solid #e2e8f0; padding-top: 8px; color: #003366; }
        .btn-pay { background: #eab308; color: #000; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; margin-top: 15px; }
        .btn-pay:hover { background: #ca8a04; color: #fff; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .Pending { background: #fef08a; color: #854d0e; }
        .Ready { background: #bbf7d0; color: #166534; }
        .Completed { background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>🎓 Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if ($step == 'menu'): ?>
        <h3>🍽️ PKR Food Court Menu</h3>
        <div class="menu-grid">
            <?php foreach($menu as $m): ?>
            <div class="menu-card">
                <h4><?= htmlspecialchars($m['item_name']) ?></h4>
                <p>Price: ₹<?= number_format($m['price'], 2) ?></p>
                <?php if($m['is_available']): ?>
                <form method="POST">
                    <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                    <button type="submit" name="proceed_to_pay" class="btn">Order Now</button>
                </form>
                <?php else: ?>
                <span style="color:red; font-weight:bold;">Out of Stock</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <h3>🎟️ My Token & Order Status History</h3>
        <table>
            <tr><th>Token No</th><th>Item</th><th>Price</th><th>Status</th><th>Time</th></tr>
            <?php foreach($my_tokens as $t): ?>
            <tr>
                <td><strong><?= $t['token_number'] ?></strong></td>
                <td><?= htmlspecialchars($t['order_details']) ?></td>
                <td>₹<?= number_format($t['total_price'], 2) ?></td>
                <td><span class="badge <?= $t['status'] ?>"><?= $t['status'] ?></span></td>
                <td><?= $t['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($step == 'payment' && $selected_item): ?>
        <!-- SECURE PAYMENT GATEWAY INTERFACE -->
        <div style="background:#003366; color:white; padding:10px 15px; border-radius:6px 6px 0 0; display:flex; justify-content:space-between; align-items:center;">
            <span>🔒 Secure Payment Gateway (SBIePay Integration)</span>
            <span style="font-size:12px;">PKR ARTS COLLEGE FOR WOMEN</span>
        </div>
        
        <form method="POST">
            <input type="hidden" name="item_id" value="<?= $selected_item['id'] ?>">
            <div class="gateway-container">
                <div class="gateway-left">
                    <h4 style="margin-top:0; color:#003366;">Select Payment Method</h4>
                    
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="UPI QR" checked> 
                        📱 <strong>UPI QR Code</strong> (Scan & Pay via GPay / PhonePe / Paytm)
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="Net Banking"> 
                        🏦 <strong>Banking Connect (Net Banking)</strong>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="Counter Cash"> 
                        💵 <strong>Cash Payment at Canteen Counter</strong>
                    </label>

                    <!-- Mock QR Display Box -->
                    <div style="background:white; border:1px dashed #cbd5e1; padding:15px; text-align:center; border-radius:6px; margin-top:15px;">
                        <p style="margin:0 0 10px 0; font-size:13px; font-weight:bold; color:#555;">Scan QR Code to Pay via UPI</p>
                        <div style="background:#eee; width:130px; height:130px; margin:0 auto; display:flex; align-items:center; justify-content:center; border:1px solid #ccc; font-size:11px; color:#666;">
                            [QR CODE IMAGE]
                        </div>
                        <p style="margin:8px 0 0 0; font-size:11px; color:#666;">VPA: pkrfoodcourt@sbi</p>
                    </div>
                </div>

                <div class="gateway-right">
                    <div class="summary-title">Order Summary</div>
                    <div class="summary-row">
                        <span>Order No:</span>
                        <strong>PKR-<?= rand(10000, 99999) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Item:</span>
                        <strong><?= htmlspecialchars($selected_item['item_name']) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Merchant:</span>
                        <span>PKR ARTS COLLEGE</span>
                    </div>
                    <div class="summary-row">
                        <span>Amount:</span>
                        <span>₹<?= number_format($selected_item['price'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Processing Fee:</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Payable:</span>
                        <span style="color:#003366;">₹<?= number_format($selected_item['price'], 2) ?></span>
                    </div>

                    <button type="submit" name="confirm_payment" class="btn-pay">Pay Now & Generate Token</button>
                    <a href="student_portal.php" style="display:block; text-align:center; margin-top:10px; font-size:12px; color:#dc2626; text-decoration:none; font-weight:600;">Cancel Transaction</a>
                </div>
            </div>
        </form>

    <?php elseif ($step == 'receipt' && isset($success_token)): ?>
        <!-- DIGITAL BILL RECEIPT & TOKEN CONFIRMATION -->
        <div style="text-align:center; background:#f0fdf4; border:1px solid #bbf7d0; padding:30px; border-radius:8px;">
            <h3 style="color:#166534; margin-top:0;">✅ Payment Successful & Token Generated!</h3>
            <p style="font-size:15px; color:#333;">Your order has been sent to the kitchen admin.</p>
            
            <div style="background:white; max-width:350px; margin:20px auto; padding:20px; border-radius:8px; border:1px solid #cbd5e1; text-align:left;">
                <h4 style="text-align:center; color:#003366; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">PKR FOOD COURT RECEIPT</h4>
                <p><strong>Token Number:</strong> <span style="font-size:18px; color:#dc2626;"><?= $success_token ?></span></p>
                <p><strong>Register No:</strong> <?= $_SESSION['username'] ?></p>
                <p><strong>Student Name:</strong> <?= $_SESSION['full_name'] ?></p>
                <p><strong>Item Ordered:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
                <p><strong>Total Paid:</strong> ₹<?= number_format($item['price'], 2) ?></p>
                <p style="font-size:12px; color:#666;">Status: <strong style="color:orange;">Pending Kitchen Prep</strong></p>
            </div>

            <a href="student_portal.php" class="btn" style="text-decoration:none; display:inline-block;">Back to Menu Portal</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
