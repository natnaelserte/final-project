<?php
require 'dbcon.php';
session_start();// Check whether the session variable `id` is present or not
if (!isset($_SESSION['user_id']) || trim($_SESSION['user_id']) == '') { ?>
    <script>
        window.location = "index.php";
    </script>
<?php
    exit();
}
$session_id = $_SESSION['user_id'];
try {
    // Fetch user details securely using a prepared statement
    $query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $query->execute([$session_id]);
    $user_row = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user_row) {
        // If no user is found, redirect to login
        ?>
        <script>
            window.location = "index.php";
        </script>
        <?php
        exit();
    }

    $user_username = htmlspecialchars($user_row['firstname'] . " " . $user_row['lastname']);
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit();
}
?>