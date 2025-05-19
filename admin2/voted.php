<?php include('session.php'); ?>
<?php include('head.php'); ?>
<?php require 'dbcon.php'; ?>

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
<div id="wrapper">
    <?php include('side_bar.php'); ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Voted Students</h3>
            </div>

            <!-- Buttons -->
            <div class="mb-4" style="max-width: 800px; margin: 0 auto;">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="voters.php" class="btn btn-primary btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-users"></i> ALL Voters <span class="badge badge-light"><?php echo $count['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="voted.php" class="btn btn-success btn-block btn-lg active" style="font-weight: 700;">
                            <i class="fa fa-check-circle"></i> Voted <span class="badge badge-light"><?php echo $count1['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding: 5px;">
                        <a href="unvoted.php" class="btn btn-danger btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-times-circle"></i> Unvoted <span class="badge badge-light"><?php echo $count2['total']; ?></span>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6" style="padding: 5px;">
                        <a href="#" class="btn btn-info btn-block btn-lg" style="font-weight: 700;">
                            <i class="fa fa-male"></i> Males Voted <span class="badge badge-light"><?php echo $count3['total']; ?></span>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding: 5px;">
                        <a href="#" class="btn btn-warning btn-block btn-lg" style="font-weight: 700; color: #212529;">
                            <i class="fa fa-female"></i> Females Voted <span class="badge badge-light"><?php echo $count4['total']; ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <br><br><hr>

            <!-- Voted Table -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="modal-title">
                        <div class="panel panel-success">
                            <div class="panel-heading"><i class="fa fa-users"></i> Voted Voters List</div>
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
                                            <td>" . htmlspecialchars($row['id_number']) . "</td>
                                            <td>" . htmlspecialchars($row['firstname'] . " " . $row['lastname']) . "</td>
                                            <td>" . htmlspecialchars($row['gender']) . "</td>
                                            <td>" . htmlspecialchars($row['status']) . "</td>
                                            <td>" . htmlspecialchars($row['account']) . "</td>
                                            <td>" . htmlspecialchars($row['phone']) . "</td>
                                            <td>" . htmlspecialchars($row['registration_date']) . "</td>
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

            <!-- Charts container: pie charts side by side -->
            <div style="display: flex; justify-content: space-between; gap: 20px; max-width: 1200px; margin: 0 auto;">
                <div id="genderChart" style="flex: 1; height: 400px;"></div>
                <div id="votingStatusChart" style="flex: 1; height: 400px;"></div>
                <div id="accountStatusChart" style="flex: 1; height: 400px;"></div>
            </div>

            <!-- Line chart below pie charts -->
            <div id="registrationDateChart" style="max-width: 1200px; height: 400px; margin: 30px auto 0;"></div>

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
    genderChart.draw(genderData, { title: 'Gender Distribution', pieHole: 0.4, colors: ['#3366CC', '#FF6699'] });

    var votingStatusData = google.visualization.arrayToDataTable([
        ['Status', 'Count'],
        ['Voted', <?php echo $count1['total']; ?>],
        ['Unvoted', <?php echo $count2['total']; ?>]
    ]);
    var votingStatusChart = new google.visualization.PieChart(document.getElementById('votingStatusChart'));
    votingStatusChart.draw(votingStatusData, { title: 'Voting Status', pieHole: 0.4, colors: ['#28a745', '#dc3545'] });

    var accountStatusData = google.visualization.arrayToDataTable([
        ['Account Status', 'Count'],
        ['Active', <?php echo $count5['total']; ?>],
        ['Inactive', <?php echo $count6['total']; ?>]
    ]);
    var accountStatusChart = new google.visualization.PieChart(document.getElementById('accountStatusChart'));
    accountStatusChart.draw(accountStatusData, { title: 'Account Status', pieHole: 0.4, colors: ['#ffc107', '#6c757d'] });

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
        vAxis: { title: 'Number of Registrations' },
        legend: { position: 'bottom' },
        colors: ['#3366CC', '#FF6699'],
        curveType: 'function',
        pointSize: 5
    });
}

window.addEventListener('resize', drawCharts);
</script>

<?php include('script.php'); ?>
<?php include('edit_voters_modal.php'); ?>
</body>
</html>
