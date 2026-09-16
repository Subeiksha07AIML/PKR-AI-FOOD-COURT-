<?php
$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$status_result = null;
$searched_token = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_status'])) {
    $searched_token = trim($_POST['token_number']);
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE token_number = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $searched_token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $status_result = $res->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Quick Token Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #060913; }</style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-[#0f1523] border border-blue-500/40 rounded-3xl p-8 shadow-2xl space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 mx-auto flex items-center justify-center font-extrabold text-white text-lg shadow-lg">PKR</div>
            <h2 class="text-lg font-extrabold text-white">Live Token Status Checker</h2>
            <p class="text-xs text-slate-400">Enter your order token number below to check its live status instantly.</p>
        </div>

        <!-- Token Search Form -->
        <form method="POST" class="space-y-4">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-300">Token Number</label>
                <input type="text" name="token_number" value="<?php echo htmlspecialchars($searched_token); ?>" placeholder="e.g. PKR-102" required class="w-full bg-[#161f33] border border-slate-700 rounded-xl px-4 py-3 text-xs text-white uppercase placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <button type="submit" name="check_status" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-2xl shadow-lg transition">Check Status</button>
        </form>

        <!-- Search Result Box -->
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_status'])): ?>
            <div class="mt-6 pt-6 border-t border-slate-800 space-y-4">
                <?php if ($status_result): ?>
                    <div class="bg-[#161f33] border border-slate-700 p-5 rounded-2xl space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-400">Token: <strong class="text-blue-400"><?php echo htmlspecialchars($status_result['token_number']); ?></strong></span>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold 
                                <?php 
                                    if($status_result['status'] == 'Preparing') echo 'bg-amber-500/20 text-amber-300 border border-amber-500/30';
                                    elseif($status_result['status'] == 'Ready') echo 'bg-blue-500/20 text-blue-300 border border-blue-500/30';
                                    else echo 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                                ?>">
                                <?php echo htmlspecialchars($status_result['status']); ?>
                            </span>
                        </div>
                        <div class="text-xs space-y-1 text-slate-300">
                            <p><strong>Items:</strong> <?php echo htmlspecialchars($status_result['item_name']); ?></p>
                            <p><strong>Student Name:</strong> <?php echo htmlspecialchars($status_result['student_name']); ?></p>
                            <p><strong>Security PIN:</strong> <span class="text-emerald-400 font-extrabold"><?php echo htmlspecialchars($status_result['verification_pin']); ?></span></p>
                        </div>
                        <?php if($status_result['status'] == 'Ready'): ?>
                            <p class="text-[11px] text-emerald-400 font-extrabold animate-pulse text-center pt-2">🎉 Food is Ready! Please collect at the counter.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-rose-950/40 border border-rose-500/30 p-4 rounded-2xl text-center text-rose-300 text-xs font-bold">
                        No active order found for token "<?php echo htmlspecialchars($searched_token); ?>". Please check your token number.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation back to login or dashboard -->
        <div class="text-center pt-2">
            <a href="login.php" class="text-xs text-blue-400 hover:underline font-bold">← Back to Student Login</a>
        </div>

    </div>

</body>
</html>
