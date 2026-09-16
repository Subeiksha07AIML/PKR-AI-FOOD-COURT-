<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registerNumber = trim($_POST['register_number']);
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $dob = $_POST['dob'];

    // Check if students table exists, if not create it
    $conn->query("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        register_number VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        dob DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->prepare("INSERT INTO students (register_number, name, department, dob) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $registerNumber, $name, $department, $dob);

    try {
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please log in with your credentials.'); window.location.href='login.php';</script>";
        }
    } catch (mysqli_sql_exception $e) {
        // Error code 1062 handles duplicate entries
        if ($e->getCode() == 1062) {
            echo "<script>alert('This Register Number is already registered! Please log in directly.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    }

    $stmt->close();
}
$conn->close();
?>
