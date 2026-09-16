<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI PKR Food Court - Student Registration</title>
    <!-- Tailwind CSS CDN for styling matching your UI components -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-50 via-purple-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="rounded-2xl border bg-card text-card-foreground shadow-xl transition-all bg-white/80 backdrop-blur-md p-8">
            
            <!-- Header Section -->
            <div class="flex flex-col space-y-2 text-center mb-6">
                <span class="text-3xl mx-auto">🍛</span>
                <h3 class="text-2xl font-semibold leading-none tracking-tight text-gray-800">AI PKR Food Court</h3>
                <p class="text-sm text-muted-foreground text-gray-500">
                    PKR Arts College for Women, Gobichettipalayam, Erode
                </p>
                <div class="mt-2 inline-block bg-purple-100 text-purple-700 text-xs font-medium px-2.5 py-0.5 rounded-full mx-auto">
                    Student Registration Portal
                </div>
            </div>

            <!-- Registration Form -->
            <form id="studentRegisterForm" class="space-y-4">
                
                <!-- Full Name -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-gray-700" for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        required
                        placeholder="Enter your full name"
                        class="flex h-10 w-full rounded-full border border-input bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-gray-300"
                    />
                </div>

                <!-- Register Number / Student ID -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-gray-700" for="registerNumber">Register Number / Roll No</label>
                    <input 
                        type="text" 
                        id="registerNumber" 
                        required
                        placeholder="e.g. 21BCA01"
                        class="flex h-10 w-full rounded-full border border-input bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 border-gray-300"
                    />
                </div>

                <!-- Department / Course -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-gray-700" for="department">Department</label>
                    <input 
                        type="text" 
                        id="department" 
                        required
                        placeholder="e.g. B.Sc Computer Science"
                        class="flex h-10 w-full rounded-full border border-input bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 border-gray-300"
                    />
                </div>

                <!-- Email Address -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-gray-700" for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        required
                        placeholder="student@pkrassistant.com"
                        class="flex h-10 w-full rounded-full border border-input bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 border-gray-300"
                    />
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-gray-700" for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        required
                        placeholder="Create a secure password"
                        class="flex h-10 w-full rounded-full border border-input bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 border-gray-300"
                    />
                </div>

                <!-- Error / Success Message Container -->
                <div id="formMessage" class="text-xs text-center font-medium hidden"></div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    id="submitBtn"
                    class="w-full h-10 rounded-full bg-purple-600 hover:bg-purple-700 text-white font-medium text-sm transition-colors shadow-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 mt-2"
                >
                    Register Account
                </button>
            </form>

            <!-- Footer Link -->
            <div class="mt-6 text-center text-xs text-gray-500">
                Already have an account? 
                <a href="/login" class="text-purple-600 font-medium hover:underline">Login here</a>
            </div>

        </div>
    </div>

    <!-- JavaScript Integration with your authAPI structure -->
    <script type="module">
        import { authAPI } from './src/lib/api.js'; // Adjust path based on your project directory

        const form = document.getElementById('studentRegisterForm');
        const messageDiv = document.getElementById('formMessage');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Collect form data matching typical backend expectations
            const formData = {
                name: document.getElementById('name').value,
                registerNumber: document.getElementById('registerNumber').value,
                department: document.getElementById('department').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                role: 'student',
                college: 'PKR Arts College for Women, Gobichettipalayam'
            };

            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';
            messageDiv.classList.add('hidden');

            try {
                // Utilizing your defined authAPI register method
                const response = await authAPI.register(formData);
                
                messageDiv.textContent = 'Registration successful! Redirecting to login...';
                messageDiv.className = 'text-xs text-center font-medium text-green-600 block';
                
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);

            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register Account';
                
                const errorMsg = error.response?.data?.error || 'Registration failed. Please check your details.';
                messageDiv.textContent = errorMsg;
                messageDiv.className = 'text-xs text-center font-medium text-red-600 block';
            }
        });
    </script>
</body>
</html>
