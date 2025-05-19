<?php
include('session.php');
require 'dbcon.php';

// Function to generate OTP
function generateOTP($length = 6) {
    $characters = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}
// Function to hash the OTP
function hashOTP($otp) {
    return password_hash($otp, PASSWORD_DEFAULT);
}

// Function to simulate sending OTP to phone number (stores hashed OTP in session)
function sendOTPToPhone($phone_number, $otp) {
    $hashed_otp = hashOTP($otp);
    $_SESSION['otp'] = $hashed_otp; // Store hashed OTP in session
    $_SESSION['otp_expiry'] = time() + 120; // OTP expires in 2 minutes

    // Store the UNHASHED OTP in a SEPARATE session variable (FOR SIMULATION ONLY)
    $_SESSION['unhashed_otp'] = $otp;

    return true; // Indicate success (since we're simulating)
}

// Handle OTP generation
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'getOtp' || $_POST['action'] == 'resendOtp') {
        $user_id = $_SESSION['user_id']; // Get user ID from session
        try {
            $query = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?"); // Get phone number
            $query->execute([$user_id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $phone = $row['phone'];
            $phone_last_digits= substr($phone, -4); // Get last 4 digits of phone number
            $phone = '******' . $phone_last_digits; // Mask the phone number except for the last 4 digits

            $otp = generateOTP();

            if (sendOTPToPhone($phone, $otp)) { // Send OTP to phone (simulated)
                echo json_encode(['success' => true, 'message' => 'OTP sent . Check below.','phone' => $phone,'otp' => $_SESSION['unhashed_otp']]); // Send the unhashed OTP
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send OTP (simulated).']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit; // Stop script execution
    }
}

// Handle password update
if (isset($_POST['action']) && $_POST['action'] == 'updatePassword') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $otp = $_POST['otp'];

    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Validate OTP
    if (!isset($_SESSION['otp']) || !password_verify($otp, $_SESSION['otp']) || time() > $_SESSION['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
        exit;
    }

    // Validate passwords
    if ($newPassword != $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    try {
        // Verify current password
        $query = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $query->execute([$user_id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $hashedPassword = $row['password'];

        if (!password_verify($currentPassword, $hashedPassword)) {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
            exit;
        }

        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password in database
        $update_query = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_query->execute([$hashedNewPassword, $user_id]);

        // Clear OTP from session
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
        unset($_SESSION['unhashed_otp']); // Also clear the unhashed OTP

        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// If no action is specified, return an error
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>