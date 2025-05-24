<?php
require 'dbcon.php'; // Include database connection

// Check if the 'user_id' parameter is set
if (isset($_GET['user_id'])) {
    // Get the voter ID from the URL
    $user_id = $_GET['user_id'];

    try {
        // Prepare an SQL statement to activate the voter by updating their status
        $query = $pdo->prepare("UPDATE users SET account = 'Active' WHERE user_id = :user_id");
        $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $query->execute();

        // Redirect back to the voter list page with a success message
        header("Location: index.php?account=activated");
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
