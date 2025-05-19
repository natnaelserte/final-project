<?php
require_once 'admin/dbcon.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit();
} else {
    $session_id = $_SESSION['user_id'];

    try {
        // Use a parameterized query to fetch voter details securely
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$session_id]);
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_row) {
            // If no user is found, redirect to login
            header("location:login.php");
            exit();
        }

        $user_username = htmlspecialchars($user_row['firstname'] . " " . $user_row['lastname']);
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . htmlspecialchars($e->getMessage());
        exit();
    }
}
?>