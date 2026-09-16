<?php
session_start();
require_once 'db.php';

// Ensure user is logged in (either student/staff or admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$token_id = $_GET['token_id'] ?? null;
if (!$token_id) {
    die("Invalid Token ID specified.");
}

// Fetch order details along with student/staff information
$stmt = $pdo->prepare("SELECT ft.*, s.student_name AS full_name, s.register_number FROM food_tokens ft JOIN students s ON ft.user_id = s.id WHERE ft.token_id = ?");
$stmt->execute([$token_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order receipt not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Receipt #<?= htmlspecialchars($order['token_number']) ?></title>
    <style>
        body, button, input {
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            letter-spacing: -0.01em;
        }
        body { background: #e2e8f0; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .receipt-card { background: white; width: 380px; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #003366; }
        .receipt-header { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 15px; margin-bottom: 15px; }
        .receipt-header h2 { margin: 0; color: #003366; font-size: 20px; }
        .receipt-header p { margin: 3px 0; color: #64748b; font-size: 12px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #334155; }
        .divider { border-bottom: 1px dashed #cbd5e1; margin: 15px 0; }
        .items-section { margin-bottom: 15px; font-size: 13px; color: #1e293b; line-height: 1.6; }
        .total-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; color: #003366; margin-top: 10px; }
        .print-btn-container { text-align: center; margin-top: 20px; }
        .print-btn { background: #003366; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; width: 100%; }
        .print-btn:hover { background: #002244; }
        
        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; border: none; width: 100%; padding: 0; }
            .print-btn-container { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <div class="receipt-header">
        <h2>PKR Food Court</h2>
        <p>PKR Arts College for Women, Gobichettipalayam</p>
        <p>Official Canteen Token Receipt</p>
    </div>

    <div class="info-row">
        <span><b>Token Number:</b></span>
        <span style="font-size: 15px; font-weight: bold; color: #003366;"><?= htmlspecialchars($order['token_number']) ?></span>
    </div>
    <div class="info-row">
        <span><b>Name:</b></span>
        <span><?= htmlspecialchars($order['full_name']) ?></span>
    </div>
    <div class="info-row">
        <span><b>Register No / ID:</b></span>
        <span><?= htmlspecialchars($order['register_number']) ?></span>
    </div>
    <div class="info-row">
        <span><b>Payment Mode:</b></span>
        <span><?= htmlspecialchars($order['payment_method']) ?></span>
    </div>
    <div class="info-row">
        <span><b>Order Date:</b></span>
        <span><?= htmlspecialchars($order['created_at']) ?></span>
    </div>

    <div class="divider"></div>

    <div style="font-weight: 600; margin-bottom: 8px; font-size: 13px; color: #475569;">Ordered Items:</div>
    <div class="items-section">
        <?= nl2br(htmlspecialchars($order['order_details'])) ?>
    </div>

    <div class="divider"></div>

    <div class="total-row">
        <span>Grand Total:</span>
        <span>₹<?= htmlspecialchars($order['total_price']) ?></span>
    </div>

    <div class="receipt-header" style="border-bottom: none; border-top: 2px dashed #cbd5e1; margin-top: 20px; padding-top: 15px; padding-bottom: 0;">
        <p style="font-size: 11px;">Status: <b><?= htmlspecialchars($order['status']) ?></b></p>
        <p style="font-size: 11px; margin-top: 5px;">Thank you for dining at PKR Food Court!</p>
    </div>

    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
</div>

</body>
</html>
