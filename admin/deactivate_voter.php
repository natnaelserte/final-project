<?php
require 'dbcon.php'; // Include database connection

// Check if the 'id' parameter is set
if (isset($_GET['user_id'])) {
    // Get the voter ID from the URL
    $user_id = $_GET['user_id'];

    try {
        // Prepare an SQL statement to deactivate the voter by updating their status
        $query = $pdo->prepare("UPDATE users SET account = 'Inactive' WHERE user_id = :user_id");
        $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $query->execute();

        // Redirect back to the voter list page with a success message
        header("Location: voters.php?account=deactivated");
        exit();
    } catch (PDOException $e) {
        // Handle errors (e.g., database issues)
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the voter ID is not set, redirect back with an error message
    echo "Error: Voter ID is not provided.";
}
?>
