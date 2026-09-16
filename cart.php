<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize cart session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding items to cart
if (isset($_GET['add'])) {
    $item_id = intval($_GET['add']);
    
    // Fetch item details from database
    $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Add or increment item in cart session
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$item_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }
    }
    $stmt->close();
    header("Location: cart.php");
    exit();
}

// Handle clearing or placing order logic if needed
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>P.K.R. Arts College for Women - Shopping Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Roboto', sans-serif; background-color: #0f172a; color: #f8fafc; }</style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <div>
        <!-- Top Navigation Bar -->
        <div class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between shadow-sm">
            <a href="dashboard.php" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-lg transition">← Back to Menu</a>
            <h1 class="text-sm font-bold tracking-wider">Review Your Cart</h1>
            <a href="logout.php" class="text-xs text-rose-400 hover:underline font-bold">Logout</a>
        </div>

        <!-- Main Cart Container -->
        <div class="max-w-2xl mx-auto p-6 space-y-6 mt-6">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-blue-400 border-b border-slate-700 pb-2">🛒 Selected Food Items</h2>

                <?php if (!empty($_SESSION['cart'])): ?>
                    <div class="space-y-3">
                        <?php foreach ($_SESSION['cart'] as $id => $details): ?>
                            <div class="flex justify-between items-center bg-slate-900/50 p-3 rounded-lg border border-slate-700 text-xs">
                                <div>
                                    <h4 class="font-bold text-slate-200"><?php echo htmlspecialchars($details['name']); ?></h4>
                                    <p class="text-slate-400">₹<?php echo htmlspecialchars($details['price']); ?> × <?php echo $details['quantity']; ?></p>
                                </div>
                                <span class="font-bold text-emerald-400">₹<?php echo $details['price'] * $details['quantity']; ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="border-t border-slate-700 pt-3 flex justify-between items-center font-bold text-sm">
                            <span>Total Amount:</span>
                            <span class="text-emerald-400 text-base">₹<?php echo $total_amount; ?></span>
                        </div>

                        <form action="checkout.php" method="POST" class="pt-2">
                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition shadow">Proceed to Checkout & Generate Token</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 space-y-2">
                        <p class="text-xs text-slate-400">Your cart is currently empty.</p>
                        <a href="dashboard.php" class="inline-block text-xs text-blue-400 hover:underline font-bold">Go back to menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
