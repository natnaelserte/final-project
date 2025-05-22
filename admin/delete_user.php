<?php
    require_once 'dbcon.php';
    $user_id = $_GET['user_id'];

    // Use prepared statements with PDO for security
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: user.php');
    exit;
?>