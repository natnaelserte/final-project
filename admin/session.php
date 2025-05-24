<?php
require 'dbcon.php';

// It's best practice to start the session at the very beginning of any script that uses it.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check whether the session variable `user_id` is present or not
if (!isset($_SESSION['user_id']) || trim($_SESSION['user_id']) == '') {
    // No need for JS redirect if PHP headers can be sent.
    // Ensure no HTML/output before this header call.
    header("Location: index.php");
    exit();
}

$session_id = $_SESSION['user_id'];

try {
    // Fetch user details securely using a prepared statement
    // Make sure to select the role_id column (or whatever your role column is named)
    $query = $pdo->prepare("SELECT *, role_id FROM users WHERE user_id = ?"); // Assuming 'role_id' is the column name
    $query->execute([$session_id]);
    $user_row = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user_row) {
        // If no user is found, destroy session and redirect to login
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        header("Location: index.php");
        exit();
    }

    $user_username = htmlspecialchars($user_row['firstname'] . " " . $user_row['lastname']);

    // --- NEW: Store user's role in the session ---
    if (isset($user_row['role_id'])) {
        $_SESSION['role'] = $user_row['role_id']; // Store the role_id in the session
    } else {
        // This case should ideally not happen if your DB schema is consistent.
        // Log an error and potentially deny access or assign a default guest role.
        error_log("CRITICAL: role_id not found in users table for user_id: " . $session_id);
        // You might want to unset the session role or redirect if role is crucial for every page
        unset($_SESSION['role']);
        // Or even log them out:
        // session_unset();
        // session_destroy();
        // header("Location: index.php?error=role_missing");
        // exit();
    }
    // --- END NEW ---

} catch (PDOException $e) {
    error_log("Database error in session.php for user_id " . $session_id . ": " . $e->getMessage());
    // For a production environment, you might want a more user-friendly error page
    // or just log them out.
    // session_unset();
    // session_destroy();
    // header("Location: index.php?error=db_session_error");
    echo "Error processing your session. Please try again later or contact support."; // Generic error
    exit();
}
?>