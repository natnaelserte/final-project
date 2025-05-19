<?php
include 'db_connect.php'; // Your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['username'];
    $voting_event_id = $_POST['voting_event_id'];
    $category = $_POST['category'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO complaints (username, voting_event_id, category, subject, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $voting_event_id, $category, $subject, $description);

    if ($stmt->execute()) {
        echo "Complaint submitted successfully!";
        // Optionally redirect or notify staff
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
