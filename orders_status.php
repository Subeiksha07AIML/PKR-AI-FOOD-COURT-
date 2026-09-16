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

$register_number = $_SESSION['register_number'];

// Fetch orders for this student
$stmt = $conn->prepare("SELECT * FROM orders WHERE register_number = ? ORDER BY order_id DESC");
$stmt->bind_param("s", $register_number);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - My Orders & Tokens</title>
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
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">MY ORDER TOKENS & HISTORY</h1>
                    <p class="text-xs text-slate-500 font-medium">Student: <span class="text-slate-700 font-bold"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span> (<?php echo htmlspecialchars($register_number); ?>)</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="menu.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">← Back to Menu</a>
                <a href="feedback.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Feedback</a>
                <a href="logout.php" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition">Log Out</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide">Your Order History</h2>
                    <p class="text-xs text-slate-500">Track active tokens and view past receipts.</p>
                </div>
                <span class="text-xs text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-200 font-medium">Total Orders: <?php echo count($orders); ?></span>
            </div>

            <!-- Orders Table Container -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="p-4">Token #</th>
                                <th class="p-4">Order Date & Time</th>
                                <th class="p-4">Total Amount</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="p-4 font-black text-blue-900 text-sm">#<?php echo htmlspecialchars($ord['token_number']); ?></td>
                                        <td class="p-4 text-slate-600"><?php echo date('d M Y, h:i A', strtotime($ord['order_timestamp'])); ?></td>
                                        <td class="p-4 font-bold text-slate-800">₹<?php echo number_format($ord['total_amount'], 2); ?></td>
                                        <td class="p-4">
                                            <?php 
                                                $status = $ord['order_status'];
                                                $badge_color = "bg-amber-50 text-amber-700 border-amber-200";
                                                if ($status == 'Ready') $badge_color = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                                if ($status == 'Completed') $badge_color = "bg-blue-50 text-blue-700 border-blue-200";
                                            ?>
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?php echo $badge_color; ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="receipt.php?id=<?php echo $ord['order_id']; ?>" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition inline-block">
                                                View Receipt 📄
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-slate-400 font-medium">
                                        You haven't placed any food orders yet. Go to the menu to order!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Simple Footer -->
    <div class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-400 mt-10">
        P.K.R. Arts College for Women - Food Court Management System &copy; 2026
    </div>

</body>
</html>
