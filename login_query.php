<?php
session_start();
require_once 'admin/dbcon.php'; // adjust path as needed

// Define constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', '15 minutes');

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if account is locked
            if ($user['is_locked'] == 1 && $user['lockout_until'] !== null) {
                $now = new DateTime();
                $lockout_until = new DateTime($user['lockout_until']);

                if ($now < $lockout_until) {
                    $remaining = $now->diff($lockout_until)->format('%i minutes and %s seconds');
                    $_SESSION['login_message'] = [
                        'type' => 'danger',
                        'text' => "Your account is locked due to too many failed login attempts. Try again in $remaining."
                    ];
                    header("Location: login.php");
                    exit();
                } else {
                    // Unlock account after lockout duration
                    $unlock_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, is_locked = 0, lockout_until = NULL WHERE user_id = ?");
                    $unlock_stmt->execute([$user['user_id']]);
                    $user['is_locked'] = 0;
                    $user['failed_login_attempts'] = 0;
                }
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts on successful login
                $reset_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, is_locked = 0, lockout_until = NULL WHERE user_id = ?");
                $reset_stmt->execute([$user['user_id']]);

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = $user['role_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['otp_verified'] = false;

                if (!empty(trim($user['phone']))) {
                    $_SESSION['phone'] = trim($user['phone']);
                } else {
                    $_SESSION['login_message'] = [
                        'type' => 'danger',
                        'text' => 'Phone number not found. OTP cannot be sent. Contact support.'
                    ];
                    header("Location: login.php");
                    exit();
                }

                // Log login time
                $log_stmt = $pdo->prepare("INSERT INTO login (user_id, username, login_time) VALUES (?, ?, ?)");
                $log_stmt->execute([$user['user_id'], $user['username'], date('Y-m-d H:i:s')]);

                header("Location: otp_form.php");
                exit();
            } else {
                // Incorrect password
                $new_attempts = $user['failed_login_attempts'] + 1;
                $now_str = date('Y-m-d H:i:s');

                if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
                    $lockout_until = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_DURATION));
                    $fail_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, is_locked = 1, lockout_until = ? WHERE user_id = ?");
                    $fail_stmt->execute([$new_attempts, $lockout_until, $user['user_id']]);
                    $_SESSION['login_message'] = [
                        'type' => 'danger',
                        'text' => "Your account has been locked due to too many failed login attempts. Try again after " . LOCKOUT_DURATION . "."
                    ];
                } else {
                    $fail_stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = ? WHERE user_id = ?");
                    $fail_stmt->execute([$new_attempts, $user['user_id']]);
                    $attempts_left = MAX_LOGIN_ATTEMPTS - $new_attempts;
                    $_SESSION['login_message'] = [
                        'type' => 'warning',
                        'text' => "Incorrect password. $attempts_left attempts remaining before your account is locked."
                    ];
                }
                header("Location: login.php");
                exit();
            }
        } else {
            // Username not found
            $_SESSION['login_message'] = [
                'type' => 'danger',
                'text' => 'Incorrect username or password.'
            ];
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['login_message'] = [
            'type' => 'danger',
            'text' => 'A database error occurred. Please try again later.'
        ];
        header("Location: login.php");
        exit();
    }
}
?>
