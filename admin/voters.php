<?php include('session.php'); ?>
<?php include('head.php'); // Ensure head.php links to the updated CSS file ?>

<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">

            <div class="row" style="margin-bottom: 20px;"> <!-- Added a row for these buttons -->
                <div class="col-md-6">
                   
                    <!-- Export and Account Actions -->
                    <?php
                    $editMode = false;
                    try {
                        $query = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
                        $query->execute();
                        $votingEvent = $query->fetch(PDO::FETCH_ASSOC);
                        $editMode = (bool)$votingEvent;

                        if ($editMode) {
                            // Edit button - Outline Warning Style
                            echo '<button type="button" class="btn btn-custom btn-custom-outline btn-warning-custom btn-lg btn-block " data-toggle="modal" data-target="#votingModal">
                                    <i class="fa fa-pencil-square-o"></i> Edit Voting
                                  </button>';
                        } else {
                            // Initiate button - Solid Primary Style
                            echo '<button type="button" class="btn btn-custom btn-custom-solid btn-info-custom btn-lg btn-block " data-toggle="modal" data-target="#votingModal">
                                    <i class="fa fa-check-square-o"></i> Initiate Voting
                                  </button>';
                        }
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                    ?>
                </div>
                <div class="col-md-6" style="margin-bottom: 10px;">
                    <a href="voters_excel.php" class="btn btn-custom btn-custom-solid btn-info-custom btn-lg btn-block">
                        <i class="fa fa-file-excel-o"></i> Export Voters to Excel
                    </a>
                </div>
            
                <div class="col-md-6">
                    <a href="activate_accounts.php" class="btn btn-custom btn-custom-solid btn-success-custom btn-lg btn-block">
                        <i class="fa fa-check-circle"></i> Activate Voter Accounts
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="deactivate_accounts.php" class="btn btn-custom btn-custom-solid btn-danger-custom btn-lg btn-block">
                        <i class="fa fa-times-circle"></i> Deactivate Voter Accounts
                    </a>
                </div>
            </div>

            <!-- Voter Table -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-users"></i> Voters List
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Names</th>
                                    <th>Gender</th>
                                    <th>Phone</th>
                                    <th>Department</th>         <!-- ADDED -->
                                    <th>Club Membership</th>    <!-- ADDED -->
                                    <th>Representative?</th>  <!-- ADDED -->
                                    <th>Status</th>
                                    <th>Account</th>
                                    <th>Date Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // require 'dbcon.php'; // Assuming dbcon.php contains $pdo
                                $queryUsers = $pdo->query("SELECT * FROM users WHERE role_id = 3 ORDER BY user_id DESC");
                                while ($row1 = $queryUsers->fetch(PDO::FETCH_ASSOC)) {
                                    $user_id = htmlspecialchars($row1['user_id']);
                                    $account = htmlspecialchars($row1['account']);
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row1['id_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row1['firstname'] . " " . $row1['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($row1['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row1['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row1['department'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row1['club_membership'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row1['is_class_representative'] ?? 'No'); ?></td>
                                        <td><?php echo htmlspecialchars($row1['status']); ?></td>
                                        <td><?php echo $account; ?></td>
                                        <td><?php echo htmlspecialchars($row1['registration_date']); ?></td>
                                        <td>
                                            <?php if ($account == 'Inactive') { ?>
                                                <a href="activate_voter.php?user_id=<?php echo $user_id; ?>" class="btn btn-success btn-sm">Activate</a>
                                            <?php } else { ?>
                                                <a href="deactivate_voter.php?user_id=<?php echo $user_id; ?>" class="btn btn-warning btn-sm">Deactivate</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Voter Count Boxes and Chart -->
            <?php
            $activeCount = 0;
            $inactiveCount = 0;
            if (isset($pdo)) {
                $activeCount = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Active' AND role_id = 3")->fetchColumn();
                $inactiveCount = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Inactive' AND role_id = 3")->fetchColumn();
            }
            ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-success">
                        <div class="panel-heading">Active Voters</div>
                        <div class="panel-body text-center">
                            <h2><?php echo $activeCount; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-danger">
                        <div class="panel-heading">Inactive Voters</div>
                        <div class="panel-body text-center">
                            <h2><?php echo $inactiveCount; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-pie-chart"></i> Voter Account Distribution
                </div>
                <div class="panel-body">
                    <div id="voterPieChart" style="height: 400px;"></div>
                </div>
            </div>
        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <!-- Voting Modal -->
    <div class="modal fade" id="votingModal" tabindex="-1" role="dialog" aria-labelledby="votingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="initiateVotingForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="votingModalLabel">
                            <?php echo $editMode ? "Edit Voting" : "Initiate Voting"; ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="votingTitle">Voting Title</label>
                            <input type="text" class="form-control" id="votingTitle" placeholder="Enter voting title" required
                            <?php
                            if ($editMode && isset($votingEvent['title'])) {
                                echo ' value="' . htmlspecialchars($votingEvent['title']) . '"';
                            }
                            ?>>
                        </div>
                        <div class="form-group">
                            <label for="votingHours">Voting Duration (Hours)</label>
                            <input type="number" class="form-control" id="votingHours" placeholder="Enter duration in hours" min="1" required
                            <?php
                            if ($editMode && isset($votingEvent['duration_hours'])) {
                                echo ' value="' . htmlspecialchars($votingEvent['duration_hours']) . '"';
                            }
                            ?>>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-custom btn-custom-outline btn-secondary-custom" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-custom btn-custom-solid btn-primary-custom" id="confirmVoting">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('script.php'); ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function () {
            $('#confirmVoting').click(function () {
                const title = $('#votingTitle').val();
                const hours = $('#votingHours').val();

                if (title && hours) {
                    $.ajax({
                        type: 'POST',
                        url: 'initiate_voting.php', 
                        data: {
                            title: title,
                            hours: hours,
                            edit: <?php echo $editMode ? 'true' : 'false'; ?>
                        },
                        success: function (response) {
                            alert(response); 
                            $('#votingModal').modal('hide');
                            location.reload(); 
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error: " + status + " - " + error);
                            alert("An error occurred while submitting voting data. Details: " + xhr.responseText);
                        }
                    });
                } else {
                    alert('Please fill in all fields.');
                }
            });

            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Account Status', 'Count'],
                    ['Active', <?php echo intval($activeCount); ?>],
                    ['Inactive', <?php echo intval($inactiveCount); ?>]
                ]);

                var options = {
                    title: 'Voter Account Status',
                    is3D: true,
                    colors: ['#28a745', '#dc3545'] 
                };

                var chart = new google.visualization.PieChart(document.getElementById('voterPieChart'));
                chart.draw(data, options);
            }
        });
    </script>
</body>
</html>