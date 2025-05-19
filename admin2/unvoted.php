<?php 
include('session.php'); 
include('head.php'); 
require 'dbcon.php';

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
                <h3 class="page-header">Unvoted Students</h3>
            </div>

            <!-- Button Section -->
            <div class="mb-4" style="max-width: 800px; margin: 0 auto;">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="voters.php" class="btn btn-primary btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-users"></i> ALL Voters <span class="badge badge-light"><?php echo $count['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="voted.php" class="btn btn-success btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-check-circle"></i> Voted <span class="badge badge-light"><?php echo $count1['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="unvoted.php" class="btn btn-danger btn-block btn-lg active" style="font-weight: 700;">
                            <i class="fa fa-times-circle"></i> Unvoted <span class="badge badge-light"><?php echo $count2['total']; ?></span>
                        </a>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="#" class="btn btn-info btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-male"></i> Male <span class="badge badge-light"><?php echo $count3['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="#" class="btn btn-warning btn-block btn-lg" style="font-weight: 700; color:#212529;">
                            <i class="fa fa-female"></i> Female <span class="badge badge-light"><?php echo $count4['total']; ?></span>
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

            <!-- Table Panel -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="modal-title" id="myModalLabel">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><i class="fa fa-users"></i> Unvoted Voters List</div>
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
                                    $query = $pdo->prepare("SELECT * FROM users WHERE status = 'Unvoted' ORDER BY user_id DESC");
                                    $query->execute();
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

            <!-- Charts Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-info">
                        <div class="panel-heading"><i class="fa fa-venus-mars"></i> Gender Distribution</div>
                        <div class="panel-body"><div id="genderChart" style="width:100%; height:300px;"></div></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-success">
                        <div class="panel-heading"><i class="fa fa-check-square"></i> Voting Status</div>
                        <div class="panel-body"><div id="votingStatusChart" style="width:100%; height:300px;"></div></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-warning">
                        <div class="panel-heading"><i class="fa fa-user-circle"></i> Account Status</div>
                        <div class="panel-body"><div id="accountStatusChart" style="width:100%; height:300px;"></div></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><i class="fa fa-calendar"></i> Registrations Over Time by Gender</div>
                        <div class="panel-body"><div id="registrationDateChart" style="width:100%; height:300px;"></div></div>
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
    genderChart.draw(genderData, { title: 'Gender Distribution', pieHole: 0.4, colors: ['#3366CC', '#FF6699'] });

    var votingStatusData = google.visualization.arrayToDataTable([
        ['Status', 'Count'],
        ['Voted', <?php echo $count1['total']; ?>],
        ['Unvoted', <?php echo $count2['total']; ?>]
    ]);
    var votingStatusChart = new google.visualization.PieChart(document.getElementById('votingStatusChart'));
    votingStatusChart.draw(votingStatusData, { title: 'Voting Status', pieHole: 0.4, colors: ['#28a745', '#dc3545'] });

    var accountStatusData = google.visualization.arrayToDataTable([
        ['Account', 'Count'],
        ['Active', <?php echo $count5['total']; ?>],
        ['Inactive', <?php echo $count6['total']; ?>]
    ]);
    var accountStatusChart = new google.visualization.PieChart(document.getElementById('accountStatusChart'));
    accountStatusChart.draw(accountStatusData, { title: 'Account Status', pieHole: 0.4, colors: ['#ffc107', '#6c757d'] });

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
        vAxis: { title: 'Registrations' },
        legend: { position: 'bottom' },
        curveType: 'function',
        colors: ['#3366CC', '#FF6699'],
        pointSize: 5
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
