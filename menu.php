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

$success_msg = "";
$error_msg = "";

// Handle Direct Order Placement from Menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $register_number = $_SESSION['register_number'];
    $student_name = $_SESSION['student_name'];

    if ($quantity > 0) {
        // Fetch item details
        $stmt = $conn->prepare("SELECT item_name, price FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($item = $result->fetch_assoc()) {
            $total_amount = $item['price'] * $quantity;
            $token_number = rand(100, 999); // Generate a random 3-digit token number
            $order_status = "Preparing";

            // Insert into orders table
            $order_stmt = $conn->prepare("INSERT INTO orders (register_number, student_name, total_amount, token_number, order_status, order_timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
            $order_stmt->bind_param("ssdis", $register_number, $student_name, $total_amount, $token_number, $order_status);
            
            if ($order_stmt->execute()) {
                $new_order_id = $conn->insert_id;
                $success_msg = "Order placed successfully! Token Assigned: #" . $token_number;
            } else {
                $error_msg = "Failed to place order. Please try again.";
            }
            $order_stmt->close();
        }
        $stmt->close();
    } else {
        $error_msg = "Please select a valid quantity.";
    }
}

// Fetch menu items from database
$menu_result = $conn->query("SELECT * FROM menu_items");
$menu_items = [];
if ($menu_result) {
    while ($row = $menu_result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Menu & Ordering</title>
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
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">STUDENT FOOD COURT MENU</h1>
                    <p class="text-xs text-slate-500 font-medium">Welcome, <span class="text-slate-700 font-bold"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span> (<?php echo htmlspecialchars($_SESSION['register_number']); ?>)</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="order_history.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">My Orders & Tokens</a>
                <a href="feedback.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Feedback</a>
                <a href="logout.php" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition">Log Out</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            
            <?php if (!empty($success_msg)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold flex justify-between items-center">
                    <span>✅ <?php echo htmlspecialchars($success_msg); ?></span>
                    <a href="order_history.php" class="underline text-emerald-900">View Tokens →</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-xs font-bold">
                    ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div>
                <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide">Available Food Items</h2>
                <p class="text-xs text-slate-500">Select quantity and place your order instantly.</p>
            </div>

            <!-- Menu Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php if (count($menu_items) > 0): ?>
                    <?php foreach ($menu_items as $item): ?>
                        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-blue-900 text-sm"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <span class="text-sm font-black text-blue-600">₹<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($item['description'] ?? 'Freshly prepared at PKR Food Court.'); ?></p>
                            </div>

                            <form method="POST" class="space-y-3 pt-2 border-t border-slate-100">
                                <input type="hidden" name="place_order" value="1">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs font-semibold text-slate-600">Qty:</label>
                                    <input type="number" name="quantity" value="1" min="1" max="10" class="w-16 bg-slate-50 border border-slate-300 rounded-lg p-1.5 text-center text-xs font-bold">
                                </div>

                                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow transition">
                                    Order Now 🛒
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center py-12 bg-white rounded-xl border border-slate-200 text-slate-400 text-xs font-medium">
                        No food items currently available in the menu database.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Simple Footer -->
    <div class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-400 mt-10">
        P.K.R. Arts College for Women - Food Court Management System &copy; 2026
    </div>

</body>
</html>
