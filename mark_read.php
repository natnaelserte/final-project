<?php

include('admin/dbcon.php');
include('head.php');

// Fetch latest 3 announcements
try {
    $sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Initialize unread announcements in session
if (!isset($_SESSION['unread_announcements'])) {
    $_SESSION['unread_announcements'] = array_column($announcements, 'id');
    $_SESSION['unread_count'] = count($_SESSION['unread_announcements']);
}

// Handle AJAX mark-as-read request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    if (in_array($id, $_SESSION['unread_announcements'])) {
        $_SESSION['unread_announcements'] = array_diff($_SESSION['unread_announcements'], [$id]);
        $_SESSION['unread_count'] = count($_SESSION['unread_announcements']);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'already_read']);
    }
    exit();
}
?>
