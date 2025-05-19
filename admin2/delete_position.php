<?php
require 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position_id'])) {
    $position_id = $_POST['position_id'];

    try {
        // Delete the position securely using a prepared statement
        $stmt = $pdo->prepare("DELETE FROM position WHERE position_id = :position_id");
        $stmt->bindParam(':position_id', $position_id, PDO::PARAM_INT);
        $stmt->execute();

        // Start session and set success message
        session_start();
        $_SESSION['success_message'] = "Position deleted successfully.";
        header("Location: add_position.php"); // Redirect back to the position list page
        exit();
    } catch (PDOException $e) {
        // Start session and set error message
        session_start();
        $_SESSION['error_message'] = "Error deleting position: " . htmlspecialchars($e->getMessage());
        header("Location: add_position.php"); // Redirect back to the position list page
        exit();
    }
} else {
    // Redirect if accessed without POST
    header("Location: add_position.php");
    exit();
}
?>

