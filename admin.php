<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle Adding New Menu Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (!empty($name) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, available) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("ssd", $name, $description, $price);
        if ($stmt->execute()) {
            $message = "Menu item added successfully!";
        }
        $stmt->close();
    }
}

// Handle Availability Toggle
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $current_status = intval($_GET['toggle']);
    $new_status = ($current_status == 1) ? 0 : 1;

    $stmt = $conn->prepare("UPDATE menu_items SET available = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_menu.php");
    exit();
}

// Handle Delete Item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_menu.php");
    exit();
}

// Fetch all menu items
$menu_result = $conn->query("SELECT * FROM menu_items ORDER BY id DESC");
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
    <title>PKR Food Court - Menu Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Roboto', sans-serif; background-color: #f4f6f9; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col justify-between">

    <div>
        <!-- Top Header Bar -->
        <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full border-2 border-slate-300 flex items-center justify-center bg-slate-50 text-[9px] font-bold text-slate-600 p-1">
                    PKR LOGO
                </div>
                <div>
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">MENU CATALOG MANAGEMENT</h1>
                    <p class="text-xs text-slate-500 font-medium">P.K.R. Arts College for Women - Admin Portal</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">Kitchen Dashboard</a>
                <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Back to Login</a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="max-w-6xl mx-auto p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Add New Item Form -->
            <div class="space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4 sticky top-4">
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide border-b pb-2">Add New Food Item</h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 p-3 rounded-lg text-xs font-medium text-center">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-3 text-xs">
                        <input type="hidden" name="add_item" value="1">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-600">Item Name</label>
                            <input type="text" name="name" required placeholder="e.g. Masala Dosa" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-medium">
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-600">Description</label>
                            <textarea name="description" rows="2" placeholder="Brief food description..." class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-medium"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-600">Price (₹)</label>
                            <input type="number" step="0.01" name="price" required placeholder="e.g. 50.00" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-medium">
                        </div>
                        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow transition">Add Item to Menu</button>
                    </form>
                </div>
            </div>

            <!-- Existing Catalog List (Span 2) -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-blue-900 uppercase tracking-wide border-b pb-2">Current Menu Catalog</h2>

                    <div class="space-y-3">
                        <?php if (count($menu_items) > 0): ?>
                            <?php foreach ($menu_items as $item): ?>
                                <div class="border border-slate-200 rounded-lg p-4 flex justify-between items-center bg-slate-50/50">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <h3 class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                                <?php echo $item['available'] == 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>">
                                                <?php echo $item['available'] == 1 ? 'Available' : 'Out of Stock'; ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <p class="text-xs font-extrabold text-blue-600">₹<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="admin_menu.php?toggle=<?php echo $item['available']; ?>&id=<?php echo $item['id']; ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition 
                                            <?php echo $item['available'] == 1 ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'; ?>">
                                            <?php echo $item['available'] == 1 ? 'Mark Out of Stock' : 'Mark Available'; ?>
                                        </a>
                                        <a href="admin_menu.php?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this menu item?');" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-lg border border-rose-200 transition">
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 text-center py-8">No menu items found. Add your first item using the form!</p>
                        <?php endif; ?>
                    </div>
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
