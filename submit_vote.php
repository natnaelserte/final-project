<?php
include("admin/dbcon.php");
session_start();

// Include AES configuration
include("admin2/AES/aes_config.php");

// Voter ID from session
$voter_id = $_SESSION['user_id'] ?? null;

try {
    // Begin a transaction
    $pdo->beginTransaction();

    // Prepare the SQL statement for inserting votes
    $sql = "INSERT INTO `votes` (`candidate_id`, `voters_id`) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);

    // Fetch all positions from the position table
    $positions_query = $pdo->query("SELECT * FROM `position`");
    while ($position = $positions_query->fetch(PDO::FETCH_ASSOC)) {
        $position_name = strtolower(str_replace(' ', '_', $position['position_name']));
        $position_id_name = $position_name . '_id';

        // Get the candidate ID from the session
        $candidate_id = $_SESSION[$position_id_name] ?? null;

        // Insert the vote into the `votes` table only if a candidate was selected
        if (!empty($candidate_id)) {
            $candidate_id = (int)$candidate_id; // Cast to integer for security

            // Encrypt the candidate ID
            $encrypted_candidate_id = openssl_encrypt(
                $candidate_id,
                'aes-256-cbc',
                AES_KEY,
                0,
                AES_IV
            );

            $stmt->execute([$encrypted_candidate_id, $voter_id]);
        }
    }

    // Prepare and execute the update statement for voter status
    $update_sql = "UPDATE `users` SET `status` = 'Voted' WHERE `user_id` = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$voter_id]);

    // Commit the transaction
    $pdo->commit();

    // Destroy the session
    session_destroy();

    // Redirect to the thank you page
    header("location: thank_you.php");
    exit();
} catch (PDOException $e) {
    // Roll back the transaction in case of an error
    $pdo->rollBack();
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit();
}
?>