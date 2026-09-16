<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle adding new menu items with stock and timing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $max_quantity = $_POST['max_quantity'];
    $available_from = !empty($_POST['available_from']) ? $_POST['available_from'] : NULL;
    $available_to = !empty($_POST['available_to']) ? $_POST['available_to'] : NULL;

    $stmt = $conn->prepare("INSERT INTO menu_items (item_name, category, price, max_quantity, current_orders, available_from, available_to) VALUES (?, ?, ?, ?, 0, ?, ?)");
    $stmt->bind_param("ssdiss", $item_name, $category, $price, $max_quantity, $available_from, $available_to);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_menu.php");
    exit();
}

// Handle resetting stock or deleting item
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'reset') {
        $conn->query("UPDATE menu_items SET current_orders = 0 WHERE id = $id");
    } elseif ($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM menu_items WHERE id = $id");
    }
    header("Location: admin_menu.php");
    exit();
}

$menu_result = $conn->query("SELECT * FROM menu_items ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Admin Menu & Stock Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-4 md:p-8">
    
    <header class="max-w-7xl mx-auto bg-slate-900 border border-slate-800 p-6 rounded-2xl flex justify-between items-center mb-8 shadow-2xl">
        <div class="flex items-center space-x-4">
            <img src="pkr_logo.png" alt="Logo" class="w-12 h-12 bg-white rounded-xl p-1 object-contain" onerror="this.src='https://via.placeholder.com/50?text=PKR'">
            <div>
                <h1 class="font-extrabold text-base text-white">PKR Canteen - Menu & Stock Automation</h1>
                <p class="text-xs text-blue-400 font-semibold">Manage Daily/Special items, 20-item limits, & time slots</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="admin.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition">← Back to Kitchen Orders</a>
            <a href="logout.php" class="px-4 py-2 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white text-xs font-bold rounded-xl transition">Logout</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add Menu Form -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4 h-fit">
            <h2 class="font-extrabold text-white text-sm border-b border-slate-800 pb-3">➕ Add New Canteen Item</h2>
            <form method="POST" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Item Name</label>
                    <input type="text" name="item_name" required placeholder="e.g. Fresh Mango Juice" class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Category</label>
                    <select name="category" class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                        <option value="Daily">Daily Menu</option>
                        <option value="Special">Special Timed Menu</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Price (₹)</label>
                        <input type="number" step="0.01" name="price" required placeholder="15" class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Max Limit (Stock)</label>
                        <input type="number" name="max_quantity" value="20" required class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Available From (Optional)</label>
                        <input type="time" name="available_from" class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Available To (Optional)</label>
                        <input type="time" name="available_to" class="w-full p-3 text-xs bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit" name="add_item" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl transition shadow-lg shadow-blue-600/20">Add Item to Menu</button>
            </form>
        </div>

        <!-- Menu Stock Management Table -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
            <h2 class="font-extrabold text-white text-sm border-b border-slate-800 pb-3">📦 Live Stock & Automated Limit Status (Limit: 20 Items)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-950 text-[10px] font-extrabold text-slate-400 uppercase border-b border-slate-800">
                            <th class="py-3 px-3">Item Name</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Price</th>
                            <th class="py-3 px-3">Stock Left</th>
                            <th class="py-3 px-3">Time Window</th>
                            <th class="py-3 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-xs">
                        <?php
                        if ($menu_result->num_rows > 0) {
                            while($row = $menu_result->fetch_assoc()) {
                                $left = $row['max_quantity'] - $row['current_orders'];
                                $stockBadge = $left > 0 ? "<span class='text-emerald-400 font-bold'>$left left</span>" : "<span class='text-rose-400 font-extrabold'>OUT OF STOCK</span>";
                                
                                echo "<tr class='hover:bg-slate-800/50'>";
                                echo "<td class='py-3 px-3 font-bold text-white'>" . htmlspecialchars($row['item_name']) . "</td>";
                                echo "<td class='py-3 px-3'><span class='px-2 py-0.5 rounded-full text-[10px] font-bold " . ($row['category']=='Special'?'bg-amber-500/20 text-amber-300':'bg-blue-500/20 text-blue-300') . "'>" . $row['category'] . "</span></td>";
                                echo "<td class='py-3 px-3 font-extrabold text-white'>₹" . $row['price'] . "</td>";
                                echo "<td class='py-3 px-3'>" . $stockBadge . "</td>";
                                echo "<td class='py-3 px-3 text-[11px] text-slate-400'>" . ($row['available_from'] ? $row['available_from']." - ".$row['available_to'] : "All Day") . "</td>";
                                echo "<td class='py-3 px-3 space-x-2'>
                                        <a href='admin_menu.php?action=reset&id=" . $row['id'] . "' class='px-2 py-1 bg-slate-800 hover:bg-slate-700 text-blue-400 rounded-lg text-[10px] font-bold'>Reset Stock</a>
                                        <a href='admin_menu.php?action=delete&id=" . $row['id'] . "' class='px-2 py-1 bg-rose-600/20 hover:bg-rose-600 text-rose-300 rounded-lg text-[10px] font-bold'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='py-6 text-center text-slate-500'>No menu items created yet.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</body>
</html>
