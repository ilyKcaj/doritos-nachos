<?php
session_start();

// Enable error reporting for debugging (probably turn this off in production lol)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // database connection, assuming this exists

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        registerUser($conn);
    } else if (isset($_POST['login'])) { // using else-if instead of elseif, personal preference
        loginUser($conn);
    }
}

function registerUser($conn) {
    $name = trim($_POST['name']);  // remove extra spaces, just in case
    $email = trim($_POST['email']); // same here
    $password = $_POST['password']; // no need to trim passwords

    if (!$name || !$email || !$password) { // simpler way to check for empty fields
        $_SESSION['register_error'] = "All fields are required!";
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    // Check if email is already in use
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = "Email is already registered!";
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }
    $stmt->close();

    // Encrypt the password before storing (bcrypt is decent)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Account created! Please log in.";
        $_SESSION['active_form'] = 'login'; // switch to login form
        header("Location: index.php");
    } else {
        $_SESSION['register_error'] = "Registration failed. Try again!"; // vague error message, security reasons
    }
    
    $stmt->close();
    $conn->close(); // Close connection, but PHP will do this automatically anyway
}

function loginUser($conn) {
    $email = trim($_POST['email']); // again, remove spaces
    $password = $_POST['password']; // passwords don't need trimming

    if (!$email || !$password) { // same shortcut check
        $_SESSION['login_error'] = "All fields are required!";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }

    // Verify user exists
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) { // must be exactly 1 result
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) { // compare hashed passwords
            $_SESSION['user_id'] = $userId; // Store session ID
            header("Location: user_page.php"); // Redirect to user dashboard
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password or email!";
        }
    } else {
        $_SESSION['login_error'] = "Incorrect password or email!"; // generic error to avoid leaking info
    }
    
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}

// Close connection at the end, but PHP auto-closes it anyway
$conn->close();
