<?php include('session.php'); ?>
<?php include('head.php'); ?>

<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Currently Added Voters</h3>
                    <a href="download.php" class="btn btn-success ">
                        <i class="glyphicon glyphicon-save"></i> Import Students Data
                    </a>
                    <a href="add_student.php" class="btn btn-success ">
                        <i class="glyphicon glyphicon-save"></i> Add Student ID
                    </a>

                    <!-- Delete All IDs Button -->
                    <button type="button" class="btn btn-danger pull-right" data-toggle="modal" data-target="#deleteAllIdsModal">
                        <i class="fa fa-trash"></i> Delete All IDs
                    </button>

                    <hr />

                    <div class="panel panel-default">

                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
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
                                                    <td><?php echo htmlspecialchars($row1['id_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['date']); ?></td>
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAllIdsModalLabel">Confirm Delete All IDs</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Make sure the voters are Registered and voted. Are you sure you want to delete all IDs? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteAllIds">Delete All IDs</button>
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