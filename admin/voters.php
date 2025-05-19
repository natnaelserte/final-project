<?php include('session.php'); ?>
<?php include('head.php'); ?>
<?php include('dbcon.php'); ?>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Voter List</h3>

                <!-- Export to Excel Button -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <a href="voters_excel.php" class="btn btn-lg btn-block" style="background-color:#17a2b8; color:white; font-weight:bold;">
                            <i class="fa fa-file-excel-o"></i> Export Voters to Excel
                        </a>
                    </div>
                </div>

                <!-- Stylish Activation/Deactivation Buttons -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="activate_accounts.php" class="btn btn-lg btn-block" style="background-color:#28a745; color:white; font-weight:bold;">
                            <i class="fa fa-check-circle"></i> Activate Voter Accounts
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="deactivate_accounts.php" class="btn btn-lg btn-block" style="background-color:#dc3545; color:white; font-weight:bold;">
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
                                        <th>Status</th>
                                        <th>Account</th>
                                        <th>Date Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require 'dbcon.php';
                                    $query = $pdo->query("SELECT * FROM users WHERE role_id = 3 ORDER BY user_id DESC");
                                    while ($row1 = $query->fetch(PDO::FETCH_ASSOC)) {
                                        $user_id = htmlspecialchars($row1['user_id']);
                                        $account = htmlspecialchars($row1['account']);
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row1['id_number']); ?></td>
                                            <td><?php echo htmlspecialchars($row1['firstname'] . " " . $row1['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($row1['gender']); ?></td>
                                            <td><?php echo htmlspecialchars($row1['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row1['status']); ?></td>
                                            <td><?php echo htmlspecialchars($row1['account']); ?></td>
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
                $activeCount = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Active' AND role_id = 3")->fetchColumn();
                $inactiveCount = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Inactive' AND role_id = 3")->fetchColumn();
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
            </div>
        </div>
    </div>
</div>

<?php include('script.php'); ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Account Status', 'Count'],
            ['Active', <?php echo $activeCount; ?>],
            ['Inactive', <?php echo $inactiveCount; ?>]
        ]);

        var options = {
            title: 'Voter Account Status',
            is3D: true,
            colors: ['#28a745', '#dc3545']
        };

        var chart = new google.visualization.PieChart(document.getElementById('voterPieChart'));
        chart.draw(data, options);
    }
</script>
</body>
</html>
