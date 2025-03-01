<?php
// Start the session to access user info
session_start();

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit(); // Stop execution here if not logged in
}

require 'config.php'; // Include the configuration file for the database connection

$user_id = $_SESSION['user_id']; // Get the user_id from session

// Prepare the SQL query to get user details (name, email, and reward points)
$stmt = $conn->prepare("SELECT name, email, reward_points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id); // Bind the user_id parameter
$stmt->execute(); // Execute the query
$stmt->bind_result($name, $email, $reward_points); // Bind the result to variables
$stmt->fetch(); // Fetch the result
$stmt->close(); // Close the statement to free up resources
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCoin | User Dashboard</title>
    <link rel="stylesheet" href="user_page.css?v=<?= time(); ?>"> <!-- Cache-busting for styles -->
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font"
    />
    <link rel="icon" type="image/x-icon" href="logo.PNG"> <!-- Site favicon -->
</head>
<body>
    <!-- Header section with navigation bar -->
    <header>
        <nav class="navbar">
            <h1 class="logo"><img src="logo.PNG" alt="EcoCoin Logo"></h1>
            <ul class="nav-links">
                <li><a href="user_page.php">Home</a></li>
                <li><a href="scan.php">Scan</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="prizes.php">Prizes</a></li>
                <li><a href="logout.php" id="logout_btn">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <!-- Main content section -->
    <section class="content">
        <img src="Home.png" alt="EcoCoin Home Image">
        <h2 id="welcome">Hey, <?= htmlspecialchars($name); ?>!</h2> <!-- Display welcome message -->
        <span id="reward-points"><?= htmlspecialchars($reward_points); ?> </span> <!-- Display user's reward points -->
        <button id="scan-button" onclick="window.location.href='/ecocoin/scan.php';">Waste Scanner</button> <!-- Button to navigate to scan page -->
    </section>

    <!-- Footer section -->
    <footer>
        <p>&copy; 2025 EcoCoin. All rights reserved.</p>
    </footer>

    <!-- Script for updating reward points -->
    <script>
        function updatePoints(points) {
            // Send a POST request to update points in the backend
            fetch("update_points.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "points=" + points // Send the points as form data
            })
            .then(response => response.json()) // Parse the response as JSON
            .then(data => {
                if (data.success) {
                    // If successful, update the points on the page
                    document.getElementById("reward-points").textContent = data.new_points;
                } else {
                    // If there's an error, show an alert with the error message
                    alert("Whoops! Something went wrong: " + data.error);
                }
            })
            .catch(error => {
                // Handle any network or request errors
                alert("Network Error: " + error.message);
            });
        }
    </script>
</body>
</html>