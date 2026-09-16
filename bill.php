<?php
session_start();

// Redirect to login if not authenticated as a student
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";
$assigned_token = "";

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $register_number = $_SESSION['register_number'];
    $student_name = $_SESSION['student_name'];
    $quantities = $_POST['quantity'] ?? [];
    
    $total_amount = 0;
    $ordered_items_summary = [];

    // Calculate total and validate quantities
    foreach ($quantities as $item_id => $qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            $stmt = $conn->prepare("SELECT name, price FROM menu_items WHERE id = ? AND available = 1");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $subtotal = $row['price'] * $qty;
                $total_amount += $subtotal;
            }
            $stmt->close();
        }
    }

    if ($total_amount > 0) {
        // Generate a new token number (find max token + 1, or start at 101)
        $token_result = $conn->query("SELECT MAX(token_number) as max_tok FROM orders");
        $token_row = $token_result->fetch_assoc();
        $assigned_token = ($token_row['max_tok'] ? intval($token_row['max_tok']) + 1 : 101);

        // Insert into orders table
        $order_stmt = $conn->prepare("INSERT INTO orders (token_number, register_number, student_name, total_amount, order_status) VALUES (?, ?, ?, ?, 'Preparing')");
        $order_stmt->bind_param("issd", $assigned_token, $register_number, $student_name, $total_amount);
        
        if ($order_stmt->execute()) {
            $success_message = "Order placed successfully! Your Token Number is #" . $assigned_token;
        } else {
            $error_message = "Failed to place order. Please try again.";
        }
        $order_stmt->close();
    } else {
        $error_message = "Please select at least one food item to order.";
    }
}

// Fetch available menu items
$menu_result = $conn->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY id DESC");
$menu_items = [];
if ($menu_result && $menu_result->num_rows > 0) {
    while($row = $menu_result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Student Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Roboto', sans-serif; background-color: #f4f6f9; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col justify-between">

    <div>
        <!-- Top Navigation Header -->
        <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full border-2 border-slate-300 flex items-center justify-center bg-slate-50 text-[9px] font-bold text-slate-600 p-1">
                    PKR LOGO
                </div>
                <div>
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">PKR FOOD COURT PORTAL</h1>
                    <p class="text-xs text-slate-500 font-medium">Welcome, <span class="text-slate-700 font-bold"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span> (<?php echo htmlspecialchars($_SESSION['register_number']); ?>)</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="order_history.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">My Tokens & History</a>
                <a href="chat_support.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Support Chat</a>
                <a href="logout.php" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition">Log Out</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-5xl mx-auto p-6 space-y-6">
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm font-bold flex justify-between items-center shadow-sm">
                    <span>🎉 <?php echo htmlspecialchars($success_message); ?></span>
                    <a href="order_history.php" class="text-xs bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition">View Token Status</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs font-bold">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide">Today's Fresh Menu</h2>
                    <p class="text-xs text-slate-500">Select quantities and place your order instantly.</p>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 font-medium">Canteen Open & Active</span>
            </div>

            <!-- Ordering Form & Grid -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="place_order" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (count($menu_items) > 0): ?>
                        <?php foreach ($menu_items as $item): ?>
                            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between space-y-4">
                                <div class="space-y-1">
                                    <div class="flex justify-between items-start">
                                        <h3 class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <span class="font-black text-blue-600 text-sm">₹<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>

                                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                    <span class="text-xs font-semibold text-slate-600">Quantity:</span>
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" min="0" value="0" class="w-20 bg-slate-50 border border-slate-300 rounded-lg p-2 text-center text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 bg-white border border-slate-200 rounded-xl p-12 text-center space-y-2">
                            <p class="text-slate-400 text-sm font-medium">No menu items are currently available.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($menu_items) > 0): ?>
                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex justify-between items-center">
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Ready to submit your food order?</p>
                            <p class="text-sm font-bold text-blue-900">Tokens will be sent directly to the kitchen dashboard.</p>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Place Order Now 🚀
                        </button>
                    </div>
                <?php endif; ?>
            </form>

        </div>
    </div>

    <!-- Simple Footer -->
    <div class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-400 mt-10">
        P.K.R. Arts College for Women - Food Court Management System &copy; 2026
    </div>

</body>
</html>
