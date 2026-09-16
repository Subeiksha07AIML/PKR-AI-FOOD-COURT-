<?php
// ==========================================
// QUICK TOKEN STATUS LOOKUP (track_order.php)
// ==========================================
require 'db_connect.php';

// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

$order = null;
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_token'])) {
    $searchToken = trim($_POST['token_id']);
    
    if (!empty($searchToken)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE orderId = ?");
        $stmt->execute([$searchToken]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            $errorMsg = "Token not found. Please check your Order ID.";
        }
    } else {
        $errorMsg = "Please enter a valid Token ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Quick Token Status</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; margin: 40px; background: #f4f7f6; color: #333; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #b52a2a; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        input[type="text"] { width: 100%; padding: 10px; box-sizing: border-box; font-size: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; background: #d9534f; color: white; border: none; padding: 10px; font-size: 16px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #c9302c; }
        .error { color: red; text-align: center; font-size: 14px; margin-top: 10px; }
        .token-result-box { margin-top: 25px; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .token-result-box p { margin: 8px 0; font-size: 15px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; background: #ffc107; color: #333; }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #007bff; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>QUICK TOKEN STATUS LOOKUP</h2>
    
    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="token_id" placeholder="ENTER TOKEN (E.G. ORD1024)" required value="<?php echo htmlspecialchars($_POST['token_id'] ?? ''); ?>">
        </div>
        <button type="submit" name="check_token">Check Status</button>
    </form>

    <?php if(!empty($errorMsg)): ?>
        <p class="error"><?php echo $errorMsg; ?></p>
    <?php endif; ?>

    <?php if ($order): ?>
        <?php
        // Preparation time logic and Indian Standard Time calculations
        if ($order['orderStatus'] == 'Preparing') {
            if (empty($order['estimated_ready_time'])) {
                $orderTime = $order['createdAt']; 
                $prepMinutes = 15; // Standard estimated prep time in minutes
                
                $readyTimestamp = strtotime($orderTime) + ($prepMinutes * 60);
                $estimatedReadyTime = date('h:i:s A', $readyTimestamp);
            } else {
                $prepMinutes = $order['prep_minutes'];
                $estimatedReadyTime = date('h:i:s A', strtotime($order['estimated_ready_time']));
            }
        }
        ?>

        <div class="token-result-box">
            <p><strong>Token ID:</strong> <span style="color: #007bff;"><?php echo htmlspecialchars($order['orderId']); ?></span></p>
            
            <p><strong>Items Ordered:</strong> 
                <?php 
                $decodedItems = json_decode($order['items'], true);
                if (is_array($decodedItems)) {
                    $itemString = [];
                    foreach ($decodedItems as $it) {
                        $itemString[] = $it['itemName'] . " (x" . $it['quantity'] . ")";
                    }
                    echo implode(", ", $itemString);
                } else {
                    echo htmlspecialchars($order['items']);
                }
                ?>
            </p>

            <p><strong>Order Time:</strong> <?php echo date('h:i:s A', strtotime($order['createdAt'])); ?> (IST)</p>
            
            <?php if ($order['orderStatus'] == 'Preparing'): ?>
                <p style="color: #d9534f; font-weight: bold; background: #fdf7f7; padding: 8px; border-left: 4px solid #d9534f;">
                    Estimated Preparation Time: <?php echo $prepMinutes; ?> Minutes 
                    <br>Expected Ready At (IST): <?php echo $estimatedReadyTime; ?>
                </p>
            <?php endif; ?>
            
            <p style="margin-top: 12px;"><strong>Current Status:</strong> <span class="badge"><?php echo htmlspecialchars($order['orderStatus']); ?></span></p>
        </div>
    <?php endif; ?>

    <a href="index.php" class="back-link">Return to Login / Home</a>
</div>

</body>
</html>
