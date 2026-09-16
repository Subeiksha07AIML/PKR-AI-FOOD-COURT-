<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registerNumber = trim($_POST['registerNumber']);
    $dob = $_POST['dob'];

    $stmt = $conn->prepare("SELECT name, register_number, department FROM students WHERE register_number = ? AND dob = ?");
    $stmt->bind_param("ss", $registerNumber, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
        $_SESSION['student_logged'] = true;
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['register_number'] = $student['register_number'];
        $_SESSION['department'] = $student['department'];

        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
