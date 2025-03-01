<?php
// Start session - making sure we can track the user
session_start();

// If user isn't logged in, kick 'em out
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Send 'em back to login
    exit(); // Stop script here, no need to continue
}

require 'config.php'; // Bring in DB connection, hope it works...

$user_id = $_SESSION['user_id']; // Grab the user's ID from session

// Fetch some basic user details
$stmt = $conn->prepare("SELECT name, email, reward_points FROM users WHERE id = ?");
if (!$stmt) {
    die("Something went wrong with the SQL query"); // Debugging fallback
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $reward_points);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCoin | Catalog</title>
    <link rel="stylesheet" href="catalog.css?v=<?= time(); ?>"> <!-- Cache busting, kinda hacky -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap">
    <link rel="icon" type="image/x-icon" href="logo.PNG"> <!-- Hope this file exists -->
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo"><img src="logo.PNG" alt="Logo"></h1> <!-- Might wanna add alt text -->
            <ul class="nav-links">
                <li><a href="user_page.php">Home</a></li>
                <li><a href="scan.php">Scan</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="prizes.php">Prizes</a></li>
                <li><a href="logout.php" id="logout_btn">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="content">
        <img src="Catalog.png" alt="Catalog Image"> <!-- Hope this is the right path -->
    </section>

    <footer>
        <p>&copy; <?= date("Y"); ?> EcoCoin. All rights reserved.</p> <!-- Auto-updating year, cuz why not -->
    </footer>

    <script>
        function updatePoints(points) {
            // Basic AJAX request to update points, not the best error handling
            fetch("update_points.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "points=" + encodeURIComponent(points) // Safety first
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("reward-points").textContent = data.new_points;
                } else {
                    alert("Error: " + data.error); // Generic error handling, could be better
                }
            })
            .catch(error => console.error("Something went wrong", error)); // Debugging output
        }
    </script>
</body>
</html>