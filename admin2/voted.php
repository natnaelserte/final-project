<?php include('session.php'); ?>
<?php include('head.php'); ?>
<?php require 'dbcon.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Voted Users - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>

<?php
try {
    // Total counts
    $count = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC);
    $count1 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Voted'")->fetch(PDO::FETCH_ASSOC);
    $count2 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Unvoted'")->fetch(PDO::FETCH_ASSOC);
    $count3 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Male' AND status = 'Voted'")->fetch(PDO::FETCH_ASSOC);
    $count4 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Female' AND status = 'Voted'")->fetch(PDO::FETCH_ASSOC);
    $count5 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Active'")->fetch(PDO::FETCH_ASSOC);
    $count6 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Inactive'")->fetch(PDO::FETCH_ASSOC);

    $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $countsByMonthGender = [];

    foreach ($months as $month) {
        foreach (['Male', 'Female'] as $gender) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE gender = :gender AND DATE_FORMAT(registration_date, '%M') = :month");
            $stmt->execute(['gender' => $gender, 'month' => $month]);
            $countsByMonthGender[$month][$gender] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<body>
<div id="wrapper " >
    <?php include('side_bar.php'); ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="modern-page-header">
                    <h1><i class="fa fa-check-circle"></i> Voted Users</h1>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Section -->
        <div class="row" style="margin-bottom: 25px;">
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count['total']; ?></div>
                                <div>Total Voters</div>
                            </div>
                        </div>
                    </div>
                    <a href="voters.php">
                        <div class="panel-footer">
                            <span class="pull-left">View All</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card success">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-check-circle fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count1['total']; ?></div>
                                <div>Voted</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <span class="pull-left">Current Page</span>
                        <span class="pull-right"><i class="fa fa-check"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card danger">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-times-circle fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count2['total']; ?></div>
                                <div>Unvoted</div>
                            </div>
                        </div>
                    </div>
                    <a href="unvoted.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Unvoted</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card info">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-percent fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count['total'] > 0 ? round(($count1['total'] / $count['total']) * 100, 1) : 0; ?>%</div>
                                <div>Turnout Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender Statistics Row -->
        <div class="row" style="margin-bottom: 25px;">
            <div class="col-lg-6 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-male fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count3['total']; ?></div>
                                <div>Males Voted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-female fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $count4['total']; ?></div>
                                <div>Females Voted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voted Table -->
        <div class="panel modern-table-panel">
            <div class="panel-heading">
                <i class="fa fa-table fa-fw"></i> Voted Users Directory
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover modern-table" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Names</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>Account</th>
                                    <th>Phone</th>
                                    <th>Date Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $query = $pdo->query("SELECT * FROM users WHERE status = 'Voted' ORDER BY user_id DESC");
                                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>
                                            <td><strong>" . htmlspecialchars($row['id_number']) . "</strong></td>
                                            <td>" . htmlspecialchars($row['firstname'] . " " . $row['lastname']) . "</td>
                                            <td><span class='badge badge-secondary'>" . htmlspecialchars($row['gender']) . "</span></td>
                                            <td><span class='badge badge-success'>" . htmlspecialchars($row['status']) . "</span></td>
                                            <td><span class='badge " . ($row['account'] === 'Active' ? 'badge-success' : 'badge-warning') . "'>" . htmlspecialchars($row['account']) . "</span></td>
                                            <td>" . htmlspecialchars($row['phone']) . "</td>
                                            <td>" . htmlspecialchars(date('M d, Y H:i', strtotime($row['registration_date']))) . "</td>
                                        </tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='7'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- Charts Section -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-md-4 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Gender Distribution</h3>
                    </div>
                    <div class="panel-body">
                        <div id="genderChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-check-circle"></i> Voting Status</h3>
                    </div>
                    <div class="panel-body">
                        <div id="votingStatusChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-user-circle"></i> Account Status</h3>
                    </div>
                    <div class="panel-body">
                        <div id="accountStatusChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Timeline Chart -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-line-chart"></i> Registration Timeline by Gender</h3>
                    </div>
                    <div class="panel-body">
                        <div id="registrationDateChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', { packages: ['corechart', 'bar'] });
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    var genderData = google.visualization.arrayToDataTable([
        ['Gender', 'Count'],
        ['Male', <?php echo $count3['total']; ?>],
        ['Female', <?php echo $count4['total']; ?>]
    ]);
    var genderChart = new google.visualization.PieChart(document.getElementById('genderChart'));
    genderChart.draw(genderData, {
        title: 'Gender Distribution',
        pieHole: 0.4,
        colors: ['#90D1CA', '#A8DCD6'],
        backgroundColor: 'transparent',
        legend: { position: 'bottom' }
    });

    var votingStatusData = google.visualization.arrayToDataTable([
        ['Status', 'Count'],
        ['Voted', <?php echo $count1['total']; ?>],
        ['Unvoted', <?php echo $count2['total']; ?>]
    ]);
    var votingStatusChart = new google.visualization.PieChart(document.getElementById('votingStatusChart'));
    votingStatusChart.draw(votingStatusData, {
        title: 'Voting Status',
        pieHole: 0.4,
        colors: ['#90D1CA', '#dc3545'],
        backgroundColor: 'transparent',
        legend: { position: 'bottom' }
    });

    var accountStatusData = google.visualization.arrayToDataTable([
        ['Account Status', 'Count'],
        ['Active', <?php echo $count5['total']; ?>],
        ['Inactive', <?php echo $count6['total']; ?>]
    ]);
    var accountStatusChart = new google.visualization.PieChart(document.getElementById('accountStatusChart'));
    accountStatusChart.draw(accountStatusData, {
        title: 'Account Status',
        pieHole: 0.4,
        colors: ['#90D1CA', '#6c757d'],
        backgroundColor: 'transparent',
        legend: { position: 'bottom' }
    });

    var regDataArray = [['Month', 'Male', 'Female']];
    <?php
    foreach ($months as $month) {
        $maleCount = $countsByMonthGender[$month]['Male'] ?? 0;
        $femaleCount = $countsByMonthGender[$month]['Female'] ?? 0;
        echo "regDataArray.push(['$month', $maleCount, $femaleCount]);\n";
    }
    ?>
    var regData = google.visualization.arrayToDataTable(regDataArray);
    var regChart = new google.visualization.LineChart(document.getElementById('registrationDateChart'));
    regChart.draw(regData, {
        title: 'Registrations Over Time by Gender',
        hAxis: { title: 'Month', slantedText: true, slantedTextAngle: 45 },
        vAxis: { title: 'Number of Registrations', minValue: 0, format: '0' },
        legend: { position: 'bottom' },
        colors: ['#90D1CA', '#75B5AE'],
        curveType: 'function',
        pointSize: 5,
        backgroundColor: 'transparent',
        chartArea: { left: 60, top: 40, width: '85%', height: '70%' }
    });
}

window.addEventListener('resize', drawCharts);
</script>

<?php include('script.php'); ?>
<?php include('edit_voters_modal.php'); ?>
</body>
</html>
