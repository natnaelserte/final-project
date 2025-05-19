<?php
include('session.php'); // Ensure user is logged in (admin)
require 'dbcon.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Disable foreign key checks
        $pdo->query("SET FOREIGN_KEY_CHECKS=0");

        // Delete all records from the 'voters' table
        $stmt = $pdo->prepare("DELETE FROM voters");
        $stmt->execute();

        // Re-enable foreign key checks
        $pdo->query("SET FOREIGN_KEY_CHECKS=1");

        // Return a success message
        echo "All voters deleted successfully!";
    } catch (PDOException $e) {
        // Return an error message
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the request is not a POST request, return an error
    echo "Error: Invalid request method.";
}
?>