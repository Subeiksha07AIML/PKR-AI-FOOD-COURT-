<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_logged']) || $_SESSION['student_logged'] !== true) {
    echo json_encode([]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$reg_no = $_SESSION['register_number'];
$result = $conn->query("SELECT id, token_number, status, verification_pin FROM orders WHERE register_number = '$reg_no'");

$orders = [];
while($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
$conn->close();
?>
