<?php
session_start();

// Determine the base URL of your local project automatically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$portal_url = $protocol . "://" . $host . $path . "/index.php";

// Using a reliable public QR code generator API service
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($portal_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Table QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f6f9; }
        @media print {
            body { background-color: white; }
            .no-print { display: none; }
            .print-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col justify-between">

    <div class="no-print">
        <!-- Top Navigation Header -->
        <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full border-2 border-slate-300 flex items-center justify-center bg-slate-50 text-[9px] font-bold text-slate-600 p-1">
                    PKR LOGO
                </div>
                <div>
                    <h1 class="text-base font-bold text-blue-900 tracking-tight">QR CODE GENERATOR</h1>
                    <p class="text-xs text-slate-500 font-medium">P.K.R. Arts College for Women - Food Court</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="admin_dashboard.php" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-lg border border-blue-200 transition">Kitchen Dashboard</a>
                <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">Back to Login</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-md mx-auto p-6 my-auto w-full">
        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm text-center space-y-6 print-card">
            
            <div class="space-y-1">
                <h2 class="text-lg font-black text-blue-900 tracking-tight">P.K.R. FOOD COURT PORTAL</h2>
                <p class="text-xs text-slate-500 font-medium">Scan using your smartphone camera to order food</p>
            </div>

            <!-- QR Code Image Box -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl inline-block shadow-inner">
                <img src="<?php echo $qr_api_url; ?>" alt="Food Court QR Code" class="w-48 h-48 mx-auto rounded-lg">
            </div>

            <div class="space-y-1 bg-blue-50 border border-blue-100 p-3 rounded-xl">
                <p class="text-[11px] font-bold text-blue-900">Target Portal Link:</p>
                <p class="text-[10px] text-blue-600 font-mono break-all"><?php echo htmlspecialchars($portal_url); ?></p>
            </div>

            <!-- Print Button (Hidden during actual printing) -->
            <div class="no-print pt-2">
                <button onclick="window.print()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition">
                    🖨️ Print QR Code Card
                </button>
            </div>

        </div>
    </div>

    <!-- Simple Footer -->
    <div class="no-print bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-400 mt-10">
        P.K.R. Arts College for Women - Food Court Management System &copy; 2026
    </div>

</body>
</html>
