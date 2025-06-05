<?php
include('session.php'); // Handles session_start() and sets $_SESSION['user_id'] (ensure it's robust)
include('head.php');    // Includes Bootstrap CSS, Font Awesome, Theme CSS, etc.
require_once 'dbcon.php'; // Your PDO database connection object $pdo

// Ensure PDO object is available
if (!isset($pdo)) {
    error_log("FATAL ERROR: Database connection object (\$pdo) not available in voters_dashboard.php.");
    die("<div style='padding:20px; background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb;'>
            <strong>Critical Error:</strong> Database connection failed. Please contact the system administrator.
         </div>");
}

// Initialize counts to prevent errors if DB queries fail
$count = ['total' => 0];
$count1 = ['total' => 0]; // Voted
$count2 = ['total' => 0]; // Unvoted
$count3 = ['total' => 0]; // Male
$count4 = ['total' => 0]; // Female
$count5 = ['total' => 0]; // Active Accounts
$count6 = ['total' => 0]; // Inactive Accounts
$months = [];
$countsByMonthGender = [];

// Fetch counts securely using PDO
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    if($count_stmt) $count = $count_stmt->fetch(PDO::FETCH_ASSOC);

    $count1_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Voted'");
    if($count1_stmt) $count1 = $count1_stmt->fetch(PDO::FETCH_ASSOC);

    $count2_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'Unvoted'");
    if($count2_stmt) $count2 = $count2_stmt->fetch(PDO::FETCH_ASSOC);

    $count3_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Male'");
    if($count3_stmt) $count3 = $count3_stmt->fetch(PDO::FETCH_ASSOC);

    $count4_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'Female'");
    if($count4_stmt) $count4 = $count4_stmt->fetch(PDO::FETCH_ASSOC);

    $count5_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Active'");
    if($count5_stmt) $count5 = $count5_stmt->fetch(PDO::FETCH_ASSOC);

    $count6_stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE account = 'Inactive'");
    if($count6_stmt) $count6 = $count6_stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch registrations grouped by month and gender
    $regDatesByGenderRaw = $pdo->query("
        SELECT
            DATE_FORMAT(registration_date, '%Y-%m') AS reg_month,
            gender,
            COUNT(*) AS count
        FROM users
        WHERE registration_date IS NOT NULL /* Optional: exclude users with no registration_date */
        GROUP BY reg_month, gender
        ORDER BY reg_month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($regDatesByGenderRaw as $row) {
        $month = $row['reg_month'];
        $gender_val = $row['gender'] ?? 'Other'; // Default to 'Other' if gender is NULL
        $countVal = (int)$row['count'];
        if (!in_array($month, $months)) {
            $months[] = $month;
        }
        if (!isset($countsByMonthGender[$month])) {
            $countsByMonthGender[$month] = ['Male' => 0, 'Female' => 0, 'Other' => 0];
        }
        if (array_key_exists($gender_val, $countsByMonthGender[$month])) {
             $countsByMonthGender[$month][$gender_val] += $countVal; // Use += in case of multiple 'Other' entries for same month
        } else {
             $countsByMonthGender[$month]['Other'] += $countVal;
        }
    }
    sort($months);

} catch (PDOException $e) {
    // Log the error and potentially show a user-friendly message
    error_log("Database error on voters_dashboard.php: " . $e->getMessage());
    // The counts will remain 0, and charts will show "No data" messages
    echo "<div class='alert alert-warning'>Could not load all dashboard data due to a database issue. Some information may be missing.</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Voter Dashboard - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>

<body>
    <div id="wrapper">

        <?php include('side_bar.php'); ?>

        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="modern-page-header">
                            <h1><i class="fa fa-users"></i> Voter Dashboard & Analytics</h1>
                        </div>
                         <?php
                        // Display session messages if any
                        if (isset($_SESSION['message'])) {
                            $msg_type = $_SESSION['message']['type'] ?? 'info';
                            $msg_text = $_SESSION['message']['text'] ?? 'Action completed.';
                            echo "<div class='alert alert-{$msg_type} alert-dismissable'>
                                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                                    <i class='fa fa-info-circle'></i> " . htmlspecialchars($msg_text) . "
                                  </div>";
                            unset($_SESSION['message']);
                        }
                        ?>
                    </div>
                </div>

                <!-- Statistics Cards Section -->
                <div class="row" style="margin-bottom: 25px;">
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card primary" style="cursor: pointer;" id="showAllVotersBtn">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-users fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo htmlspecialchars($count['total']); ?></div>
                                        <div>Total Voters</div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
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
                                        <div class="huge"><?php echo htmlspecialchars($count1['total']); ?></div>
                                        <div>Voted</div>
                                    </div>
                                </div>
                            </div>
                            <a href="voted.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
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
                                        <div class="huge"><?php echo htmlspecialchars($count2['total']); ?></div>
                                        <div>Unvoted</div>
                                    </div>
                                </div>
                            </div>
                            <a href="unvoted.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card warning">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-user-times fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo htmlspecialchars($count6['total']); ?></div>
                                        <div>Inactive</div>
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
                                        <div class="huge"><?php echo htmlspecialchars($count3['total']); ?></div>
                                        <div>Male Voters</div>
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
                                        <div class="huge"><?php echo htmlspecialchars($count4['total']); ?></div>
                                        <div>Female Voters</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voter List Panel (Initially Hidden) -->
                <div class="panel modern-table-panel" id="votersListPanel" style="display:none; margin-top: 10px;">
                    <div class="panel-heading">
                         <div class="row">
                            <div class="col-xs-10 col-sm-11">
                                <i class="fa fa-table fa-fw"></i> Voters Directory
                            </div>
                            <div class="col-xs-2 col-sm-1 text-right">
                                <button type="button" class="btn btn-default btn-xs" id="hideVotersListBtn" title="Hide List" style="margin-top:2px;">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover modern-table" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>User ID</th> <!-- Changed from Student ID for generality -->
                                        <th>Names</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Account Status</th>
                                        <th>Vote Status</th>
                                        <th>Registered On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        // This query fetches ALL users.
                                        // If this page is specifically for "Student Voters", add "WHERE role_id = 3"
                                        // Or if for Students & Faculty, add "WHERE role_id IN (3,5)"
                                        $queryVoters = $pdo->query("SELECT user_id, id_number, firstname, lastname, gender, phone, account, status, registration_date FROM users ORDER BY user_id DESC");
                                        if ($queryVoters->rowCount() > 0) {
                                            while ($rowVoter = $queryVoters->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($rowVoter['id_number']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($rowVoter['firstname'] . " " . $rowVoter['lastname']); ?></td>
                                                    <td>
                                                        <span class="badge badge-secondary">
                                                            <?php echo htmlspecialchars($rowVoter['gender']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($rowVoter['phone'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $rowVoter['account'] === 'Active' ? 'badge-success' : 'badge-warning'; ?>">
                                                            <?php echo htmlspecialchars($rowVoter['account']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $rowVoter['status'] === 'Voted' ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo htmlspecialchars($rowVoter['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($rowVoter['registration_date']))); ?></td>
                                                </tr>
                                    <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>No voters found.</td></tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='7' class='text-center text-danger'>Error fetching voters: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                        error_log("Error fetching voters for table in voters_dashboard.php: " . $e->getMessage());
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- End Voters List Panel -->

                <!-- Charts Section -->
                <div class="row" style="margin-top: 30px;">
                    <div class="col-md-6 col-sm-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-pie-chart fa-fw"></i> Gender Distribution</h3>
                            </div>
                            <div class="panel-body">
                                <div id="genderChart" style="width:100%; height:350px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-check-circle fa-fw"></i> Voting Status</h3>
                            </div>
                            <div class="panel-body">
                                <div id="votingStatusChart" style="width:100%; height:350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-user-circle fa-fw"></i> Account Status</h3>
                            </div>
                            <div class="panel-body">
                                <div id="accountStatusChart" style="width:100%; height:350px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-line-chart fa-fw"></i> Registration Timeline</h3>
                            </div>
                            <div class="panel-body">
                                 <div id="registrationDateChart" style="width:100%; height:350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons for Charts -->
                <div class="action-buttons-container" style="margin-top:20px; margin-bottom: 50px;">
                    <h4><i class="fa fa-download"></i> Export Charts</h4>
                    <div class="text-center">
                        <button class="btn btn-success" onclick="exportChartAsImage('genderChart')">
                            <i class="fa fa-download"></i> Gender Chart
                        </button>
                        <button class="btn btn-success" onclick="exportChartAsImage('votingStatusChart')">
                            <i class="fa fa-download"></i> Voting Chart
                        </button>
                        <button class="btn btn-success" onclick="exportChartAsImage('accountStatusChart')">
                            <i class="fa fa-download"></i> Account Chart
                        </button>
                        <button class="btn btn-success" onclick="exportChartAsImage('registrationDateChart')">
                            <i class="fa fa-download"></i> Registration Chart
                        </button>
                    </div>
                </div>

            </div> <!-- /.container-fluid -->
        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <?php include('script.php'); // Includes jQuery, Bootstrap JS, DataTables JS ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    $(document).ready(function() {
        var dataTableInitialized = false;

        function initializeVotersDataTable() {
            if (!dataTableInitialized && $.fn.dataTable) {
                $('#dataTables-example').DataTable({
                    responsive: true,
                    "pageLength": 10,
                    "order": [[6, "desc"]] // Order by Date Registered descending
                });
                dataTableInitialized = true;
            }
        }

        $('#showAllVotersBtn').on('click', function(e) {
            e.preventDefault();
            var $votersPanel = $('#votersListPanel');
            // Toggle visibility with slide effect
            $votersPanel.slideToggle(400, function() {
                // After animation completes
                if ($votersPanel.is(':visible')) {
                    initializeVotersDataTable(); // Initialize if becoming visible
                    // Smooth scroll to the table if it's not already in view
                    if ($(window).scrollTop() > $votersPanel.offset().top - 70 ||
                        $(window).scrollTop() + $(window).height() < $votersPanel.offset().top + $votersPanel.height()) {
                        $('html, body').animate({
                            scrollTop: $votersPanel.offset().top - 70 // Adjust 70 for fixed navbar height
                        }, 500);
                    }
                }
            });
        });

        $('#hideVotersListBtn').on('click', function() {
            $('#votersListPanel').slideUp();
        });

        // Google Charts
        google.charts.load('current', { packages: ['corechart', 'line'] });
        google.charts.setOnLoadCallback(drawAllCharts);

        function drawAllCharts() {
            drawGenderChart();
            drawVotingStatusChart();
            drawAccountStatusChart();
            drawRegistrationDateChart();
        }

        function drawGenderChart() {
            var genderData = google.visualization.arrayToDataTable([
                ['Gender', 'Count'],
                ['Male', <?php echo (int)($count3['total'] ?? 0); ?>],
                ['Female', <?php echo (int)($count4['total'] ?? 0); ?>]
                // Add 'Other' if you track it: ['Other', <?php // echo (int)($count_other_gender['total'] ?? 0); ?>]
            ]);
            var genderOptions = {
                title: 'Gender Distribution',
                pieHole: 0.4,
                colors: ['#90D1CA', '#75B5AE', '#A8DCD6'], // Teal color palette
                legend: { position: 'bottom' },
                chartArea:{left:15,top:30,width:'90%',height:'75%'},
                pieSliceText: 'percentage',
                tooltip: { text: 'percentage' }
            };
            var genderChart = new google.visualization.PieChart(document.getElementById('genderChart'));
            genderChart.draw(genderData, genderOptions);
        }

        function drawVotingStatusChart() {
            var votingData = google.visualization.arrayToDataTable([
                ['Status', 'Count'],
                ['Voted', <?php echo (int)($count1['total'] ?? 0); ?>],
                ['Unvoted', <?php echo (int)($count2['total'] ?? 0); ?>]
            ]);
            var votingOptions = {
                title: 'Voting Status',
                pieHole: 0.4,
                colors: ['#90D1CA', '#dc3545'], // Teal for voted, keeping red for unvoted
                legend: { position: 'bottom' },
                chartArea:{left:15,top:30,width:'90%',height:'75%'},
                pieSliceText: 'percentage',
                tooltip: { text: 'percentage' }
            };
            var votingChart = new google.visualization.PieChart(document.getElementById('votingStatusChart'));
            votingChart.draw(votingData, votingOptions);
        }

        function drawAccountStatusChart(){
            var accountData = google.visualization.arrayToDataTable([
                ['Account', 'Count'],
                ['Active', <?php echo (int)($count5['total'] ?? 0); ?>],
                ['Inactive', <?php echo (int)($count6['total'] ?? 0); ?>]
            ]);
            var accountOptions = {
                title: 'Account Status',
                pieHole: 0.4,
                colors: ['#90D1CA', '#f0ad4e'], // Teal for active
                legend: { position: 'bottom' },
                chartArea:{left:15,top:30,width:'90%',height:'75%'},
                pieSliceText: 'percentage',
                tooltip: { text: 'percentage' }
            };
            var accountChart = new google.visualization.PieChart(document.getElementById('accountStatusChart'));
            accountChart.draw(accountData, accountOptions);
        }

        function drawRegistrationDateChart() {
            var regDataArray = [['Month', 'Male', 'Female', 'Other']];
            <?php
            if (!empty($months)) {
                foreach ($months as $month_key) {
                    $m_val = $countsByMonthGender[$month_key]['Male'] ?? 0;
                    $f_val = $countsByMonthGender[$month_key]['Female'] ?? 0;
                    $o_val = $countsByMonthGender[$month_key]['Other'] ?? 0;
                    echo "regDataArray.push(['" . htmlspecialchars(date("M Y", strtotime($month_key."-01"))) . "', " . $m_val . ", " . $f_val . ", " . $o_val . "]);\n";
                }
            }
            ?>
            var regData = google.visualization.arrayToDataTable(regDataArray);
            var regOptions = {
                title: 'Registrations Over Time by Gender',
                curveType: 'none',
                legend: { position: 'bottom' },
                hAxis: { title: 'Month', slantedText: false, textStyle: {fontSize: 10} },
                vAxis: { title: 'Number of Registrations', minValue: 0, format: '0', gridlines: {count: -1} },
                pointSize: 5,
                series: {
                    0: { color: '#90D1CA' }, // Male - teal
                    1: { color: '#75B5AE' }, // Female - darker teal
                    2: { color: '#A8DCD6', lineDashStyle: [4, 4] }  // Other - lighter teal, dashed line
                },
                chartArea:{left:60,top:40,width:'85%',height:'65%'}
            };
            if (regDataArray.length > 1) {
                new google.visualization.LineChart(document.getElementById('registrationDateChart')).draw(regData, regOptions);
            } else {
                 $('#registrationDateChart').html('<div class="text-center" style="padding:50px 10px; color:#777;">No registration data for timeline.</div>');
            }
        }

        var resizeTimerCharts; // Use a different timer for charts if needed
        $(window).resize(function(){
            clearTimeout(resizeTimerCharts);
            resizeTimerCharts = setTimeout(drawAllCharts, 250);
        });

    }); // End document ready

    function exportChartAsImage(chartId) {
        const chartElement = document.getElementById(chartId);
        const chartTitleElement = $(chartElement).closest('.panel').find('.panel-heading');
        let chartTitle = chartTitleElement.text().trim().replace(/\s+/g, '_').replace(/[^\w_]/g, '') || chartId;


        if (chartElement && (chartElement.innerHTML.includes('google-visualization') || chartElement.querySelector('svg') || chartElement.querySelector('canvas'))) {
            html2canvas(chartElement, {
                allowTaint: true,
                useCORS: true,
                logging: true, // Enable logging for debugging html2canvas
                onclone: function (clonedDoc) {
                    // Attempt to fix SVG issues if charts render as SVG and are complex
                    // This might not be necessary for simple Google Charts
                    $(clonedDoc).find('svg').each(function() {
                        var svg = this;
                        var xml = new XMLSerializer().serializeToString(svg);
                        var dataUrl = 'data:image/svg+xml;base64,' + window.btoa(unescape(encodeURIComponent(xml)));
                        var img = new Image();
                        img.src = dataUrl;
                        $(svg).replaceWith(img); // This might not work perfectly with Google Charts internal structure
                    });
                }
            }).then(canvas => {
                var link = document.createElement('a');
                link.download = chartTitle + '.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link); // Required for Firefox
                link.click();
                document.body.removeChild(link); // Clean up
            }).catch(err => {
                console.error('Error exporting chart using html2canvas:', err);
                alert('Could not export chart. See console for details. Error: ' + err.message);
            });
        } else {
            alert('Chart is not drawn or data is not available for export.');
        }
    }
    </script>
</body>
</html>
