<?php
require_once 'dbcon.php'; // Ensure $pdo is available

if (isset($_POST['change']) && isset($_GET['user_id'])) { // Ensure user_id is also set
    $user_id = $_GET['user_id'];
    $username = trim($_POST['username'] ?? '');
    $new_password_from_form = $_POST['password'] ?? ''; // Password from the form
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $Phone = trim($_POST['Phone'] ?? ''); // Ensure consistent casing if column name is 'phone'
    $email = trim($_POST['email'] ?? '');

    // --- Basic Validation (add more as needed) ---
    if (empty($username) || empty($firstname) || empty($lastname) || empty($email) || empty($Phone)) {
        ?>
        <script type="text/javascript">
            alert('All fields except password are required.');
            window.location = 'user.php'; // Or user.php
        </script>
        <?php
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         ?>
        <script type="text/javascript">
            alert('Invalid email format.');
            window.location = 'user.php';
        </script>
        <?php
        exit;
    }
    // Add phone validation if needed

    try {
        // 1. Fetch the current password hash for the user
        $stmt_current_pass = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt_current_pass->execute([$user_id]);
        $current_db_password_hash = $stmt_current_pass->fetchColumn();

        if ($current_db_password_hash === false) {
            // User not found, should not happen if user_id is from a valid source
            ?>
            <script type="text/javascript">
                alert('User not found.');
                window.location = 'user.php';
            </script>
            <?php
            exit;
        }

        $password_to_save = ''; // This will hold the password to be saved in DB

        // 2. Check if the new password field from the form is empty
        if (empty($new_password_from_form)) {
            // Password field is empty, so user does not want to change it. Use the existing hash.
            $password_to_save = $current_db_password_hash;
        } else {
            // Password field is not empty, user might be changing it or re-typing the old one.
            // Validate new password length before hashing
            if (strlen($new_password_from_form) < 8) {
                 ?>
                <script type="text/javascript">
                    alert('New password must be at least 8 characters long.');
                    window.location = 'user.php';
                </script>
                <?php
                exit;
            }

            // 3. Verify if the new password matches the current one
            if (password_verify($new_password_from_form, $current_db_password_hash)) {
                // Submitted password is the same as the current password. Use the existing hash.
                $password_to_save = $current_db_password_hash;
            } else {
                // Submitted password is new and different. Hash it.
                $password_to_save = password_hash($new_password_from_form, PASSWORD_DEFAULT);
            }
        }

        // 4. Update user details with the determined password
        // Note: Ensure your 'Phone' column in DB matches the case used here.
        $stmt_update = $pdo->prepare("UPDATE users SET 
            username = ?, 
            password = ?, 
            firstname = ?, 
            lastname = ?, 
            phone = ?,  -- Changed 'Phone' to 'phone' assuming lowercase column name
            email = ? 
            WHERE user_id = ?");
        
        // Execute with consistent casing for phone if needed
        $stmt_update->execute([$username, $password_to_save, $firstname, $lastname, $Phone, $email, $user_id]);

        ?>
        <script type="text/javascript">
            alert('User updated successfully');
            window.location = 'user.php';
        </script>
        <?php
    } catch (PDOException $e) {
        // Log the detailed error for administrators
        error_log("Error updating user (ID: $user_id): " . $e->getMessage()); 
        ?>
        <script type="text/javascript">
            // Provide a more generic error to the user for security
            alert('An error occurred while updating the user. Please try again.');
            // Optionally redirect to the edit page or user list
            window.location = 'user.php'; 
            // window.location = 'user.php';
        </script>
        <?php
    }
} else {
    // Redirect if accessed directly or missing parameters
    // header('Location: user.php');
    // exit;
    // Or show an error message
    echo "Invalid request.";
}
?>