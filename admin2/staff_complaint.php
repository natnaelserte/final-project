<?php
include('session.php');
require_once('dbcon.php');
include('head.php'); 

// Fetch complaints from the database
$complaints = $pdo->query("SELECT * FROM report_complaints ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <title>Complaint Management</title>
    
    <style>
        /* Custom styles for centering and max-width */
        .container-custom {
            max-width: 900px; /* Set a maximum width */
            margin: 0 auto; /* Center it horizontally */
            padding: 15px;
        }

        .complaint-panel {
            border-left: 4px solid #337ab7;
            transition: box-shadow 0.3s ease-in-out;
        }
        .complaint-panel:hover {
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        .details {
            display: none;
        }

        /* Ensure the page doesn't become too wide on large screens */
        @media (min-width: 1200px) {
            .container-custom {
                max-width: 960px; /* Adjust the max-width for larger screens */
            }
        }

        /* Padding and margin adjustments for mobile responsiveness */
        @media (max-width: 767px) {
            .container-custom {
                padding-left: 10px;
                padding-right: 10px;
            }
        }
    </style>
</head>

<body class="bg-light">
<div id="wrapper">
<?php include('side_bar.php'); ?> 

<div id="page-wrapper">
    <div class="container-custom">
        <h2 class="text-primary page-header">Complaint Management</h2>
        <a href="index.php" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Back to Home</a>

        <?php foreach ($complaints as $complaint): ?>
            <div class="panel panel-default complaint-panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-9">
                            <p><strong>Username:</strong> <?= htmlspecialchars($complaint['username']) ?></p>
                            <div class="form-inline">
                                <label class="control-label"><strong>Status:</strong></label>
                                <select class="form-control input-sm status-selector" data-id="<?= $complaint['id'] ?>" data-status="<?= $complaint['status'] ?>">
                                    <option value="pending" <?= $complaint['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= $complaint['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <button class="btn btn-info btn-sm toggle-details">Show Details</button>
                        </div>
                    </div>

                    <div class="details well well-sm" style="margin-top:15px;">
                        <h4><strong>Subject:</strong> <?= htmlspecialchars($complaint['subject']) ?></h4>
                        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($complaint['description'])) ?></p>

                        <form action="submit_response.php" method="POST" class="form">
                            <div class="form-group">
                                <label for="response_<?= $complaint['id'] ?>">Response:</label>
                                <textarea class="form-control" name="response" id="response_<?= $complaint['id'] ?>" rows="3" required><?= htmlspecialchars($complaint['response'] ?? '') ?></textarea>
                            </div>
                            <input type="hidden" name="id" value="<?= $complaint['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">Submit Response</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
// Toggle details
$('.toggle-details').on('click', function() {
    var $details = $(this).closest('.panel-body').find('.details');
    $details.slideToggle();
    $(this).text($details.is(':visible') ? 'Hide Details' : 'Show Details');
});

// Update status
$('.status-selector').on('change', function() {
    var id = $(this).data('id');
    var status = $(this).val();

    $.post('update_status.php', { id: id, status: status })
        .fail(function() {
            alert('Error updating status.');
        });
});
</script>

<?php include('script.php'); ?>
</body>
</html>
