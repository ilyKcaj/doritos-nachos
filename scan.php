<?php
session_start();

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit(); // Always exit after header redirect
}

require 'config.php'; // Include the DB config file to connect

$user_id = $_SESSION['user_id']; // User's session ID is stored here

// Get user's name, email, and reward points from the DB
$stmt = $conn->prepare("SELECT name, email, reward_points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute(); // Executes the SQL query
$stmt->bind_result($name, $email, $reward_points); // Fetch the results
$stmt->fetch(); // Pulls the data from the query into the variables
$stmt->close(); // Always remember to close your statements!
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCoin | Waste Scanning</title>
    <link rel="stylesheet" href="scan.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" data-tag="font" />
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
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

    <!-- Video feed for camera input -->
    <video id="camera" width="400" height="300" autoplay></video>
    <canvas id="canvas" width="400" height="300"></canvas>

    <script>
        // Function to send updated points to the backend
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
                    alert("Error: " + data.error); // If error, alert the user
                }
            });
        }

        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');

        // Load the COCO-SSD model from TensorFlow
        let model;
        cocoSsd.load().then(loadedModel => {
            model = loadedModel;
            console.log('COCO-SSD model loaded successfully!');
            startVideo();
        });

        // Start accessing the webcam to stream video
        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then((stream) => {
                    video.srcObject = stream;
                    console.log("Camera streaming started.");

                    // Once the video metadata is loaded, set the canvas size to match the video
                    video.onloadedmetadata = () => {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                    };
                })
                .catch((err) => {
                    console.error("Error accessing camera:", err);
                    alert('Please allow access to your camera to scan items.');
                });
        }

        // List of recyclable items, including paper-based materials
        const recyclableItems = [
            'bottle', 'can', 'plastic', 'paper', 'cardboard', 'newspaper', 'paper bag', 'magazine', 'book', 'cup'
        ];

        const MIN_CONFIDENCE = 0.7; // Minimum confidence score for detection to be considered valid

        // Object to store timers for detecting recyclable items
        let recyclableTimers = {};

        // Main detection loop that constantly checks for objects in the video stream
        function detectObjects() {
            model.detect(video).then(predictions => {
                context.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas before drawing the new frame
                context.drawImage(video, 0, 0, canvas.width, canvas.height); // Draw the video frame onto the canvas

                predictions.forEach(prediction => {
                    // Skip "remote" or "traffic light" objects
                    if (prediction.class.toLowerCase() === 'remote' || prediction.class.toLowerCase() === 'traffic light') {
                        return;
                    }

                    // Only consider objects that meet the confidence threshold (unless it's a book)
                    if (prediction.class.toLowerCase() !== 'book' && prediction.score < MIN_CONFIDENCE) {
                        return;
                    }

                    // Check if the detected object is recyclable
                    const isRecyclable = recyclableItems.includes(prediction.class.toLowerCase());

                    // Draw the bounding box with a color indicating recyclability
                    context.beginPath();
                    context.rect(prediction.bbox[0], prediction.bbox[1], prediction.bbox[2], prediction.bbox[3]);
                    context.lineWidth = 2;
                    context.strokeStyle = isRecyclable ? 'green' : 'red'; // Green if recyclable, red if not
                    context.fillStyle = isRecyclable ? 'green' : 'red'; 
                    context.stroke();

                    // Display the object label on the canvas
                    context.fillText(prediction.class + ': ' + Math.round(prediction.score * 100) + '%', prediction.bbox[0], prediction.bbox[1] > 10 ? prediction.bbox[1] - 5 : 10);

                    // If it's recyclable, start tracking its time
                    if (isRecyclable) {
                        const key = prediction.class + '-' + Math.round(prediction.bbox[0]) + '-' + Math.round(prediction.bbox[1]);

                        if (!recyclableTimers[key]) {
                            recyclableTimers[key] = { start: Date.now(), detected: true };
                        }
                    }
                });

                // Check if any recyclable item has been detected for more than 1 second
                for (const key in recyclableTimers) {
                    if (Date.now() - recyclableTimers[key].start > 1000) {
                        window.location.href = "./receipt.php"; // Redirect after detecting recyclable item for 1 second
                    }
                }
            });
        }

        // Call the detection function every 1ms to ensure real-time object recognition
        setInterval(detectObjects, 1);
    </script>

    <footer>
        <p>&copy; 2025 EcoCoin. All rights reserved.</p>
    </footer>

</body>
</html>