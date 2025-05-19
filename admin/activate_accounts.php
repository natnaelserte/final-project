<?php
require_once 'dbcon.php';

try {
    // Update all voters' account to 'Active'
    $stmt = $pdo->prepare("UPDATE users SET account = 'Active'");
    $stmt->execute();

    // Redirect to the voters page with success message
    echo "<script>
        alert('All voter accounts have been activated successfully!');
        window.location = 'voters.php';
    </script>";
} catch (PDOException $e) {
    
    echo "<script>
        alert('Error activating accounts: " . htmlspecialchars($e->getMessage()) . "');
        window.location = 'voters.php';
    </script>";
}
?>