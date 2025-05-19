<?php 
include('session.php'); 
include('head.php'); 
require 'dbcon.php';

// Fetch counts securely using PDO
try {
    $count = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC);
    $count1 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Voted'")->fetch(PDO::FETCH_ASSOC);
    $count2 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Unvoted'")->fetch(PDO::FETCH_ASSOC);
    $count3 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Male'")->fetch(PDO::FETCH_ASSOC);
    $count4 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Female'")->fetch(PDO::FETCH_ASSOC);
    $count5 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Active'")->fetch(PDO::FETCH_ASSOC);
    $count6 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Inactive'")->fetch(PDO::FETCH_ASSOC);

    // Fetch registrations grouped by month and gender
    $regDatesByGenderRaw = $pdo->query("
        SELECT 
            DATE_FORMAT(registration_date, '%Y-%m') AS reg_month, 
            gender,
            COUNT(*) AS count
        FROM users
        GROUP BY reg_month, gender
        ORDER BY reg_month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Pivot the data: months list and counts by gender per month
    $months = [];
    $countsByMonthGender = [];

    foreach ($regDatesByGenderRaw as $row) {
        $month = $row['reg_month'];
        $gender = $row['gender'];
        $countVal = (int)$row['count'];
        if (!in_array($month, $months)) {
            $months[] = $month;
        }
        $countsByMonthGender[$month][$gender] = $countVal;
    }

    sort($months);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Student Voter List</h3>
                </div>

               <!-- Buttons Section -->
<!-- Buttons Section with 2 rows, 3 buttons each -->
<div class="mb-4" style="max-width: 800px; margin: 0 auto;">
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <a href="voters.php" class="btn btn-primary btn-block btn-lg" style="font-weight: 700;">
                <i class="fa fa-users"></i> ALL Voters <span class="badge badge-light"><?php echo htmlspecialchars($count['total']); ?></span>
            </a>
        </div>
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <a href="voted.php" class="btn btn-success btn-block btn-lg" style="font-weight: 700;">
                <i class="fa fa-check-circle"></i> Voted <span class="badge badge-light"><?php echo htmlspecialchars($count1['total']); ?></span>
            </a>
        </div>
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <a href="unvoted.php" class="btn btn-danger btn-block btn-lg" style="font-weight: 700;">
                <i class="fa fa-times-circle"></i> Unvoted <span class="badge badge-light"><?php echo htmlspecialchars($count2['total']); ?></span>
            </a>
        </div>
    </div>
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <a href="#" class="btn btn-info btn-block btn-lg" style="font-weight: 700;">
                <i class="fa fa-male"></i> Male <span class="badge badge-light"><?php echo htmlspecialchars($count3['total']); ?></span>
            </a>
        </div>
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <a href="#" class="btn btn-warning btn-block btn-lg" style="font-weight: 700; color:#212529;">
                <i class="fa fa-female"></i> Female <span class="badge badge-light"><?php echo htmlspecialchars($count4['total']); ?></span>
            </a>
        </div>
        <div class="col-xs-12 col-sm-4" style="padding: 5px;">
            <button type="button" class="btn btn-danger btn-block btn-lg" data-toggle="modal" data-target="#deleteAllVotersModal" style="font-weight: 700;">
                <i class="fa fa-trash"></i> Delete All Voters
            </button>
        </div>
    </div>
</div>

                <br /><br /><hr />

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="modal-title" id="myModalLabel">
                            <div class="panel panel-primary">
                                <div class="panel-heading"><i class="fa fa-users"></i> Voters List</div>
                            </div>
                        </h4>
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
                                        <th>Account</th>
                                        <th>Status</th>
                                        <th>Date Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $query = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
                                        while ($row1 = $query->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($row1['id_number']) . "</td>
                                                <td>" . htmlspecialchars($row1['firstname'] . " " . $row1['lastname']) . "</td>
                                                <td>" . htmlspecialchars($row1['gender']) . "</td>
                                                <td>" . htmlspecialchars($row1['phone']) . "</td>
                                                <td>" . htmlspecialchars($row1['account']) . "</td>
                                                <td>" . htmlspecialchars($row1['status']) . "</td>
                                                <td>" . htmlspecialchars($row1['registration_date']) . "</td>
                                            </tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='7'>Error fetching voters: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <!-- Gender Chart -->
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading"><i class="fa fa-venus-mars"></i> Gender Distribution</div>
                            <div class="panel-body">
                                <div id="genderChart" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Voting Status Chart -->
                    <div class="col-md-6">
                        <div class="panel panel-success">
                            <div class="panel-heading"><i class="fa fa-check-square"></i> Voting Status</div>
                            <div class="panel-body">
                                <div id="votingStatusChart" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Account Status Chart -->
                    <div class="col-md-6">
                        <div class="panel panel-warning">
                            <div class="panel-heading"><i class="fa fa-user-circle"></i> Account Status</div>
                            <div class="panel-body">
                                <div id="accountStatusChart" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Dates Chart -->
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><i class="fa fa-calendar"></i> Registrations Over Time by Gender</div>
                            <div class="panel-body">
                                <div id="registrationDateChart" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Delete All Voters Modal -->
    <div class="modal fade" id="deleteAllVotersModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllVotersModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllVotersModalLabel">Confirm Delete All Voters</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Before deleting voters data kindly make sure you have printed the voting report first. Are you sure you want to delete all voters? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAllVoters">Delete All Voters</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('script.php'); ?>

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
    google.charts.load('current', {
        packages: ['corechart', 'bar']
    });
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        // Gender Chart
        var genderData = google.visualization.arrayToDataTable([
            ['Gender', 'Count'],
            ['Male', <?php echo $count3['total']; ?>],
            ['Female', <?php echo $count4['total']; ?>]
        ]);
        var genderOptions = {
            title: 'Gender Distribution',
            pieHole: 0.4,
            colors: ['#3366CC', '#FF6699']
        };
        var genderChart = new google.visualization.PieChart(document.getElementById('genderChart'));
        genderChart.draw(genderData, genderOptions);

        // Voting Status Chart
        var votingStatusData = google.visualization.arrayToDataTable([
            ['Status', 'Count'],
            ['Voted', <?php echo $count1['total']; ?>],
            ['Unvoted', <?php echo $count2['total']; ?>]
        ]);
        var votingStatusOptions = {
            title: 'Voting Status',
            pieHole: 0.4,
            colors: ['#28a745', '#dc3545']
        };
        var votingStatusChart = new google.visualization.PieChart(document.getElementById('votingStatusChart'));
        votingStatusChart.draw(votingStatusData, votingStatusOptions);

        // Account Status Chart
        var accountStatusData = google.visualization.arrayToDataTable([
            ['Account Status', 'Count'],
            ['Active', <?php echo $count5['total']; ?>],
            ['Inactive', <?php echo $count6['total']; ?>]
        ]);
        var accountStatusOptions = {
            title: 'Account Status',
            pieHole: 0.4,
            colors: ['#ffc107', '#6c757d']
        };
        var accountStatusChart = new google.visualization.PieChart(document.getElementById('accountStatusChart'));
        accountStatusChart.draw(accountStatusData, accountStatusOptions);

        // Registration Dates Multi-line Chart (Male vs Female)
        var regDataArray = [
            ['Month', 'Male', 'Female']
        ];

        <?php
        foreach ($months as $month) {
            $maleCount = isset($countsByMonthGender[$month]['Male']) ? $countsByMonthGender[$month]['Male'] : 0;
            $femaleCount = isset($countsByMonthGender[$month]['Female']) ? $countsByMonthGender[$month]['Female'] : 0;
            echo "regDataArray.push(['" . htmlspecialchars($month) . "', $maleCount, $femaleCount]);\n";
        }
        ?>

        var regData = google.visualization.arrayToDataTable(regDataArray);
        var regOptions = {
            title: 'Registrations Over Time by Gender',
            hAxis: {
                title: 'Month',
                slantedText: true,
                slantedTextAngle: 45
            },
            vAxis: {
                title: 'Number of Registrations'
            },
            legend: { position: 'bottom' },
            colors: ['#3366CC', '#FF6699'],
            curveType: 'function',
            pointSize: 5
        };
        var regChart = new google.visualization.LineChart(document.getElementById('registrationDateChart'));
        regChart.draw(regData, regOptions);
    }

    window.addEventListener('resize', drawCharts);

    // Delete all voters AJAX
    $(document).ready(function() {
        $('#confirmDeleteAllVoters').click(function() {
            $.ajax({
                type: 'POST',
                url: 'delete_all_voters.php',
                success: function(response) {
                    alert(response);
                    $('#deleteAllVotersModal').modal('hide');
location.reload();
},
error: function() {
alert('Error deleting voters.');
}
});
});
</body> </html> ```
