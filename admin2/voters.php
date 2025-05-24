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
    <div id="wrapper" >

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

  

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif;  }
        .chart-container { margin-bottom: 40px; }
        button { margin: 10px 5px; padding: 10px; }
    </style>
</head>
<body>

<?php
// Simulated values (replace with your real DB query results)
$count1 = ['total' => 150]; // Voted
$count2 = ['total' => 50];  // Unvoted
$count3 = ['total' => 120]; // Male
$count4 = ['total' => 80];  // Female
$count5 = ['total' => 160]; // Active
$count6 = ['total' => 40];  // Inactive

// Example monthly data
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
$countsByMonthGender = [
    'Jan' => ['Male' => 10, 'Female' => 5],
    'Feb' => ['Male' => 20, 'Female' => 15],
    'Mar' => ['Male' => 30, 'Female' => 20],
    'Apr' => ['Male' => 25, 'Female' => 25],
    'May' => ['Male' => 35, 'Female' => 15]
];
?>

<div class="chart-container" id="genderChart" style="width:100%; height:300px;"></div>
<div class="chart-container" id="votingStatusChart" style="width:100%; height:300px;"></div>
<div class="chart-container" id="accountStatusChart" style="width:100%; height:300px;"></div>
<div class="chart-container" id="registrationDateChart" style="width:100%; height:300px;"></div>

<button onclick="exportChartAsImage('genderChart')">Export Gender Chart</button>
<button onclick="exportChartAsImage('votingStatusChart')">Export Voting Chart</button>
<button onclick="exportChartAsImage('accountStatusChart')">Export Account Chart</button>
<button onclick="exportChartAsImage('registrationDateChart')">Export Registration Chart</button>

<script>
google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    var genderData = google.visualization.arrayToDataTable([
        ['Gender', 'Count'],
        ['Male', <?php echo (int)$count3['total']; ?>],
        ['Female', <?php echo (int)$count4['total']; ?>]
    ]);
    var genderOptions = { title: 'Gender Distribution', pieHole: 0.4, colors: ['#3366CC', '#FF6699'] };
    new google.visualization.PieChart(document.getElementById('genderChart')).draw(genderData, genderOptions);

    var votingData = google.visualization.arrayToDataTable([
        ['Status', 'Count'],
        ['Voted', <?php echo (int)$count1['total']; ?>],
        ['Unvoted', <?php echo (int)$count2['total']; ?>]
    ]);
    var votingOptions = { title: 'Voting Status', pieHole: 0.4, colors: ['#28a745', '#dc3545'] };
    new google.visualization.PieChart(document.getElementById('votingStatusChart')).draw(votingData, votingOptions);

    var accountData = google.visualization.arrayToDataTable([
        ['Account', 'Count'],
        ['Active', <?php echo (int)$count5['total']; ?>],
        ['Inactive', <?php echo (int)$count6['total']; ?>]
    ]);
    var accountOptions = { title: 'Account Status', pieHole: 0.4, colors: ['#ffc107', '#6c757d'] };
    new google.visualization.PieChart(document.getElementById('accountStatusChart')).draw(accountData, accountOptions);

    var regDataArray = [['Month', 'Male', 'Female']];
    <?php
    foreach ($months as $month) {
        $m = isset($countsByMonthGender[$month]['Male']) ? $countsByMonthGender[$month]['Male'] : 0;
        $f = isset($countsByMonthGender[$month]['Female']) ? $countsByMonthGender[$month]['Female'] : 0;
        echo "regDataArray.push(['$month', $m, $f]);\n";
    }
    ?>
    var regData = google.visualization.arrayToDataTable(regDataArray);
    var regOptions = {
        title: 'Registrations Over Time by Gender',
        curveType: 'function',
        legend: { position: 'bottom' },
        hAxis: { title: 'Month', slantedText: true, slantedTextAngle: 45 },
        vAxis: { title: 'Registrations' },
        pointSize: 5
    };
    new google.visualization.LineChart(document.getElementById('registrationDateChart')).draw(regData, regOptions);
}

window.addEventListener('resize', drawCharts);

function exportChartAsImage(chartId) {
    html2canvas(document.getElementById(chartId)).then(canvas => {
        var link = document.createElement('a');
        link.download = chartId + '.png';
        link.href = canvas.toDataURL();
        link.click();
    });
}
</script>

</body>
</html>