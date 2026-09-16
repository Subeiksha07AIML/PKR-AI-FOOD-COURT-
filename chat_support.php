<?php
session_start();

// Redirect to login if not authenticated as a student
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$register_number = $_SESSION['register_number'];
$student_name = $_SESSION['student_name'];
$success_msg = "";

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        // You can save this to a chat messages table if you'd like persistent history
        $success_msg = "Message sent successfully!";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Live Support Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Roboto', sans-serif; background-color: #1e293b; }</style>
</head>
<body class="text-slate-800 min-h-screen flex items-center justify-center p-4">

    <!-- Mobile-styled Chat Container matching the design UI -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl flex flex-col h-[650px] overflow-hidden border border-slate-200">
        
        <!-- Chat Top Header Bar -->
        <div class="bg-white px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <a href="menu.php" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition font-bold">
                ←
            </a>
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700">Support Online</span>
            </div>
            <div class="w-6 flex flex-col items-end space-y-1">
                <div class="w-5 h-0.5 bg-slate-800 rounded"></div>
                <div class="w-3.5 h-0.5 bg-slate-800 rounded"></div>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="flex-1 p-5 overflow-y-auto space-y-4 bg-slate-50/50">
            
            <!-- Support Agent Message 1 -->
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                    👤
                </div>
                <div class="bg-slate-100 text-slate-800 p-4 rounded-2xl rounded-tl-sm text-xs font-medium max-w-[75%] shadow-sm">
                    Hi, how can I help you?
                </div>
            </div>

            <!-- Student Outgoing Message 1 -->
            <div class="flex items-end justify-end space-x-3">
                <div class="bg-rose-500 text-white p-4 rounded-2xl rounded-tr-sm text-xs font-medium max-w-[75%] shadow-sm">
                    Hello, I ordered two fried chicken burgers. Can I know how much time it will take to arrive?
                </div>
                <div class="w-9 h-9 rounded-full bg-rose-100 border-2 border-rose-500 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden shadow-sm">
                    <span class="text-[10px]">👩‍🎓</span>
                </div>
            </div>

            <!-- Support Agent Reply 1 -->
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                    👤
                </div>
                <div class="bg-slate-100 text-slate-800 p-4 rounded-2xl rounded-tl-sm text-xs font-medium max-w-[75%] shadow-sm">
                    Ok, please let me check!
                </div>
            </div>

            <!-- Student Outgoing Message 2 -->
            <div class="flex items-end justify-end space-x-3">
                <div class="bg-rose-500 text-white p-3 rounded-2xl rounded-tr-sm text-xs font-medium max-w-[75%] shadow-sm">
                    Sure...
                </div>
                <div class="w-9 h-9 rounded-full bg-rose-100 border-2 border-rose-500 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden shadow-sm">
                    <span class="text-[10px]">👩‍🎓</span>
                </div>
            </div>

            <!-- Support Agent Reply 2 -->
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                    👤
                </div>
                <div class="space-y-1">
                    <div class="bg-slate-100 text-slate-800 p-4 rounded-2xl rounded-tl-sm text-xs font-medium max-w-[85%] shadow-sm">
                        It’ll get 25 minutes to arrive to your address
                    </div>
                    <p class="text-[10px] text-slate-400 pl-1">26 minutes ago</p>
                </div>
            </div>

            <!-- Student Outgoing Final -->
            <div class="flex items-end justify-end space-x-3">
                <div class="bg-rose-500 text-white p-4 rounded-2xl rounded-tr-sm text-xs font-medium max-w-[75%] shadow-sm">
                    Ok, thanks you for your support
                </div>
                <div class="w-9 h-9 rounded-full bg-rose-100 border-2 border-rose-500 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden shadow-sm">
                    <span class="text-[10px]">👩‍🎓</span>
                </div>
            </div>

        </div>

        <!-- Chat Input Form Footer -->
        <div class="p-4 bg-white border-t border-slate-100">
            <form method="POST" class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2 shadow-inner">
                <input type="text" name="message" required placeholder="Type here..." class="w-full bg-transparent text-xs text-slate-800 focus:outline-none py-2 font-medium">
                <button type="submit" class="w-10 h-10 bg-rose-500 hover:bg-rose-600 text-white rounded-xl flex items-center justify-center shadow transition flex-shrink-0">
                    ➤
                </button>
            </form>
        </div>

    </div>

</body>
</html>
