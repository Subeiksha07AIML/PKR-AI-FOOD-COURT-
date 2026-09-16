<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['student_logged'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate total from cart session
$total_amount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_type = $_POST['payment_type'];
    $student_name = $_SESSION['student_name'];
    $register_number = $_SESSION['register_number'];

    // Compile items
    $item_summary = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $details) {
            $item_summary[] = $details['name'] . " (x" . $details['quantity'] . ")";
        }
    }
    $item_name_string = implode(", ", $item_summary);
    $token_number = "PKR-" . rand(1000, 9999);
    $status = "Preparing";

    // Insert order into database
    $stmt = $conn->prepare("INSERT INTO orders (token_number, student_name, register_number, item_name, total_amount, payment_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssdss", $token_number, $student_name, $register_number, $item_name_string, $total_amount, $payment_type, $status);
        $stmt->execute();
        $stmt->close();
    }

    // Save token to session for bill retrieval
    $_SESSION['token_number'] = $token_number;
    
    // Clear cart
    unset($_SESSION['cart']);

    header("Location: bill.php");
    exit();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Secure Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #060913; }</style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-[#0f1523] border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-lg font-black text-white">Select Payment Method</h1>
            <p class="text-xs text-blue-400 font-medium">Total Payable Amount: <span class="font-bold text-emerald-400">₹<?php echo number_format($total_amount, 2); ?></span></p>
        </div>

        <form method="POST" class="space-y-4">
            <label class="block p-4 bg-[#161f33] border border-slate-700/80 rounded-2xl cursor-pointer hover:border-blue-500 transition">
                <div class="flex items-center space-x-3">
                    <input type="radio" name="payment_type" value="UPI / QR Code" required class="text-blue-600 focus:ring-0">
                    <div>
                        <p class="text-xs font-bold text-white">UPI / Google Pay / PhonePe</p>
                        <p class="text-[10px] text-slate-400">Instant scan & pay via campus gateway</p>
                    </div>
                </div>
            </label>

            <label class="block p-4 bg-[#161f33] border border-slate-700/80 rounded-2xl cursor-pointer hover:border-blue-500 transition">
                <div class="flex items-center space-x-3">
                    <input type="radio" name="payment_type" value="Debit / Credit Card" required class="text-blue-600 focus:ring-0">
                    <div>
                        <p class="text-xs font-bold text-white">Debit / Credit Card</p>
                        <p class="text-[10px] text-slate-400">Secure online card processing</p>
                    </div>
                </div>
            </label>

            <label class="block p-4 bg-[#161f33] border border-slate-700/80 rounded-2xl cursor-pointer hover:border-blue-500 transition">
                <div class="flex items-center space-x-3">
                    <input type="radio" name="payment_type" value="Cash at Counter" required class="text-blue-600 focus:ring-0">
                    <div>
                        <p class="text-xs font-bold text-white">Cash at Counter</p>
                        <p class="text-[10px] text-slate-400">Pay physically when collecting order</p>
                    </div>
                </div>
            </label>

            <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl transition shadow-lg shadow-blue-600/20">Confirm Payment & Generate Bill</button>
        </form>
        <div class="text-center">
            <a href="cart.php" class="text-[11px] text-slate-400 hover:text-white font-bold transition">← Return to Cart</a>
        </div>
    </div>
</body>
</html>
