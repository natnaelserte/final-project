<?php
session_start();
include('dbcon.php'); // Assumes $pdo is defined here (using PDO)
 // Include sidebar for navigation
include('head.php'); // Include session management
// Initialize announcements
$announcements = [];
$current_date = date('Y-m-d H:i:s');
$Session_id = $_SESSION['username'];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $posted_by = $_POST['posted_by']; // In this case, any name can be used here
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, posted_by, start_date, end_date) 
                               VALUES (:title, :message, :posted_by, :start_date, :end_date)");
        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':posted_by' => $posted_by,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $_SESSION['success'] = "Announcement posted successfully!";
        header("Location: post_announcement.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error posting announcement: " . $e->getMessage();
    }
}

// Fetch active announcements
try {
    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting Announcements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS v3 -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .announcement-card {
            background: #f8f9fa;
            padding: 20px;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            width: 600px;
        }
    </style>
</head>
<body>
<?php include('side_bar.php');?>
<div class="container mt-4" style="margin: 0 0 0 250px;">
    <h2 class="text-center mb-4">Voting Announcements</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Announcement Form (open to all users) -->
    <div class="panel panel-default mb-4">
        <div class="panel-heading">Post New Announcement</div>
        <div class="panel-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="datetime-local" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control" required>
                </div>
                <input type="hidden" name="posted_by" value="staff_user"> <!-- Replace with any default name or leave it as is -->
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </form>
        </div>
    </div>

    <!-- Announcement List -->
    <?php if (count($announcements) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Posted By</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($announcements as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= htmlspecialchars($a['posted_by']) ?></td>
                    <td><?= htmlspecialchars($a['start_date']) ?></td>
                    <td><?= htmlspecialchars($a['end_date']) ?></td>
                    <td><?= nl2br(htmlspecialchars($a['message'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">No active announcements at the moment.</div>
<?php endif; ?>

</div>

<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>
