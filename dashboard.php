<?php
session_start();

// Check if student is logged in, otherwise redirect to login page
if (!isset($_SESSION['student_logged']) || $_SESSION['student_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$student_name = $_SESSION['student_name'];
$register_number = $_SESSION['register_number'];

// Database connection
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch menu items from database
$menu_result = $conn->query("SELECT * FROM menu");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>P.K.R. Arts College for Women - Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Roboto', sans-serif; background-color: #f4f6f9; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col justify-between">

    <div>
        <!-- College Header Section -->
        <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-full border-2 border-slate-300 flex items-center justify-center bg-slate-50 text-[10px] font-bold text-center text-slate-600 p-1">
                    PKR LOGO
                </div>
                <div>
                    <h1 class="text-lg font-bold text-blue-900 tracking-tight">P.K.R. ARTS COLLEGE FOR WOMEN</h1>
                    <p class="text-xs text-slate-500 font-medium">Autonomous Institution - Affiliated to Bharathiar University</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($student_name); ?></p>
                <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($register_number); ?></p>
                <a href="logout.php" class="text-[11px] text-rose-600 hover:underline font-bold">Logout</a>
            </div>
        </div>

        <!-- Blue Navigation Bar -->
        <div class="bg-[#2563eb] text-white px-6 py-3 flex items-center justify-between shadow-md">
            <span class="font-bold tracking-wider text-sm">CAMPUS FOOD COURT DASHBOARD</span>
            <div class="space-x-4 text-xs font-bold">
                <a href="dashboard.php" class="hover:underline">Menu</a>
                <a href="cart.php" class="hover:underline">Cart</a>
            </div>
        </div>

        <!-- Main Content Container -->
        <div class="max-w-4xl mx-auto p-6 space-y-6 mt-4">

            <!-- Quick Token Status Lookup Check Point (Available on every page) -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-blue-900 uppercase border-b pb-2">Quick Token Status Lookup</h3>
                <form method="POST" class="space-y-3" autocomplete="off">
                    <div class="flex space-x-2">
                        <input type="text" name="global_token_number" placeholder="Enter Token (e.g. PKR-102)" required class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 uppercase focus:outline-none focus:border-blue-600">
                        <button type="submit" name="check_global_token" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition">Check</button>
                    </div>
                </form>

                <?php 
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_global_token'])) {
                    $g_token = trim($_POST['global_token_number']);
                    $g_stmt = $conn->prepare("SELECT * FROM orders WHERE token_number = ? ORDER BY id DESC LIMIT 1");
                    $g_stmt->bind_param("s", $g_token);
                    $g_stmt->execute();
                    $g_res = $g_stmt->get_result();
                    
                    echo '<div class="bg-slate-50 border border-slate-200 p-3 rounded-lg space-y-1 text-xs mt-3">';
                    if ($g_res->num_rows > 0) {
                        $row = $g_res->fetch_assoc();
                        echo '<div class="flex justify-between font-bold">';
                        echo '<span>Token: <span class="text-blue-600">' . htmlspecialchars($row['token_number']) . '</span></span>';
                        echo '<span class="px-2 py-0.5 rounded text-[10px] ';
                        if($row['status'] == 'Preparing') echo 'bg-amber-100 text-amber-800';
                        elseif($row['status'] == 'Ready') echo 'bg-blue-100 text-blue-800';
                        else echo 'bg-emerald-100 text-emerald-800';
                        echo '">' . htmlspecialchars($row['status']) . '</span>';
                        echo '</div>';
                        echo '<p class="text-slate-600 pt-1">Items: <strong class="text-slate-800">' . htmlspecialchars($row['item_name']) . '</strong></p>';
                        if (!empty($row['created_at'])) {
                            echo '<p class="text-slate-500 text-[11px]">Order Time: <strong class="text-slate-700">' . date('h:i:s A', strtotime($row['created_at'])) . '</strong></p>';
                        }
                        if ($row['status'] == 'Ready') {
                            echo '<p class="text-[11px] text-emerald-600 font-bold pt-1">🎉 Food is ready at the counter!</p>';
                        }
                    } else {
                        echo '<p class="text-rose-600 text-center font-bold">No order found for token "' . htmlspecialchars($g_token) . '"</p>';
                    }
                    echo '</div>';
                    $g_stmt->close();
                }
                ?>
            </div>

            <!-- Menu Catalog Header -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-blue-900 uppercase border-b pb-2">Available Food Items</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if ($menu_result && $menu_result->num_rows > 0): ?>
                        <?php while($item = $menu_result->fetch_assoc()): ?>
                            <div class="border border-slate-200 p-4 rounded-lg flex justify-between items-center bg-slate-50">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="text-xs text-slate-500">₹<?php echo htmlspecialchars($item['price']); ?></p>
                                </div>
                                <a href="cart.php?add=<?php echo $item['id']; ?>" class="px-3 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition">Order</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-500">No food items available right now.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
