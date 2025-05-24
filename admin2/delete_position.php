<?php
session_start(); // Start session to store messages
require 'dbcon.php'; // Include your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $position_id = $_GET['id'];

    try {
        // Use a prepared statement to delete securely
        $stmt = $pdo->prepare("DELETE FROM position WHERE position_id = :position_id");
        $stmt->bindParam(':position_id', $position_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Position deleted successfully.";
        header("Location: add_position.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting position: " . htmlspecialchars($e->getMessage());
        header("Location: add_position.php");
        exit();
    }
} else {
    // Redirect if accessed without valid GET request
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: add_position.php");
    exit();
}
?>
