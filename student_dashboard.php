<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['registerNo'])) {
    header("Location: index.php");
    exit();
}

$registerNo = $_SESSION['registerNo'];
$successMsg = "";

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $selectedItems = $_POST['items'] ?? [];
    $quantities = $_POST['qty'] ?? [];
    
    if (!empty($selectedItems)) {
        $orderItems = [];
        $totalAmount = 0;
        
        foreach ($selectedItems as $itemId) {
            $qty = intval($quantities[$itemId]);
            if ($qty > 0) {
                // Fetch item details
                $stmt = $pdo->prepare("SELECT * FROM menu WHERE itemId = ?");
                $stmt->execute([$itemId]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($item) {
                    $subtotal = $item['price'] * $qty;
                    $totalAmount += $subtotal;
                    $orderItems[] = [
                        "itemId" => $item['itemId'],
                        "itemName" => $item['itemName'],
                        "quantity" => $qty,
                        "price" => $item['price']
                    ];
                }
            }
        }
        
        if (!empty($orderItems)) {
            $orderId = "ORD" . rand(1000, 9999);
            $otp = rand(1000, 9999); // Random 4-digit verification OTP
            $itemsJson = json_encode($orderItems);
            
            $insertStmt = $pdo->prepare("INSERT INTO orders (orderId, registerNo, items, totalAmount, orderStatus, verification_otp, createdAt) VALUES (?, ?, ?, ?, 'Pending', ?, NOW())");
            $insertStmt->execute([$orderId, $registerNo, $itemsJson, $totalAmount, $otp]);
            
            // Redirect straight to bill page
            header("Location: bill.php?orderId=$orderId");
            exit();
        } else {
            $successMsg = "Please select at least one item quantity.";
        }
    } else {
        $successMsg = "Please select items to order.";
    }
}

// Fetch Menu Items
$menuQuery = $pdo->query("SELECT * FROM menu WHERE isAvailable = 1");
$menuItems = $menuQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch Student's Active Orders for Live Tracking
$trackQuery = $pdo->prepare("SELECT * FROM orders WHERE registerNo = ? ORDER BY createdAt DESC LIMIT 3");
$trackQuery->execute([$registerNo]);
$myOrders = $trackQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - PKR AI Food Court</title>
    <meta http-equiv="refresh" content="10"> <!-- Auto refresh every 10s for live tracking update -->
    <style>
        body { font-family: 'Times New Roman', Times, serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { background: #28a745; color: white; border: none; padding: 10px 15px; cursor: pointer; font-size: 16px; border-radius: 4px; margin-top: 15px; }
        .tracker-box { background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin-top: 20px; border-radius: 5px; }
        .logout { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['registerNo']); ?></h3>
        <a href="index.php" class="logout">Logout</a>
    </div>

    <?php if($successMsg) echo "<p style='color: red;'>$successMsg</p>"; ?>

    <!-- LIVE ORDER TRACKING WIDGET (Visible on Student Page) -->
    <div class="tracker-box">
        <h4 style="margin: 0 0 10px 0; color: #856404;">Live Order Tracking & Verification OTP</h4>
        <?php if(empty($myOrders)): ?>
            <p style="margin:0;">No active orders found.</p>
        <?php else: ?>
            <?php foreach($myOrders as $ord): ?>
                <div style="background: white; padding: 8px; margin-bottom: 5px; border-radius: 4px; border: 1px solid #ddd; display: flex; justify-content: space-between;">
                    <span><strong>Order ID:</strong> <?php echo $ord['orderId']; ?> | <strong>Status:</strong> <span style="color: green; font-weight: bold;"><?php echo $ord['orderStatus']; ?></span></span>
                    <span>Pickup OTP: <strong style="color: #007bff;"><?php echo $ord['verification_otp']; ?></strong> | <a href="bill.php?orderId=<?php echo $ord['orderId']; ?>" target="_blank">View Bill</a></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MENU CATALOG & ORDER FORM -->
    <h3>Available Menu Items</h3>
    <form method="POST" action="">
        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Price (₹)</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($menuItems as $item): ?>
                <tr>
                    <td><input type="checkbox" name="items[]" value="<?php echo $item['itemId']; ?>"></td>
                    <td><?php echo htmlspecialchars($item['itemName']); ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>₹<?php echo htmlspecialchars($item['price']); ?></td>
                    <td><input type="number" name="qty[<?php echo $item['itemId']; ?>]" value="1" min="1" style="width: 60px;"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="place_order" class="btn">Place Order & Generate Bill</button>
    </form>
</div>

</body>
</html>
