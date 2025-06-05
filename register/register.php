<?php
session_start();
require_once 'dbcon.php'; // Your database connection
date_default_timezone_set('Africa/Nairobi'); // Or your preferred timezone

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Validate session data more robustly
    if (
        !isset($_SESSION['registration_data']) ||
        !isset($_SESSION['phone_number']) ||
        !isset($_SESSION['user_otp']) // This should be the OTP entered by the user, not one stored long-term
    ) {
        // Clear potentially incomplete session data to force restart
        unset($_SESSION['registration_data'], $_SESSION['phone_number'], $_SESSION['user_otp'], $_SESSION['otp_sent_time']);
        echo "<script>alert('Session data is missing or incomplete. Please start the registration process again.'); window.location='index.php';</script>";
        exit();
    }

    // Extract session data
    $registration_data = $_SESSION['registration_data'];
    $phone_from_session = $_SESSION['phone_number']; // Phone number used for OTP
    $user_entered_otp = $_SESSION['user_otp']; // The OTP the user submitted for verification

    // Registration data from the first form
    $id_number = $registration_data['id_number'];
    $password_hashed = $registration_data['password']; // Already encrypted/hashed
    $gender = $registration_data['gender'];
    $email = $registration_data['email'];
    $department = $registration_data['department'];
    $club_membership = $registration_data['club_membership'];
    $is_class_representative = $registration_data['is_class_representative'];
    $registration_date = date("Y-m-d H:i:s");

    // Validate OTP (e.g., check against `otp_table` or however you manage OTPs)
    // This is a crucial step. The example below assumes an `otp_table`.
    // For security, OTPs should have a short expiry and be marked as used.
    $otp_expiry_minutes = 10; // Example: OTP valid for 10 minutes
    $valid_otp_time_from = date('Y-m-d H:i:s', strtotime("-{$otp_expiry_minutes} minutes"));

    $stmt_otp = $pdo->prepare("SELECT id FROM otp_table WHERE phone_number = :phone AND otp = :otp AND expiration_time >= :valid_from AND is_verified = 0");
    $stmt_otp->bindParam(':phone', $phone_from_session);
    $stmt_otp->bindParam(':otp', $user_entered_otp); // Use the OTP the user entered
    $stmt_otp->bindParam(':valid_from', $valid_otp_time_from);
    $stmt_otp->execute();
    $otp_record = $stmt_otp->fetch(PDO::FETCH_ASSOC);

    if (!$otp_record) {
        // Optionally, increment OTP attempt counter here to prevent brute-forcing
        echo "<script>alert('Invalid or expired OTP. Please try verifying again.'); window.location='otp_verify.php';</script>";
        exit();
    }

    // Mark OTP as verified in your otp_table to prevent reuse
    $stmt_mark_otp = $pdo->prepare("UPDATE otp_table SET is_verified = 1 WHERE id = :otp_id");
    $stmt_mark_otp->bindParam(':otp_id', $otp_record['id']);
    $stmt_mark_otp->execute();


    // Check if user (id_number) already exists in the users table
    $stmt_user_exists = $pdo->prepare("SELECT 1 FROM users WHERE id_number = :id_number");
    $stmt_user_exists->bindParam(':id_number', $id_number);
    $stmt_user_exists->execute();
    if ($stmt_user_exists->fetch()) {
        echo "<script>alert('This ID Number is already registered. Please login or contact support.'); window.location='../index.php';</script>"; // Redirect to login or main index
        exit();
    }

    // Fetch user details (firstname, lastname, username, AND role_id) from the 'ids' table
    // ***** MODIFIED TO FETCH role_id *****
    $stmt_id_data = $pdo->prepare("SELECT username, firstname, lastname, role_id FROM ids WHERE id_number = :id_number");
    $stmt_id_data->bindParam(':id_number', $id_number);
    $stmt_id_data->execute();
    $id_table_data = $stmt_id_data->fetch(PDO::FETCH_ASSOC);

    if (!$id_table_data) {
        echo "<script>alert('ID number not found in our pre-approved records. Please ensure your ID is correct or contact administration.'); window.location='index.php';</script>";
        exit();
    }

    // Extract data from 'ids' table
    $username_from_ids = $id_table_data['username'];
    $firstname_from_ids = $id_table_data['firstname'];
    $lastname_from_ids = $id_table_data['lastname'];
    $role_id_from_ids = $id_table_data['role_id']; // ***** FETCHED role_id *****

    // Validate that role_id was fetched and is not null (important for DB constraints if role_id is NOT NULL in users table)
    if ($role_id_from_ids === null || $role_id_from_ids === '') {
         // Clear sensitive session data
        unset($_SESSION['registration_data'], $_SESSION['phone_number'], $_SESSION['user_otp'], $_SESSION['otp_sent_time']);
        error_log("Registration Error: role_id is NULL for id_number: {$id_number} in 'ids' table.");
        echo "<script>alert('Critical error: User role information is missing for your ID. Please contact administration.'); window.location='index.php';</script>";
        exit();
    }


    // Insert new user into the 'users' table
    try {
        // ***** MODIFIED: Added 'role_id' to INSERT query and execute array *****
        $insert_query = "INSERT INTO users (
                            id_number, username, password, firstname, lastname, role_id, 
                            gender, phone, email, department, club_membership, 
                            is_class_representative, registration_date, status, account 
                         ) VALUES (
                            :id_number, :username, :password, :firstname, :lastname, :role_id, 
                            :gender, :phone, :email, :department, :club_membership, 
                            :is_class_representative, :registration_date, :status, :account
                         )";
        $stmt_insert_user = $pdo->prepare($insert_query);
        $stmt_insert_user->execute([
            ':id_number' => $id_number,
            ':username' => $username_from_ids, // Use username from ids table
            ':password' => $password_hashed,
            ':firstname' => $firstname_from_ids, // Use firstname from ids table
            ':lastname' => $lastname_from_ids,   // Use lastname from ids table
            ':role_id' => $role_id_from_ids,     // ***** USING role_id from ids table *****
            ':gender' => $gender,
            ':phone' => $phone_from_session,
            ':email' => $email,
            ':department' => $department,
            ':club_membership' => $club_membership,
            ':is_class_representative' => $is_class_representative,
            ':registration_date' => $registration_date,
            ':status' => 'Unvoted', // Default status for a new voter
            ':account' => 'Inactive'   // Default account state
        ]);

        // Clear session data after successful registration
        unset($_SESSION['registration_data'], $_SESSION['phone_number'], $_SESSION['user_otp'], $_SESSION['otp_sent_time']);

        // Optionally, log the user in automatically by setting session variables for logged-in state
        // $_SESSION['user_id'] = $id_number; // Or the auto-incremented ID from 'users' table if you retrieve it
        // $_SESSION['username'] = $username_from_ids;
        // $_SESSION['user_role'] = $role_id_from_ids;


        echo "<script>alert('Registration successful! You can now login.'); window.location='../login.php';</script>"; // Redirect to login page
        exit();

    } catch (PDOException $e) {
        // Log the detailed error for the admin
        error_log("Database Error during user insertion: " . $e->getMessage() . " for ID: " . $id_number);
        // Show a generic error to the user
        echo "<script>alert('An error occurred while finalizing your registration. Please try again or contact support if the problem persists.'); window.location='index.php';</script>";
        exit();
    }

} else {
    // If not a GET request, or if accessed directly without proper flow
    header("Location: index.php"); // Redirect to the initial registration page
    exit();
}
?>