<?php
include('dbcon.php');

if (isset($_GET['user_id'])) {
    $voters_id = intval($_GET['user_id']); // Basic validation to ensure it's an integer

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('Location: voters.php');
            exit();
        } else {
            die("Error deleting user.");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>
