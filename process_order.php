<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'pkr_food_court');
if ($conn->connect_error) { 
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]); 
    exit(); 
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['cart']) || empty($data['cart'])) { 
    echo json_encode(["status" => "error", "message" => "Cart is empty"]); 
    exit(); 
}

$cart = $data['cart'];
$total = 0;
foreach($cart as $item) { 
    $total += floatval($item['price']); 
}

$tokenNumber = '#' . rand(10, 99);
$prepMinutes = 15;

date_default_timezone_set('Asia/Kolkata');
$readyTimeIST = date('H:i:s', strtotime("+$prepMinutes minutes"));

$stmt = $conn->prepare("INSERT INTO orders (token_number, total_amount, prep_duration_minutes, ready_time_ist, order_status) VALUES (?, ?, ?, ?, 'Preparing')");
$stmt->bind_param("sdis", $tokenNumber, $total, $prepMinutes, $readyTimeIST);

if($stmt->execute()) {
    $orderId = $stmt->insert_id;
    
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, item_price) VALUES (?, ?, ?)");
    foreach($cart as $item) {
        $itemStmt->bind_param("isd", $orderId, $item['name'], $item['price']);
        $itemStmt->execute();
    }
    $itemStmt->close();
    
    echo json_encode(["status" => "success", "order_id" => $orderId]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save order"]);
}

$stmt->close();
$conn->close();
?>
