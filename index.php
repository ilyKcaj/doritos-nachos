<?php
session_start();

// Debugging settings - probably shouldn't be on in production lol
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Grab error messages from session (if any)
$errors = [
    'login' => isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '',
    'register' => isset($_SESSION['register_error']) ? $_SESSION['register_error'] : ''
];

// Keep track of which form was last active (so page reload doesn't reset it)
$activeForm = isset($_SESSION['active_form']) ? $_SESSION['active_form'] : 'login';

// Store success messages if available
$registerSuccessMessage = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : '';
$logoutSuccessMessage = isset($_SESSION['logout_success']) ? $_SESSION['logout_success'] : '';

// Clear messages after use so they don't persist forever
unset($_SESSION['register_success']);
unset($_SESSION['logout_success']);
unset($_SESSION['login_error']);
unset($_SESSION['register_error']);

// Function to return error messages (if any)
function showError($error) {
    return $error ? "<p class='error-message'>$error</p>" : '';
}

// Function to check if form is active (used for CSS)
function isActiveForm($formName, $activeForm) {
    return ($formName === $activeForm) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCoin</title>
    <link rel="stylesheet" href="index.css?v=<?= time(); ?>">
    <link rel="icon" type="image/x-icon" href="logo.PNG">
</head>

<body>

    <div class="container">
        <!-- Login Form -->
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <img src="logo.PNG">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <?php if ($registerSuccessMessage): ?>
                    <p class="success-message"><?= htmlspecialchars($registerSuccessMessage); ?></p>
                <?php endif; ?>
                <?php if ($logoutSuccessMessage): ?>
                    <p class="success-message"><?= htmlspecialchars($logoutSuccessMessage); ?></p>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <img src="logo.PNG">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <script>
        // Function to toggle between forms (login/register)
        function showForm(formId) {
            document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
            document.getElementById(formId).classList.add("active");
        }
    </script>
</body>

</html>