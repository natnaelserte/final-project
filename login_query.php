<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'admin/dbcon.php'; // adjust path as needed

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', '15 minutes'); // e.g., '15 minutes', '1 hour'

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Password from form

    try {
        $stmt = $pdo->prepare("SELECT user_id, username, password, role_id, failed_login_attempts, is_locked, lockout_until, phone FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if account is locked
            if ($user['is_locked'] == 1 && $user['lockout_until'] !== null) {
                $now = new DateTime();
                $lockout_until_dt = new DateTime($user['lockout_until']);

                if ($now < $lockout_until_dt) {
                    $remaining = $now->diff($lockout_until_dt)->format('%i minutes and %s seconds');
                    $_SESSION['login_message'] = ['type' => 'danger', 'text' => "Account locked. Try again in $remaining."];
                    header("Location: login.php");
                    exit();
                } else {
                    // Unlock account
                    $unlock_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, is_locked = 0, lockout_until = NULL WHERE user_id = ?");
                    $unlock_stmt->execute([$user['user_id']]);
                    $user['is_locked'] = 0; // Reflect change locally
                    $user['failed_login_attempts'] = 0;
                }
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts on successful login
                $reset_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, is_locked = 0, lockout_until = NULL WHERE user_id = ?");
                $reset_stmt->execute([$user['user_id']]);

                // Set initial session variables needed for OTP and beyond
                $_SESSION['user_id'] = $user['user_id'];       // CRITICAL
                $_SESSION['user_role'] = $user['role_id'];     // CRITICAL
                $_SESSION['username'] = $user['username'];     // For display/logging
                $_SESSION['otp_verified'] = false;             // OTP NOT YET VERIFIED
                $_SESSION['temp_user_phone_for_otp'] = trim($user['phone']); // Store phone temporarily for 

                header("Location: otp_form.php"); // Proceed to OTP verification
                exit();

            } else { // Incorrect password
                $new_attempts = $user['failed_login_attempts'] + 1;
                if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
                    $lockout_until_ts = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_DURATION));
                    $fail_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, is_locked = 1, lockout_until = ? WHERE user_id = ?");
                    $fail_stmt->execute([$new_attempts, $lockout_until_ts, $user['user_id']]);
                    $_SESSION['login_message'] = ['type' => 'danger', 'text' => "Account locked for " . LOCKOUT_DURATION . " due to too many failed attempts."];
                } else {
                    $fail_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ? WHERE user_id = ?");
                    $fail_stmt->execute([$new_attempts, $user['user_id']]);
                    $attempts_left = MAX_LOGIN_ATTEMPTS - $new_attempts;
                    $_SESSION['login_message'] = ['type' => 'warning', 'text' => "Incorrect password. $attempts_left attempts remaining."];
                }
                header("Location: login.php");
                exit();
            }
        } else { // Username not found
            $_SESSION['login_message'] = ['type' => 'danger', 'text' => 'Incorrect username or password.'];
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login_query.php - Database error: " . $e->getMessage());
        $_SESSION['login_message'] = ['type' => 'danger', 'text' => 'A database error occurred. Please try again.'];
        header("Location: login.php");
        exit();
    }
} else {
    // If not a POST request with 'login' action
    header("Location: login.php");
    exit();
}
?>