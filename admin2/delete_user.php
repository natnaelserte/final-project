<?php
require_once 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    try {
        // Delete the user securely using a prepared statement
        $query = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $query->execute([$user_id]);

        // Start session and set success message
        session_start();
        $_SESSION['success_message'] = "User deleted successfully.";
        header('Location: user.php'); // Redirect back to the users list page
        exit();
    } catch (PDOException $e) {
        // Start session and set error message
        session_start();
        $_SESSION['error_message'] = "Error deleting user: " . htmlspecialchars($e->getMessage());
        header('Location: user.php'); // Redirect back to the users list page
        exit();
    }
} else {
    // Redirect if accessed without POST
    header('Location: user.php');
    exit();
}