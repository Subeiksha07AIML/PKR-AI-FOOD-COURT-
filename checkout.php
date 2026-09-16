<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['cart_items']) || empty($_SESSION['cart_items'])) {
    header("Location: student_portal.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_items = $_SESSION['cart_items'];

// Calculate total dynamically by extracting prices from selected string or matching DB
$calculated_total = 0;
foreach ($selected_items as $item_str) {
    // Extract price from string format "Item Name - ₹XX"
    if (preg_match('/- ₹([\d\.]+)/', $item_str, $matches)) {
        $calculated_total += floatval($matches[1]);
    }
}

$success_token = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    $payment_method = $_POST['payment_method'];
    $order_details = implode(', ', $selected_items);
    $token_number = 'PKR-' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("INSERT INTO food_tokens (user_id, token_number, order_details, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $token_number, $order_details, $calculated_total, $payment_method]);
    
    unset($_SESSION['cart_items']); 
    
    // Get inserted token id for receipt
    $get_id = $pdo->prepare("SELECT token_id FROM food_tokens WHERE token_number = ? ORDER BY token_id DESC LIMIT 1");
    $get_id->execute([$token_number]);
    $success_token_id = $get_id->fetch()['token_id'];
    
    header("Location: receipt.php?token_id=" . $success_token_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Bill & Checkout</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f8; margin: 0; }
        header { background: #004080; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 750px; margin: 30px auto; padding: 20px; }
        .panel { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background: #e6f0fa; color: #004080; }
        .total-box { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; color: #004080; }
        .payment-options { display: flex; gap: 20px; margin: 20px 0; }
        .pay-card { flex: 1; border: 2px solid #ccc; padding: 15px; border-radius: 8px; cursor: pointer; text-align: center; background: #fafafa; }
        .pay-card input { margin-bottom: 8px; }
        .qr-container { display: none; text-align: center; margin: 15px 0; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px dashed #004080; }
        button { background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background: #218838; }
        .back-btn { background: #6c757d; display: inline-block; padding: 8px 15px; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; }
    </style>
    <script>
        function togglePayment(method) {
            document.getElementById('qr-box').style.display = (method === 'QR Code') ? 'block' : 'none';
        }
    </script>
</head>
<body>

<header>
    <h2>PKR Food Court Checkout</h2>
    <a href="student_portal.php" class="back-btn">← Back to Menu</a>
</header>

<div class="container">
    <div class="panel">
        <h3>Order Bill Summary</h3>
        <table>
            <tr>
                <th>Selected Item & Price</th>
            </tr>
            <?php foreach($selected_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <div class="total-box">
            Grand Total: ₹<?= number_format($calculated_total, 2) ?>
        </div>

        <form method="POST">
            <h4 style="margin-top: 25px; color: #333;">Select Payment Method</h4>
            <div class="payment-options">
                <label class="pay-card">
                    <input type="radio" name="payment_method" value="QR Code" onclick="togglePayment('QR Code')" required>
                    <div><b>QR Code (UPI)</b><br><small>Scan & Pay via GPay/PhonePe</small></div>
                </label>
                <label class="pay-card">
                    <input type="radio" name="payment_method" value="Cash on Delivery" onclick="togglePayment('Cash on Delivery')" checked>
                    <div><b>Cash on Delivery (COD)</b><br><small>Pay cash at canteen counter</small></div>
                </label>
            </div>

            <!-- QR Code Box -->
            <div id="qr-box" class="qr-container">
                <p style="margin: 0 0 10px 0; font-weight: bold; color: #004080;">Scan to Pay ₹<?= $calculated_total ?></p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=pkrfoodcourt@upi&am=<?= $calculated_total ?>&pn=PKRFoodCourt" alt="UPI QR Code">
                <p style="font-size: 12px; color: #666; margin: 10px 0 0 0;">UPI ID: <b>pkrfoodcourt@upi</b></p>
            </div>

            <button type="submit" name="confirm_order">Confirm & Place Order ➔</button>
        </form>
    </div>
</div>

</body>
</html>
