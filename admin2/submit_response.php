<?php
require_once('dbcon.php');

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve inputs, ensuring they are set
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null; // Casting to int to prevent SQL injection via ID
    $response = isset($_POST['response']) ? trim($_POST['response']) : '';

    // Validate the inputs
    if ($id && !empty($response)) {
        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("UPDATE report_complaints SET response = :response WHERE id = :id");

            // Execute the statement with sanitized inputs
            $stmt->execute([
                ':response' => $response,
                ':id' => $id
            ]);

            // Success message with redirection
            echo "<script>alert('Response submitted successfully'); window.location.href = 'staff_complaint.php';</script>";
        } catch (PDOException $e) {
            // Catch any database errors and display a message
            echo "<script>alert('Error updating response: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    } else {
        // If validation fails, show an error message
        echo "<script>alert('Invalid data. Please check your input.'); window.history.back();</script>";
    }
} else {
    // Handle invalid request methods
    echo "Invalid request method.";
}
?>
