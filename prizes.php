<?php
session_start(); // Start the session, user data will be saved across requests

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to index if not logged in
    exit(); // Make sure the script stops executing after redirect
}

require 'config.php'; // Include database connection settings

$user_id = $_SESSION['user_id']; // Get user ID from session

// Prepare the SQL query to fetch user details, including reward points
$stmt = $conn->prepare("SELECT name, email, reward_points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id); // Bind the user ID parameter
$stmt->execute(); // Execute the query

// Bind result variables to store fetched data
$stmt->bind_result($name, $email, $reward_points);
$stmt->fetch(); // Fetch the result into variables
$stmt->close(); // Close the statement after execution
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCoin | Prizes</title>
    <link rel="stylesheet" href="prizes.css?v=<?= time(); ?>"> <!-- Cache busting by adding timestamp -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link rel="icon" type="image/x-icon" href="logo.PNG"> <!-- Site icon -->
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo"><img src="logo.PNG" alt="EcoCoin Logo"></h1> <!-- Logo image -->
            <ul class="nav-links">
                <li><a href="user_page.php">Home</a></li>
                <li><a href="scan.php">Scan</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="prizes.php">Prizes</a></li>
                <li><a href="logout.php" id="logout_btn">Logout</a></li> <!-- Logout link -->
            </ul>
        </nav>
    </header>
    
    <section class="content">
        <img src="Prizes.png" alt="Prizes Image"> <!-- Image showing prizes -->
    </section>

    <footer>
        <p>&copy; 2025 EcoCoin. All rights reserved.</p>
    </footer>

    <script>
        // Function to update reward points after an action
        function updatePoints(points) {
            fetch("update_points.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded" // Sending data as URL-encoded
                },
                body: "points=" + points // Sending points data to server
            })
            .then(response => response.json()) // Parse the response as JSON
            .then(data => {
                if (data.success) {
                    document.getElementById("reward-points").textContent = data.new_points; // Update the UI with new points
                } else {
                    alert("Error: " + data.error); // Show an error message if something goes wrong
                }
            })
            .catch(error => {
                console.error('There was an issue with the fetch operation:', error); // Log any fetch errors
                alert("An unexpected error occurred. Please try again.");
            });
        }
    </script>
</body>
</html>