<?php
require 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_positions'])) {
    try {
        // Delete all positions
        $query = $pdo->prepare("DELETE FROM position");
        $query->execute();

        // Start session and set success message
        session_start();
        $_SESSION['success_message'] = "All positions deleted successfully.";
        header("Location: add_position.php"); // Redirect back to the positions list page
        exit();
    } catch (PDOException $e) {
        // Start session and set error message
        session_start();
        $_SESSION['error_message'] = "Error deleting positions: " . htmlspecialchars($e->getMessage());
        header("Location: add_position.php"); // Redirect back to the positions list page
        exit();
    }
} else {
    // Redirect if accessed without POST
    header("Location: add_position.php");
    exit();
}
?>