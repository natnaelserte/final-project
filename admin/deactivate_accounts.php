<?php
require_once 'dbcon.php';

try {
    // Update all voters' accounts to 'Inactive'
    $stmt = $pdo->prepare("UPDATE users SET account = 'Inactive'");
    $stmt->execute();

    // Redirect to the voters page with a success message
    echo "<script>
        alert('All voter accounts have been deactivated successfully!');
        window.location = 'voters.php';
    </script>";
} catch (PDOException $e) {
    // Handle database errors
    echo "<script>
        alert('Error deactivating accounts: " . htmlspecialchars($e->getMessage()) . "');
        window.location = 'voters.php';
    </script>";
}
?>