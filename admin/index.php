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
                    <h3 class="page-header">Voter List</h3>

                    <a href="voters_excel.php">
                        <button type="button" style="margin-right:14px;" id="print" class="pull-right btn btn-info">
                            <i class="fa fa-print"></i> Export Voters to Excel
                        </button>
                    </a>
                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;

                    <a href="activate_accounts.php" class="btn btn-danger btn-outline pull-right" style="margin-right:12px;" name="go">
                        <i>Activate Voter Accounts</i>
                    </a>
                    <a href="deactivate_accounts.php" class="btn btn-danger btn-outline pull-right" style="margin-right:12px;" name="go">
                        <i>Deactivate Voter Accounts</i>
                    </a>

                    <?php
                    require 'dbcon.php';

                    try {
                        // Check if there is an active voting event
                        $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
                        $query->execute();
                        $votingEvent = $query->fetch(PDO::FETCH_ASSOC);

                        if ($votingEvent) {
                            // If there is an active voting event, show the "Edit Voting" button
                    ?>
                            <button type="button" class="btn btn-warning pull-right" data-toggle="modal" data-target="#votingModal" style="margin-right:12px;">
                                <i class="fa fa-pencil-square-o"></i> Edit Voting
                            </button>
                    <?php
                        } else {
                            // If there is no active voting event, show the "Initiate Voting" button
                    ?>
                            <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#votingModal" style="margin-right:12px;">
                                <i class="fa fa-check-square-o"></i> Initiate Voting
                            </button>
                    <?php
                        }
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Error checking voting event: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                    ?>

                    <br />
                    <br />

                    <hr />

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="modal-title" id="myModalLabel">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <i class="fa fa-users"></i> Voters List
                                    </div>
                                </div>
                            </h4>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Names</th>
                                            <th>Gender</th>
                                            <th>voter_type</th>
                                            <th>phone_number</th>
                                            <th>Account</th>
                                            <th>Date Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'dbcon.php';

                                        try {
                                            // Fetch voters securely using PDO
                                            $query = $pdo->query("SELECT * FROM voters ORDER BY voters_id DESC");
                                            while ($row1 = $query->fetch(PDO::FETCH_ASSOC)) {
                                                $voters_id = htmlspecialchars($row1['voters_id']);
                                        ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row1['id_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['firstname'] . " " . $row1['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['voter_type']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['phone_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['account']); ?></td>
                                                    <td><?php echo htmlspecialchars($row1['date']); ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } catch (PDOException $e) {
                                            echo "<tr><td colspan='7'>Error fetching voters: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
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

        <!-- Voting Modal -->
        <div class="modal fade" id="votingModal" tabindex="-1" role="dialog" aria-labelledby="votingModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="votingModalLabel">
                            <?php
                            try {
                                // Check if there is an active voting event
                                $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
                                $query->execute();
                                $votingEvent = $query->fetch(PDO::FETCH_ASSOC);

                                if ($votingEvent) {
                                    echo "Edit Voting";
                                } else {
                                    echo "Initiate Voting";
                                }
                            } catch (PDOException $e) {
                                echo "Error";
                            }
                            ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="initiateVotingForm">
                            <div class="form-group">
                                <label for="votingTitle">Voting Title</label>
                                <input type="text" class="form-control" id="votingTitle" placeholder="Enter voting title" required <?php
                                                                                                                                        try {
                                                                                                                                            // Check if there is an active voting event
                                                                                                                                            $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
                                                                                                                                            $query->execute();
                                                                                                                                            $votingEvent = $query->fetch(PDO::FETCH_ASSOC);

                                                                                                                                            if ($votingEvent) {
                                                                                                                                                echo 'value="' . htmlspecialchars($votingEvent['title']) . '"';
                                                                                                                                            }
                                                                                                                                        } catch (PDOException $e) {
                                                                                                                                            echo "Error";
                                                                                                                                        }
                                                                                                                                        ?>>
                            </div>
                            <div class="form-group">
                                <label for="votingHours">Voting Duration (Hours)</label>
                                <input type="number" class="form-control" id="votingHours" placeholder="Enter duration in hours" min="1" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmVoting">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include('script.php'); ?>

        <script>
            $(document).ready(function() {
                $('#confirmVoting').click(function() {
                    var votingTitle = $('#votingTitle').val();
                    var votingHours = $('#votingHours').val();

                    if (votingTitle && votingHours) {
                        // AJAX call to process the voting initiation/editing
                        $.ajax({
                            type: 'POST',
                            url: 'initiate_voting.php', // Use the same file
                            data: {
                                title: votingTitle,
                                hours: votingHours,
                                edit: <?php
                                        try {
                                            // Check if there is an active voting event
                                            $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
                                            $query->execute();
                                            $votingEvent = $query->fetch(PDO::FETCH_ASSOC);

                                            if ($votingEvent) {
                                                echo 'true'; // Pass 'true' if editing
                                            } else {
                                                echo 'false'; // Pass 'false' if initiating
                                            }
                                        } catch (PDOException $e) {
                                            echo 'false';
                                        }
                                        ?>
                            },
                            success: function(response) {
                                alert(response); // Show response from the server
                                $('#votingModal').modal('hide'); // Hide the modal
                                location.reload(); // Refresh the page
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX Error: " + status + " - " + error);
                                alert("An error occurred while initiating/editing voting. Please check the console.");
                            }
                        });
                    } else {
                        alert('Please fill in all the fields.');
                    }
                });
            });
        </script>
</body>

</html>