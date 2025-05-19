<?php
session_start();
include('admin/dbcon.php');
include('head.php');

try {
    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle AJAX mark-as-read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $announcement_id = $_POST['announcement_id'];
    try {
        $update_sql = "UPDATE announcements SET read_status = 1 WHERE id = :announcement_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
        $update_stmt->execute();
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting Announcements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <style>
    body { background-color: #f9f9f9; font-size: 13px; }
    .panel.announcement-panel {
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #ccc;
        margin-bottom: 12px;
        padding: 10px;
    }
    .panel-heading {
        padding: 6px 10px;
        background: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    .panel-heading h4 {
        margin: 0;
        font-size: 15px;
    }
    .announcement-meta {
        font-size: 11px;
        color: #777;
    }
    .panel-body {
        padding: 6px 10px;
    }
    .announcement-message {
        margin: 6px 0;
        line-height: 1.4;
    }
    .btn-read {
        padding: 4px 10px;
        font-size: 12px;
    }


    </style>
</head>
<body>
<?php include('view_banner.php'); ?>

<div class="container" style="margin-top: 30px; max-width: 700px;">

    <h3 class="text-center">📢 Voting Announcements</h3>

    <div id="announcement-list">
        <?php foreach ($announcements as $a): ?>
            <div class="panel panel-default announcement-panel" id="announcement-<?= htmlspecialchars($a['id']) ?>">
                <div class="panel-heading">
                    <h4><?= htmlspecialchars($a['title']) ?></h4>
                    <p class="announcement-meta">
                        <strong>Posted by:</strong> <?= htmlspecialchars($a['posted_by']) ?> |
                        <strong>Start:</strong> <?= htmlspecialchars($a['start_date']) ?> |
                        <strong>End:</strong> <?= htmlspecialchars($a['end_date']) ?>
                    </p>
                </div>
                <div class="panel-body">
                    <p class="announcement-message"><?= nl2br(htmlspecialchars($a['message'])) ?></p>
                    <?php if ($a['read_status'] == 0): ?>
                        <button class="btn btn-primary btn-read" onclick="markAsRead(<?= htmlspecialchars($a['id']) ?>)" id="btn-<?= $a['id'] ?>">Mark as Read</button>
                    <?php else: ?>
                        <button class="btn btn-default btn-read" disabled>Already Read</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<script>
function markAsRead(id) {
    var btn = $('#btn-' + id);
    btn.prop('disabled', true).text('Marking...');

    $.post('<?= basename(__FILE__) ?>', {
        mark_read: true,
        announcement_id: id
    }, function(res) {
        var data = JSON.parse(res);
        if (data.status === 'success') {
            btn.removeClass('btn-primary').addClass('btn-default').text('Already Read');
        } else {
            btn.prop('disabled', false).text('Try Again');
        }
    });
}
</script>
</body>
</html>
