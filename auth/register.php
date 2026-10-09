<?php
require_once __DIR__ . '/guest_check.php';
require_once __DIR__ . '/../classes/User.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username cannot exceed 50 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        try {
            User::register($username, $email, $password);
            header('Location: login.php?registered=1');
            exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Recipe Share</title>
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
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Join Recipe Share</h1>
            <p class="text-sm text-stone-500 mt-1">Discover, cook, and share authentic recipes</p>
        </div>

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

        <!-- Registration Form -->
        <form method="POST" action="register.php" class="space-y-4">
            <div>
                <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-stone-400">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" required minlength="3" maxlength="50"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="johndoe"
                           class="w-full pl-10 pr-3 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c54e26] focus:border-transparent transition">
                </div>
                <p class="text-[11px] text-stone-400 mt-1">At least 3 characters</p>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-stone-400">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required maxlength="100"
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
                    <input type="password" id="password" name="password" required minlength="6"
                           placeholder="••••••••"
                           class="w-full pl-10 pr-10 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c54e26] focus:border-transparent transition">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 hover:text-stone-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye text-sm" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <p class="text-[11px] text-stone-400 mt-1">Minimum 6 characters</p>
            </div>

            <button type="submit" 
                    class="w-full mt-2 bg-[#c54e26] hover:bg-[#a83c1b] text-white font-medium py-3 px-4 rounded-xl shadow-md shadow-[#c54e26]/20 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#c54e26] flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-user-plus text-sm"></i>
                <span>Create Account</span>
            </button>
        </form>

        <!-- Footer Switch -->
        <p class="mt-6 text-center text-sm text-stone-500">
            Already have an account? 
            <a href="login.php" class="text-[#c54e26] hover:text-[#a83c1b] font-semibold hover:underline">
                Sign in here
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
