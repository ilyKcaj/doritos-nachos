<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'config.php'; // Include database connection

$user_id = $_SESSION['user_id'];

// Fetch user details including reward points
$stmt = $conn->prepare("SELECT name, email, reward_points FROM users WHERE id = ?");
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
    <title>EcoCoin | Receipt</title>
    <link rel="stylesheet" href="receipt.css?v=<?= time(); ?>">
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link rel="icon" type="image/x-icon" href="logo.PNG">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo"><img src="logo.PNG"></h1>
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
        <img src="Receipt.png">
        <a href="saved_receipt.png" download="receipt.jpg">
            <button id="download-button">Download</button>
        </a>
    </section>

    <footer>
        <p>&copy; 2025 EcoCoin. All rights reserved.</p>
    </footer>

    <script>
        function updatePoints(points) {
            fetch("update_points.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "points=" + points
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("reward-points").textContent = data.new_points;
                } else {
                    alert("Error: " + data.error);
                }
            });
        }
    </script>
</body>
</html>