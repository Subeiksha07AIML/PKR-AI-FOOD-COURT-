<?php
$widget_status_result = null;
$widget_searched_token = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_quick_token'])) {
    $widget_searched_token = trim($_POST['widget_token_number']);
    
    $w_conn = new mysqli("localhost", "root", "", "pkr_food_court");
    if (!$w_conn->connect_error) {
        $w_stmt = $w_conn->prepare("SELECT * FROM orders WHERE token_number = ? ORDER BY id DESC LIMIT 1");
        $w_stmt->bind_param("s", $widget_searched_token);
        $w_stmt->execute();
        $w_res = $w_stmt->get_result();
        if ($w_res->num_rows > 0) {
            $widget_status_result = $w_res->fetch_assoc();
        }
        $w_stmt->close();
        $w_conn->close();
    }
}
?>
<!-- Reusable Token Status Widget -->
<div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4 my-4">
    <h3 class="text-sm font-bold text-blue-900 uppercase border-b pb-2">Quick Token Status Lookup</h3>
    <form method="POST" class="space-y-3">
        <div class="flex space-x-2">
            <input type="text" name="widget_token_number" value="<?php echo htmlspecialchars($widget_searched_token); ?>" placeholder="Enter Token (e.g. PKR-958)" required class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 uppercase focus:outline-none focus:border-blue-600">
            <button type="submit" name="check_quick_token" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition">Check</button>
        </div>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_quick_token'])): ?>
        <div class="bg-slate-50 border border-slate-200 p-3 rounded-lg space-y-1.5 text-xs">
            <?php if ($widget_status_result): ?>
                <div class="flex justify-between font-bold items-center">
                    <span>Token: <span class="text-blue-600"><?php echo htmlspecialchars($widget_status_result['token_number']); ?></span></span>
                    <span class="px-2 py-0.5 rounded text-[10px] 
                        <?php 
                            if($widget_status_result['status'] == 'Preparing') echo 'bg-amber-100 text-amber-800';
                            elseif($widget_status_result['status'] == 'Ready') echo 'bg-blue-100 text-blue-800';
                            else echo 'bg-emerald-100 text-emerald-800';
                        ?>">
                        <?php echo htmlspecialchars($widget_status_result['status']); ?>
                    </span>
                </div>
                <p class="text-slate-600">Items: <strong class="text-slate-800"><?php echo htmlspecialchars($widget_status_result['item_name']); ?></strong></p>
                <p class="text-slate-500 text-[11px]">Order Time: <strong class="text-slate-700"><?php echo date('h:i:s A', strtotime($widget_status_result['created_at'])); ?></strong></p>
                <?php if($widget_status_result['status'] == 'Ready'): ?>
                    <p class="text-[11px] text-emerald-600 font-bold pt-1">🎉 Food is ready at the counter!</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-rose-600 text-center font-bold">No order found for token "<?php echo htmlspecialchars($widget_searched_token); ?>"</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
