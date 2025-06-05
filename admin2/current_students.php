<?php include('session.php'); ?>
<?php include('head.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Current Students - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>

<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="modern-page-header">
                        <h1><i class="fa fa-users"></i> Student ID Management</h1>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards Section -->
            <div class="row" style="margin-bottom: 25px;">
                <div class="col-lg-12">
                    <?php
                    require 'dbcon.php';
                    $total_ids = 0;
                    try {
                        $count_query = $pdo->query("SELECT COUNT(*) as total FROM ids");
                        $total_ids = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
                    } catch (PDOException $e) {
                        // Handle error silently
                    }
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="panel modern-stat-card primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-id-card fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo $total_ids; ?></div>
                                        <div>Total Student IDs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Section -->
            <div class="action-buttons-container" style="margin-bottom: 25px;">
                <h4><i class="fa fa-cogs"></i> Management Actions</h4>
                <div class="row">
                    <div class="col-md-4">
                        <a href="download.php" class="btn btn-success btn-block">
                            <i class="fa fa-download"></i> Import Students Data
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="add_student_id.php" class="btn btn-success btn-block">
                            <i class="fa fa-plus"></i> Add Student ID
                        </a>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteAllIdsModal">
                            <i class="fa fa-trash"></i> Delete All IDs
                        </button>
                    </div>
                </div>
            </div>

            <!-- Student IDs Table -->
            <div class="panel modern-table-panel">
                <div class="panel-heading">
                    <i class="fa fa-table fa-fw"></i> Student IDs Directory
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover modern-table" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Added Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'dbcon.php';

                                        try {
                                            // Fetch all students from the database
                                            $query = $pdo->query("SELECT * FROM ids ORDER BY id_number DESC");
                                            while ($row1 = $query->fetch(PDO::FETCH_ASSOC)) {
                                                $voters_id = $row1['id_number'];
                                        ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($row1['id_number']); ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($row1['date']))); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        } catch (PDOException $e) {
                                            echo "<tr><td colspan='3'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->

                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->

                </div>
                <!-- /.row -->
            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->

        <!-- Delete All IDs Modal -->
        <div class="modal fade" id="deleteAllIdsModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllIdsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #dc3545; color: white;">
                        <h5 class="modal-title" id="deleteAllIdsModalLabel">
                            <i class="fa fa-exclamation-triangle"></i> Confirm Delete All IDs
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fa fa-warning"></i> <strong>Warning:</strong> Make sure the voters are registered and voted before proceeding.
                        </div>
                        <p>Are you sure you want to delete all Student IDs? This action cannot be undone and will permanently remove all student ID records from the system.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteAllIds">
                            <i class="fa fa-trash"></i> Delete All IDs
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php include('script.php'); ?>

        <script>
            $(document).ready(function() {
                $('#confirmDeleteAllIds').click(function() {
                    // AJAX call to delete all IDs
                    $.ajax({
                        type: 'POST',
                        url: 'delete_all_ids.php', // Create this file
                        success: function(response) {
                            alert(response); // Show response from the server
                            $('#deleteAllIdsModal').modal('hide'); // Hide the modal
                            location.reload(); // Refresh the page
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error: " + status + " - " + error);
                            alert("An error occurred while deleting all IDs. Please check the console.");
                        }
                    });
                });
            });
        </script>

</body>

</html>