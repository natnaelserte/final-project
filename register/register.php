<?php
session_start();
require_once 'dbcon.php';
date_default_timezone_set('Africa/Nairobi');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Validate session data
    if (!isset($_SESSION['registration_data'], $_SESSION['phone_number'], $_SESSION['user_otp'])) {
        echo "<script>alert('Session data missing. Please start registration again.'); window.location='index.php';</script>";
        exit();
    }

    // Extract session data
    $registration_data = $_SESSION['registration_data'];
    $phone = $_SESSION['phone_number'];
    $otp = $_SESSION['user_otp'];

    $id_number = $registration_data['id_number'];
    $password = $registration_data['password']; // Already encrypted
    $gender = $registration_data['gender'];
    $email = $registration_data['email'];
    $department = $registration_data['department'];
    $club_membership = $registration_data['club_membership'];
    $is_class_representative = $registration_data['is_class_representative'];
    $date = date("Y-m-d H:i:s");
    // $status = 'pending'; // Or 'active' depending on your workflow after OTP <-- REMOVED STATUS VARIABLE

    // Check OTP validity (not older than 1 hour)
    $valid_time = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $stmt = $pdo->prepare("SELECT * FROM otp_table WHERE phone_number = ? AND otp = ? AND expiration_time > ? AND is_verified = 0");
    $stmt->execute([$phone, $otp, $valid_time]);
    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        echo "<script>alert('Invalid OTP or OTP expired.'); window.location='otp_verify.php';</script>";
        exit();
    }

    // Mark OTP as verified
    $stmt = $pdo->prepare("UPDATE otp_table SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$otpRecord['id']]);

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE id_number = ?");
    $stmt->execute([$id_number]);
    if ($stmt->fetch()) {
        echo "<script>alert('This ID is already registered.'); window.location='../index.php';</script>";
        exit();
    }

    // Fetch user details from IDs table
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM ids WHERE id_number = ?");
    $stmt->execute([$id_number]);
    $id_data = $stmt->fetch();

    if (!$id_data) {
        echo "<script>alert('ID number not found in the system.'); window.location='index.php';</script>";
        exit();
    }

    // Prepare user details
    $username = $id_data['username'];
    $firstname = $id_data['firstname'];
    $lastname = $id_data['lastname'];

    // Insert new user
    try {
        // <-- MODIFIED: Removed 'status' from INSERT query and execute array
        $insert_query = "INSERT INTO users (id_number, username, password, firstname, lastname, gender, phone, email, department, club_membership, is_class_representative, registration_date)
                         VALUES (:id_number, :username, :password, :firstname, :lastname, :gender, :phone, :email, :department, :club_membership, :is_class_representative, :registration_date)";
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([
            ':id_number' => $id_number,
            ':username' => $username,
            ':password' => $password,
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':gender' => $gender,
            ':phone' => $phone,
            ':email' => $email,
            ':department' => $department,
            ':club_membership' => $club_membership,
            ':is_class_representative' => $is_class_representative,
            ':registration_date' => $date
            // ':status' => $status // <-- REMOVED STATUS PARAMETER
        ]);

        // Clear session data after success
        unset($_SESSION['registration_data'], $_SESSION['phone_number'], $_SESSION['otp_sent'], $_SESSION['user_otp']);

        echo "<script>alert('Registration successful.'); window.location='../index.php';</script>";
        exit();
    } catch (PDOException $e) {
        // Optionally: error_log($e->getMessage());
        echo "<script>alert('Error saving user. Please try again. Error: " . htmlspecialchars($e->getMessage()) . "'); window.location='index.php';</script>";
        exit();
    }

} else {
    // Redirect if not GET request
    header("Location: otp_verify.php");
    exit();
}
?>