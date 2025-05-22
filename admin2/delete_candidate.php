<?php
require_once 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = $_POST['candidate_id'];

    try {
        // Delete the candidate securely using a prepared statement
        $query = $pdo->prepare("DELETE FROM candidate WHERE candidate_id = ?");
        $query->execute([$candidate_id]);

        // Start session and set success message
        session_start();
        $_SESSION['success_message'] = "Candidate deleted successfully.";
        header('Location: candidate.php'); // Redirect back to the candidates list page
        exit();
    } catch (PDOException $e) {
        // Start session and set error message
        session_start();
        $_SESSION['error_message'] = "Error deleting candidate: " . htmlspecialchars($e->getMessage());
        header('Location: candidate.php'); // Redirect back to the candidates list page
        exit();
    }
} else {
    // Redirect if accessed without POST
    header('Location: candidate.php');
    exit();
}