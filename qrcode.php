<?php
// Replace with your local network IP or localhost URL when presenting
$website_url = "http://localhost/student_authentication/login.php"; 
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($website_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Website QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-800 p-8 rounded-3xl shadow-2xl border border-slate-700 text-center max-w-sm w-full space-y-6">
        <div>
            <h1 class="text-lg font-extrabold text-white">PKR AI Food Court</h1>
            <p class="text-xs text-blue-400 mt-1 font-semibold">Scan QR Code to open Portal</p>
        </div>
        
        <div class="bg-white p-4 rounded-2xl inline-block shadow-inner">
            <img src="<?php echo $qr_api_url; ?>" alt="Website QR Code" class="w-48 h-48 mx-auto">
        </div>

        <p class="text-[11px] text-slate-400 break-all bg-slate-900 p-3 rounded-xl border border-slate-800">
            <?php echo $website_url; ?>
        </p>

        <a href="login.php" class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl transition">
            Proceed to Login Portal →
        </a>
    </div>
</body>
</html>
