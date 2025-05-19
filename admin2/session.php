<?php
require 'dbcon.php'; // Assuming this file establishes the PDO connection ($pdo)
session_start();

// Check whether the session variable 'id' is present or not
if (!isset($_SESSION['user_id']) || (trim($_SESSION['user_id']) == '')) { ?>
    <script>
        window.location = "index.php";
    </script>
<?php
    exit(); // Add exit() to prevent further execution after redirection
}

$session_id = $_SESSION['user_id'];

try {
    // Use a prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$session_id]);
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC); // Use FETCH_ASSOC for associative array

    if ($user_row) {
        $user_username = $user_row['firstname'] . " " . $user_row['lastname'];
    } else {
        // Handle the case where the user is not found
        $user_username = "User not found"; // Or redirect to an error page
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Database error: " . $e->getMessage();
    exit(); // Prevent further execution
}
?>