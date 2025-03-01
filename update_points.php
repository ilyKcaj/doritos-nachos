<?php
// Start the session to access user info
session_start();
require 'config.php'; // Include the database configuration

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, return an error message
    echo json_encode(["error" => "Not logged in"]);
    exit(); // Stop further execution
}

$user_id = $_SESSION['user_id']; // Get the user ID from the session
$points = isset($_POST['points']) ? intval($_POST['points']) : 0; // Get the points from the POST request, default to 0 if not set

// Check if the points are a valid number (can't be negative)
if ($points < 0) {
    // Return an error message if the points are negative
    echo json_encode(["error" => "Invalid points"]);
    exit(); // Stop further execution
}

// Prepare the SQL statement to update the user's reward points
$stmt = $conn->prepare("UPDATE users SET reward_points = reward_points + ? WHERE id = ?");
if ($stmt === false) {
    // Handle potential error with statement preparation
    echo json_encode(["error" => "Failed to prepare the query"]);
    exit();
}

$stmt->bind_param("ii", $points, $user_id); // Bind parameters (points and user_id)
$stmt->execute(); // Execute the query

// Check if the query execution was successful
if ($stmt->affected_rows <= 0) {
    echo json_encode(["error" => "Failed to update points"]);
    $stmt->close();
    exit(); // Stop further execution if the update failed
}

$stmt->close(); // Close the statement after execution

// Fetch the updated reward points for the user
$stmt = $conn->prepare("SELECT reward_points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute(); // Execute the query

$stmt->bind_result($updatedPoints); // Bind the result to the variable $updatedPoints
$stmt->fetch(); // Fetch the result

$stmt->close(); // Close the statement

// If everything went well, return the updated points
echo json_encode(["success" => true, "new_points" => $updatedPoints]);
?>