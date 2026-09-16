<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Handle Order Status Update (e.g., Preparing -> Ready -> Completed)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['new_status']);
    
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all active and recent orders
$orders_result = $conn->query("SELECT * FROM orders ORDER BY order_id DESC");
$orders = [];
if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Kitchen Admin Dashboard</title>
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
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">KITCHEN ADMIN DASHBOARD</h1>
                    <p class="text-xs text-slate-500 font-medium">P.K.R. Arts College for Women - Food Court</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="generate_qr.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">Generate QR Code</a>
                <a href="index.php" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition">Log Out</a>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide">Live Student Orders</h2>
                    <p class="text-xs text-slate-500">Manage and update food preparation statuses for incoming student tokens.</p>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 font-medium">Auto-Sync Active</span>
            </div>

            <!-- Orders Table Container -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="p-4">Token #</th>
                                <th class="p-4">Register Number</th>
                                <th class="p-4">Student Name</th>
                                <th class="p-4">Amount</th>
                                <th class="p-4">Current Status</th>
                                <th class="p-4 text-center">Change Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="p-4 font-black text-blue-900 text-sm">#<?php echo htmlspecialchars($ord['token_number']); ?></td>
                                        <td class="p-4 font-mono text-slate-600"><?php echo htmlspecialchars($ord['register_number']); ?></td>
                                        <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($ord['student_name']); ?></td>
                                        <td class="p-4 font-bold text-slate-700">₹<?php echo number_format($ord['total_amount'], 2); ?></td>
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
                                            <form method="POST" class="inline-flex space-x-1">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                                                
                                                <select name="new_status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-slate-800 text-xs font-bold rounded-lg px-3 py-1.5 focus:outline-none focus:border-blue-600">
                                                    <option value="Preparing" <?php if($status=='Preparing') echo 'selected'; ?>>Preparing</option>
                                                    <option value="Ready" <?php if($status=='Ready') echo 'selected'; ?>>Ready</option>
                                                    <option value="Completed" <?php if($status=='Completed') echo 'selected'; ?>>Completed</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-slate-400 font-medium">
                                        No active food orders found at the moment.
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
