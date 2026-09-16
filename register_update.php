<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $register_number = trim($_POST['register_number']);
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    // Check if register number already exists
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE register_number = ?");
    $checkStmt->bind_param("s", $register_number);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        // Redirect back to login with an error query parameter
        header("Location: index.php?error=exists");
        exit();
    }
    $checkStmt->close();

    // Insert new student account
    $stmt = $conn->prepare("INSERT INTO students (register_number, name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $register_number, $name, $password);
    
    if ($stmt->execute()) {
        // Automatically log them in upon successful registration
        $_SESSION['student_logged_in'] = true;
        $_SESSION['register_number'] = $register_number;
        $_SESSION['student_name'] = $name;
        
        $stmt->close();
        $conn->close();
        
        header("Location: menu.php");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: index.php?error=failed");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
