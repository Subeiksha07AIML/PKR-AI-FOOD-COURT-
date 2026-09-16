<?php
session_start();

$conn = new mysqli("localhost", "root", "", "pkr_food_court");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = "";
$register_error = "";
$register_success = "";

// Handle Student Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_student'])) {
    $register_number = trim($_POST['register_number']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE register_number = ?");
    $stmt->bind_param("s", $register_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) { // Note: Use password_verify() if hashing passwords
            $_SESSION['student_logged_in'] = true;
            $_SESSION['register_number'] = $row['register_number'];
            $_SESSION['student_name'] = $row['student_name'];
            header("Location: menu.php");
            exit();
        } else {
            $login_error = "Invalid password. Please try again.";
        }
    } else {
        $login_error = "Register number not found. Please register first.";
    }
    $stmt->close();
}

// Handle Student Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_student'])) {
    $register_number = trim($_POST['reg_number']);
    $student_name = trim($_POST['student_name']);
    $password = trim($_POST['reg_password']);

    // Check if student already exists
    $check = $conn->prepare("SELECT id FROM students WHERE register_number = ?");
    $check->bind_param("s", $register_number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $register_error = "This register number is already registered. Please log in.";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (register_number, student_name, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $register_number, $student_name, $password);
        if ($stmt->execute()) {
            $register_success = "Registration successful! You can now log in.";
        } else {
            $register_error = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $check->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PKR Food Court - Student Portal</title>
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
                    <p class="text-xs text-slate-500 font-medium">Autonomous Institution - Gobichettipalayam</p>
                </div>
            </div>
            <div>
                <a href="admin_login.php" class="text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl border border-slate-300 transition">Kitchen Admin Login →</a>
            </div>
        </div>

        <!-- Blue Navigation Bar -->
        <div class="bg-blue-900 text-white px-6 py-3 flex items-center justify-between shadow-md">
            <span class="font-bold tracking-wider text-sm">FOOD COURT MANAGEMENT SYSTEM</span>
            <span class="text-xs bg-blue-800 px-3 py-1 rounded-lg font-medium">Student Portal</span>
        </div>

        <!-- Main Authentication Container -->
        <div class="max-w-md mx-auto p-6 mt-8">
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm space-y-6">
                
                <!-- Tabs Header -->
                <div class="flex border-b border-slate-200 pb-4 space-x-4">
                    <button onclick="switchTab('login')" id="btn-login" class="text-sm font-bold text-blue-900 border-b-2 border-blue-900 pb-1 transition">Student Login</button>
                    <button onclick="switchTab('register')" id="btn-register" class="text-sm font-bold text-slate-400 pb-1 transition">New Registration</button>
                </div>

                <!-- Login Form -->
                <div id="form-login" class="space-y-4">
                    <?php if (!empty($login_error)): ?>
                        <div class="bg-rose-50 border border-rose-200 text-rose-600 p-3 rounded-lg text-xs font-medium text-center">
                            ⚠️ <?php echo htmlspecialchars($login_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="login_student" value="1">
                        
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600">Register Number</label>
                            <input type="text" name="register_number" required placeholder="e.g. 23UCSC01" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-800 focus:outline-none focus:border-blue-600 font-medium">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600">Password</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-800 focus:outline-none focus:border-blue-600 font-medium">
                        </div>

                        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow transition">Login to Menu 🚀</button>
                    </form>
                </div>

                <!-- Registration Form -->
                <div id="form-register" class="space-y-4 hidden">
                    <?php if (!empty($register_error)): ?>
                        <div class="bg-rose-50 border border-rose-200 text-rose-600 p-3 rounded-lg text-xs font-medium text-center">
                            ⚠️ <?php echo htmlspecialchars($register_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($register_success)): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-3 rounded-lg text-xs font-medium text-center">
                            ✅ <?php echo htmlspecialchars($register_success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="register_student" value="1">
                        
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600">Register Number</label>
                            <input type="text" name="reg_number" required placeholder="e.g. 23UCSC02" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-800 focus:outline-none focus:border-blue-600 font-medium">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600">Full Name</label>
                            <input type="text" name="student_name" required placeholder="e.g. Priya Sundar" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-800 focus:outline-none focus:border-blue-600 font-medium">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-600">Password</label>
                            <input type="password" name="reg_password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-800 focus:outline-none focus:border-blue-600 font-medium">
                        </div>

                        <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white font-bold text-sm rounded-xl shadow transition">Create Account ✨</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Simple Footer -->
    <div class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-400 mt-10">
        P.K.R. Arts College for Women - Food Court Management System &copy; 2026
    </div>

    <script>
        function switchTab(tab) {
            const loginForm = document.getElementById('form-login');
            const regForm = document.getElementById('form-register');
            const btnLogin = document.getElementById('btn-login');
            const btnReg = document.getElementById('btn-register');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                regForm.classList.add('hidden');
                btnLogin.className = "text-sm font-bold text-blue-900 border-b-2 border-blue-900 pb-1 transition";
                btnReg.className = "text-sm font-bold text-slate-400 pb-1 transition";
            } else {
                loginForm.classList.add('hidden');
                regForm.classList.remove('hidden');
                btnReg.className = "text-sm font-bold text-blue-900 border-b-2 border-blue-900 pb-1 transition";
                btnLogin.className = "text-sm font-bold text-slate-400 pb-1 transition";
            }
        }
    </script>
</body>
</html>
