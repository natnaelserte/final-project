<?php
require 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    try {
        // Delete all candidates
        $query = $pdo->prepare("DELETE FROM candidate");
        $query->execute();

        // Start session and set success message
        session_start();
        $_SESSION['success_message'] = "All candidates deleted successfully.";
        header("Location: candidate.php"); // Redirect back to the candidate list page
        exit();
    } catch (PDOException $e) {
        // Start session and set error message
        session_start();
        $_SESSION['error_message'] = "Error deleting candidates: " . htmlspecialchars($e->getMessage());
        header("Location: candidate.php"); // Redirect back to the candidate list page
        exit();
    }
} else {
    // Redirect if accessed without POST
    header("Location: candidate.php");
    exit();
}
?>