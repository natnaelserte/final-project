<?php
require_once 'dbcon.php';
session_start();

if (isset($_POST['verify_otp'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $login_id = $_POST['login_id'];

    try {
        // 1. Retrieve the user's hashed password from the database
        $stmt = $pdo->prepare("SELECT user_id, password FROM user WHERE username = ? AND user_id = ?");
        $stmt->execute([$username, $login_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2. Verify the password using password_verify()
            if (password_verify($password, $user['password'])) {
                // Password is correct

                // Store user ID and username in the session (after successful authentication)
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['get_otp'] = true; // Indicate that the user has successfully logged in and can request OTP
                header('Location: otp_form.php'); // Redirect to OTP form

            } else {
                // Password is incorrect
                echo "<script>alert('Invalid Login ID, Username, or Password!'); window.location = 'index.php';</script>";
                exit();
            }
        } else {
            // User not found
            echo "<script>alert('Invalid Login ID, Username, or Password!'); window.location = 'index.php';</script>";
            exit();
        }

    } catch (PDOException $e) {
        // Handle database errors
        echo "Database error: " . $e->getMessage();
        error_log("PDOException: " . $e->getMessage());
    }
}
?>