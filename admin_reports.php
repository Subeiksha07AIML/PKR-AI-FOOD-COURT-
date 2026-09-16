<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Key Metrics
// 1. Total Revenue from Completed or non-cancelled orders
$revenue_query = $conn->query("SELECT SUM(total_amount) as total_rev FROM orders WHERE order_status != 'Cancelled'");
$revenue_data = $revenue_query->fetch_assoc();
$total_revenue = $revenue_data['total_rev'] ?? 0;

// 2. Total Orders Count
$orders_count_query = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$orders_count_data = $orders_count_query->fetch_assoc();
$total_orders = $orders_count_data['total_orders'] ?? 0;

// 3. Status Breakdown Counts
$status_counts = [];
$status_res = $conn->query("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
while ($row = $status_res->fetch_assoc()) {
    $status_counts[$row['order_status']] = $row['count'];
}

// 4. Fetch All Orders for Reporting Table
$all_orders_res = $conn->query("SELECT * FROM orders ORDER BY order_id DESC");
$all_orders = [];
while ($row = $all_orders_res->fetch_assoc()) {
    $all_orders[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Sales & Reports</title>
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
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">SALES & ANALYTICS REPORTS</h1>
                    <p class="text-xs text-slate-500 font-medium">P.K.R. Arts College for Women - Admin Portal</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">Kitchen Dashboard</a>
                <a href="admin_menu.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Manage Menu</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            
            <!-- Summary Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Revenue -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue</p>
                    <p class="text-2xl font-black text-blue-600">₹<?php echo number_format($total_revenue, 2); ?></p>
                    <p class="text-[11px] text-emerald-600 font-medium">From active & completed orders</p>
                </div>

                <!-- Total Orders -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders Placed</p>
                    <p class="text-2xl font-black text-slate-800"><?php echo $total_orders; ?></p>
                    <p class="text-[11px] text-blue-600 font-medium">All-time database count</p>
                </div>

                <!-- Preparing Orders -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">In Kitchen (Preparing)</p>
                    <p class="text-2xl font-black text-amber-600"><?php echo $status_counts['Preparing'] ?? 0; ?></p>
                    <p class="text-[11px] text-amber-600 font-medium">Pending fulfillment</p>
                </div>

                <!-- Completed Orders -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Completed Orders</p>
                    <p class="text-2xl font-black text-emerald-600"><?php echo $status_counts['Completed'] ?? 0; ?></p>
                    <p class="text-[11px] text-emerald-600 font-medium">Successfully served</p>
                </div>
            </div>

            <!-- Detailed Transaction Ledger Table -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide">Complete Transaction History</h2>
                    <span class="text-xs text-slate-500">Showing all records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-4">Token #</th>
                                <th class="p-4">Student Name</th>
                                <th class="p-4">Register Number</th>
                                <th class="p-4">Amount</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($all_orders) > 0): ?>
                                <?php foreach ($all_orders as $ord): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="p-4 font-black text-blue-900">#<?php echo htmlspecialchars($ord['token_number']); ?></td>
                                        <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($ord['student_name']); ?></td>
                                        <td class="p-4 text-slate-600 font-medium"><?php echo htmlspecialchars($ord['register_number']); ?></td>
                                        <td class="p-4 font-extrabold text-blue-600">₹<?php echo number_format($ord['total_amount'], 2); ?></td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase 
                                                <?php 
                                                    if($ord['order_status'] == 'Preparing') echo 'bg-amber-100 text-amber-800';
                                                    elseif($ord['order_status'] == 'Ready') echo 'bg-blue-100 text-blue-800';
                                                    elseif($ord['order_status'] == 'Completed') echo 'bg-emerald-100 text-emerald-800';
                                                    else echo 'bg-rose-100 text-rose-800';
                                                ?>">
                                                <?php echo htmlspecialchars($ord['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-500"><?php echo date('d M Y, h:i A', strtotime($ord['order_timestamp'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium">No order transaction records found.</td>
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
