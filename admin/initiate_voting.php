<?php
include('session.php'); // Ensure user is logged in (admin)
require 'dbcon.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the title and hours from the POST data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $hours = isset($_POST['hours']) ? intval($_POST['hours']) : 0;
    $edit = isset($_POST['edit']) ? filter_var($_POST['edit'], FILTER_VALIDATE_BOOLEAN) : false; // Get the 'edit' parameter

    // Validate the data
    if (empty($title) || $hours <= 0) {
        echo "Error: Invalid title or hours.";
        exit;
    }

    // Calculate the end time
    $startTime = time();
    $endTime = $startTime + ($hours * 3600);  // hours * seconds in an hour

    try {
        if ($edit) {
            // Editing existing event
            //Get the active voting event
            $checkStmt = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
            $checkStmt->execute();
            $votingEvent = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$votingEvent) {
                echo "Error: No active voting event found to edit.";
                exit;
            }

            $votingEventId = $votingEvent['id'];

            // Prepare the SQL statement to update the existing event
            $stmt = $pdo->prepare("UPDATE voting_events SET title = ?, end_time = ? WHERE id = ?");
            // Execute the statement
            $stmt->execute([$title, $endTime, $votingEventId]);

            // Return a success message
            echo "Voting event updated successfully! Title: " . htmlspecialchars($title) . ", Duration: " . $hours . " hours.";

        } else {
            // Creating a new event
            // Check if there's an existing active voting
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM voting_events WHERE is_active = 1");
            $checkStmt->execute();
            $activeVotingCount = $checkStmt->fetchColumn();

            if ($activeVotingCount > 0) {
                echo "Error: There is already an active voting event. Please end it before starting a new one.";
                exit;
            }

            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO voting_events (title, start_time, end_time, is_active) VALUES (?, ?, ?, ?)");

            // Execute the statement
            $stmt->execute([$title, $startTime, $endTime, 1]);

            // Get the ID of the newly inserted voting event
            $votingEventId = $pdo->lastInsertId();

            // Activate all voters
            $updateStmt = $pdo->prepare("UPDATE voters SET account = 'Active'");
            $updateStmt->execute();

            // Return a success message
            echo "Voting initiated successfully! Title: " . htmlspecialchars($title) . ", Duration: " . $hours . " hours.";
        }
    } catch (PDOException $e) {
        // Return an error message
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the request is not a POST request, return an error
    echo "Error: Invalid request method.";
}
?>