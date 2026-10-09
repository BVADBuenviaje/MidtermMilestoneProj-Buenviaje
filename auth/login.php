<?php
require_once __DIR__ . '/guest_check.php';
require_once __DIR__ . '/../classes/User.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password cannot be empty.';
    }

    if (empty($errors)) {
        $user = User::login($email, $password);

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ../index.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Recipe Share</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        terracotta: {
                            50: '#fdf6f2',
                            100: '#f9ebe2',
                            200: '#f3d3c1',
                            500: '#d9643a',
                            600: '#c54e26',
                            700: '#a83c1b',
                            800: '#873218',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#fcf9f6] min-h-screen flex items-center justify-center p-4 antialiased text-stone-800">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl shadow-amber-950/5 border border-amber-100">
        <!-- Brand Header -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100/70 text-amber-700 mb-3 shadow-inner">
                <i class="fa-solid fa-utensils text-2xl text-[#c54e26]"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Welcome Back</h1>
            <p class="text-sm text-stone-500 mt-1">Sign in to your Recipe Share account</p>
        </div>

        <!-- Registration Success Notification -->
        <?php if (isset($_GET['registered'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl mb-5 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                <span>Registration successful! Please sign in with your credentials.</span>
            </div>
        <?php endif; ?>

        <!-- Errors Display -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50/80 border border-red-200 text-red-700 p-4 rounded-xl mb-5 text-sm space-y-1">
                <?php foreach ($errors as $error): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500 shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-stone-400">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="john@example.com"
                           class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c54e26] focus:border-transparent transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-stone-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="w-full pl-10 pr-10 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c54e26] focus:border-transparent transition">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 hover:text-stone-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye text-sm" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" 
                    class="w-full mt-2 bg-[#c54e26] hover:bg-[#a83c1b] text-white font-medium py-3 px-4 rounded-xl shadow-md shadow-[#c54e26]/20 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#c54e26] flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-right-to-bracket text-sm"></i>
                <span>Sign In</span>
            </button>
        </form>

        <!-- Footer Switch -->
        <p class="mt-6 text-center text-sm text-stone-500">
            Don't have an account yet? 
            <a href="register.php" class="text-[#c54e26] hover:text-[#a83c1b] font-semibold hover:underline">
                Create one now
            </a>
        </p>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('fa-eye');
                toggleIcon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
