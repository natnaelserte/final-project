<?php
 
// RIGOROUS SESSION START: Ensure this is at the very top, before ANY output or other includes.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



if (!isset($_SESSION['user_id'])) {
    // Optionally store the intended destination to redirect after login
    // $_SESSION['redirect_url_after_login'] = $_SERVER['REQUEST_URI'];
    header("location:login.php");
    exit();
}

// For pages that require full authentication (including OTP), check otp_verified status
$currentPage = basename($_SERVER['PHP_SELF']);
$otpSensitivePages = ['vote.php', 'vote_review.php', 'submit_vote.php']; // Add other pages that need OTP
$otpBypassPages = ['otp_form.php', 'login.php', 'login_query.php']; // Pages involved in login/OTP itself

if (in_array($currentPage, $otpSensitivePages) && empty($_SESSION['otp_verified'])) {
    // If OTP is not verified and the current page requires it, redirect to OTP form.
    // You might want to store a message for otp_form.php to display.
    $_SESSION['otp_message'] = "Please complete Two-Factor Authentication to proceed.";
    $_SESSION['otp_message_type'] = "info";
    // $_SESSION['redirect_url_after_otp'] = $_SERVER['REQUEST_URI']; // Store where to go after OTP
    header("Location: otp_form.php");
    exit();
}

// At this point, $_SESSION['user_id'] is set.
// The database check for user existence is good for security.
$session_id = $_SESSION['user_id'];
// $user_role = $_SESSION['user_role'] ?? null; // This variable is local to sess.php unless needed globally from here
require_once 'admin/dbcon.php';
try {
    $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, role_id FROM users WHERE user_id = ?"); // Fetch only needed fields
    $stmt->execute([$session_id]);
    $user_row_sess = $stmt->fetch(PDO::FETCH_ASSOC); // Use a different var name to avoid clashes

    if (!$user_row_sess) {
        // User ID in session does not exist in DB. Invalid session.
        session_unset(); // Remove all session variables
        session_destroy(); // Destroy the session
        header("location:login.php?error=invalid_session_user_not_found");
        exit();
    }
    // Optionally, you could re-verify/update $_SESSION['user_role'] here if it can change
    // and you want sess.php to be the source of truth after login.
    // $_SESSION['user_role'] = $user_row_sess['role_id']; 

    // $user_username_sess = htmlspecialchars($user_row_sess['firstname'] . " " . $user_row_sess['lastname']); // Variable for display if needed by including pages
} catch (PDOException $e) {
    error_log("sess.php - Database error: " . $e->getMessage());
    // Display a generic error or redirect to an error page
    echo "A critical error occurred with the session. Please try again later or contact support.";
    exit();
}
?>