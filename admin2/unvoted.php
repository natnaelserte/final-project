<?php
include('session.php');
include('head.php');
require 'dbcon.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Unvoted Users - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>
<?php

try {
    $count = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC);
    $count1 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Voted'")->fetch(PDO::FETCH_ASSOC);
    $count2 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Unvoted'")->fetch(PDO::FETCH_ASSOC);
    $count3 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Male'")->fetch(PDO::FETCH_ASSOC);
    $count4 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Female'")->fetch(PDO::FETCH_ASSOC);
    $count5 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Active'")->fetch(PDO::FETCH_ASSOC);
    $count6 = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Inactive'")->fetch(PDO::FETCH_ASSOC);

    $regDatesByGenderRaw = $pdo->query("
        SELECT
            DATE_FORMAT(registration_date, '%Y-%m') AS reg_month,
            gender,
            COUNT(*) AS count
        FROM users
        GROUP BY reg_month, gender
        ORDER BY reg_month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

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
    <?php include('side_bar.php'); ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="modern-page-header">
                    <h1><i class="fa fa-times-circle"></i> Unvoted Users</h1>
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
                    <a href="voted.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Voted</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
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
                    <div class="panel-footer">
                        <span class="pull-left">Current Page</span>
                        <span class="pull-right"><i class="fa fa-check"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card warning">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-trash fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge" style="font-size: 1.5em;">Manage</div>
                                <div>Delete All</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer" style="cursor: pointer;" data-toggle="modal" data-target="#deleteAllVotersModal">
                        <span class="pull-left">Delete All Voters</span>
                        <span class="pull-right"><i class="fa fa-trash"></i></span>
                        <div class="clearfix"></div>
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
                                <div>Total Males</div>
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
                                <div>Total Females</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="panel modern-table-panel">
            <div class="panel-heading">
                <i class="fa fa-table fa-fw"></i> Unvoted Users Directory
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover modern-table" id="dataTables-example">
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
                                    $query = $pdo->prepare("SELECT * FROM users WHERE status = 'Unvoted' ORDER BY user_id DESC");
                                    $query->execute();
                                    while ($row1 = $query->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>
                                            <td><strong>" . htmlspecialchars($row1['id_number']) . "</strong></td>
                                            <td>" . htmlspecialchars($row1['firstname'] . " " . $row1['lastname']) . "</td>
                                            <td><span class='badge badge-secondary'>" . htmlspecialchars($row1['gender']) . "</span></td>
                                            <td>" . htmlspecialchars($row1['phone']) . "</td>
                                            <td><span class='badge " . ($row1['account'] === 'Active' ? 'badge-success' : 'badge-warning') . "'>" . htmlspecialchars($row1['account']) . "</span></td>
                                            <td><span class='badge badge-danger'>" . htmlspecialchars($row1['status']) . "</span></td>
                                            <td>" . htmlspecialchars(date('M d, Y H:i', strtotime($row1['registration_date']))) . "</td>
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

        <!-- Charts Section -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-md-6 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Gender Distribution</h3>
                    </div>
                    <div class="panel-body">
                        <div id="genderChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-check-circle"></i> Voting Status</h3>
                    </div>
                    <div class="panel-body">
                        <div id="votingStatusChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-user-circle"></i> Account Status</h3>
                    </div>
                    <div class="panel-body">
                        <div id="accountStatusChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-line-chart"></i> Registration Timeline</h3>
                    </div>
                    <div class="panel-body">
                        <div id="registrationDateChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteAllVotersModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllVotersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete All Voters</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete all voters? This action cannot be undone. Please back up the report first.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAllVoters">Delete All Voters</button>
            </div>
        </div>
    </div>
</div>

<?php include('script.php'); ?>
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
        ['Account', 'Count'],
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
        $male = $countsByMonthGender[$month]['Male'] ?? 0;
        $female = $countsByMonthGender[$month]['Female'] ?? 0;
        echo "regDataArray.push(['$month', $male, $female]);\n";
    }
    ?>
    var regChart = new google.visualization.LineChart(document.getElementById('registrationDateChart'));
    regChart.draw(google.visualization.arrayToDataTable(regDataArray), {
        title: 'Registrations Over Time by Gender',
        hAxis: { title: 'Month', slantedText: true, slantedTextAngle: 45 },
        vAxis: { title: 'Registrations', minValue: 0, format: '0' },
        legend: { position: 'bottom' },
        curveType: 'function',
        colors: ['#90D1CA', '#75B5AE'],
        pointSize: 5,
        backgroundColor: 'transparent',
        chartArea: { left: 60, top: 40, width: '85%', height: '70%' }
    });
}

window.addEventListener('resize', drawCharts);

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
});
</script>
</body>
</html>
