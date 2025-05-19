<?php
require 'dbcon.php'; // include your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? '';

    $valid_statuses = ['pending', 'in_progress', 'resolved'];

    if ($id && in_array($status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE report_complaints SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
        echo "Status updated successfully.";
    } else {
        echo "Invalid input.";
    }
}
?>
