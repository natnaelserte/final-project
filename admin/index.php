<?php include 'session.php'; // Include your head content ?>
<?php

// --- SESSION AND COMMON INCLUDES ---
require_once 'dbcon.php'; // Your database connection
// --- REDIRECT IF NOT LOGGED IN (Example - adapt to your login check) ---
if (!isset($_SESSION['user_id'])) { // Assuming 'user_id' is set upon successful login
    header("Location: login.php"); // Redirect to your login page
    exit();
}

// --- FETCH USERNAME FOR PAGE TITLE OR OTHER USES ---
$loggedInUserFirstName = "Admin"; // Default
$pageTitlePrefix = "";
if (isset($_SESSION['user_id'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT firstname FROM users WHERE user_id = :user_id");
        $stmtUser->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtUser->execute();
        if ($userRow = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
            $loggedInUserFirstName = htmlspecialchars($userRow['firstname']);
            $pageTitlePrefix = $loggedInUserFirstName . "'s ";
        }
    } catch (PDOException $e) {
        error_log("Error fetching admin name for dashboard title: " . $e->getMessage());
    }
}

// --- DATABASE QUERIES FOR STATS ---
$stats = [
    'total_voters' => 0,
    'active_voters' => 0,
    'inactive_voters' => 0,
    'gender_counts' => ['male' => 0, 'female' => 0], // Initialize to avoid errors if no data
    'batch_counts' => [],
    'recent_registrations' => [],
    'locked_accounts_count' => 0,
];

try {
    // For these dashboard stats, we are counting users with role_id = 3 as "voters".
    // If "Staff" also have role_id = 3 and should NOT be counted, you need an additional
    // condition to differentiate them (e.g., another column like 'user_type').
    // For now, role_id = 3 will include both voters and staff if they share that ID.
    $target_role_id = 3;

    // Total "Voters/Staff" (role_id = 3)
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = :role_id");
    $stmtTotal->execute(['role_id' => $target_role_id]);
    $stats['total_voters'] = $stmtTotal->fetchColumn();

    // Active "Voters/Staff"
    $stmtActive = $pdo->prepare("SELECT COUNT(*) FROM users WHERE account = 'Active' AND role_id = :role_id");
    $stmtActive->execute(['role_id' => $target_role_id]);
    $stats['active_voters'] = $stmtActive->fetchColumn();

    // Inactive "Voters/Staff"
    $stmtInactive = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (account = 'Inactive' OR account IS NULL) AND role_id = :role_id");
    $stmtInactive->execute(['role_id' => $target_role_id]);
    $stats['inactive_voters'] = $stmtInactive->fetchColumn();

    // "Voters/Staff" by Gender (Normalize 'male' and 'Male', 'female' and 'Female')
    $stmtGender = $pdo->prepare("SELECT LOWER(gender) as gender_group, COUNT(*) as count
                                FROM users
                                WHERE role_id = :role_id AND gender IS NOT NULL AND TRIM(gender) != ''
                                GROUP BY gender_group");
    $stmtGender->execute(['role_id' => $target_role_id]);
    $genderResults = $stmtGender->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['gender_counts']['male'] = $genderResults['male'] ?? 0; // Handle 'male'
    $stats['gender_counts']['female'] = $genderResults['female'] ?? 0; // Handle 'female'
    // If you have other gender strings like 'Male' with uppercase, the LOWER() handles it.

    // "Voters/Staff" by Batch (Extract last part of id_number after the last '/')
    $stmtBatch = $pdo->prepare("SELECT SUBSTRING_INDEX(id_number, '/', -1) as batch, COUNT(*) as count
                                FROM users
                                WHERE role_id = :role_id AND id_number IS NOT NULL AND TRIM(id_number) != ''
                                GROUP BY batch
                                ORDER BY CAST(batch AS UNSIGNED) ASC, batch ASC");
    $stmtBatch->execute(['role_id' => $target_role_id]);
    $stats['batch_counts'] = $stmtBatch->fetchAll(PDO::FETCH_ASSOC);

    // Recent Registrations (of "Voters/Staff")
    $stmtRecent = $pdo->prepare("SELECT firstname, lastname, registration_date
                                FROM users
                                WHERE role_id = :role_id AND registration_date IS NOT NULL
                                ORDER BY registration_date DESC, user_id DESC LIMIT 5");
    $stmtRecent->execute(['role_id' => $target_role_id]);
    $stats['recent_registrations'] = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    // Locked "Voter/Staff" Accounts
    $stmtLocked = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_locked = 1 AND role_id = :role_id");
    $stmtLocked->execute(['role_id' => $target_role_id]);
    $stats['locked_accounts_count'] = $stmtLocked->fetchColumn();

} catch (PDOException $e) {
    $db_error_message = "Database error fetching stats: " . htmlspecialchars($e->getMessage());
    error_log($db_error_message); // Log detailed error
}

// Prepare data for Chart.js
$genderChartLabels = json_encode(array_map('ucfirst', array_keys($stats['gender_counts']))); // Capitalize 'male', 'female'
$genderChartData = json_encode(array_values($stats['gender_counts']));

$batchChartLabels = json_encode(array_column($stats['batch_counts'], 'batch'));
$batchChartData = json_encode(array_column($stats['batch_counts'], 'count'));

include 'head.php'; // Include your head content
?>

<!DOCTYPE html> <!-- This DOCTYPE might already be in head.php -->
<html lang="en">  <!-- This lang might already be in head.php -->
<head>
    <!-- Meta tags, etc., are likely in head.php -->
    <title><?php echo $pageTitlePrefix; ?>Admin Dashboard</title>
    <!-- Link to your main admin CSS (e.g., from SB Admin or your custom one) -->
    <!-- Ensure this path is correct and it includes Bootstrap or your framework's CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css"> <!-- Example Bootstrap CSS -->
    <link rel="stylesheet" href="css/sb-admin.css"> <!-- Example SB Admin CSS -->
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css"> <!-- Example Font Awesome -->

    <!-- NEW CSS for dashboard specific styles -->
    <link rel="stylesheet" href="css/dashboard.css">
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="css/modern-admin.css">
    <!-- Google Fonts (if not already in head.php) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>


    <div id="wrapper"> <!-- Common wrapper for SB Admin like templates -->

        <?php include 'side_bar.php'; // Your existing sidebar, which includes the top navbar ?>

        <div id="page-wrapper"> <!-- Main content area for SB Admin like templates -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="modern-page-header">
                            <h1>
                                <i class="fa fa-dashboard"></i> Admin Dashboard <small>Statistics Overview</small>
                            </h1>
                        </div>
                        <ol class="breadcrumb">
                            <li class="active">
                                <i class="fa fa-home"></i> Dashboard Home
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /.row -->

                <?php if (isset($db_error_message)): ?>
                    <div class="alert alert-danger">
                        <strong>Error!</strong> <?php echo "Could not load all dashboard statistics. Please check system logs or contact support."; ?>
                    </div>
                <?php endif; ?>

                <!-- Stat Cards Row 1 -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-users fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo htmlspecialchars($stats['total_voters']); ?></div>
                                        <div>Total Users (Role 3)</div>
                                    </div>
                                </div>
                            </div>
                            <a href="voters.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
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
                                        <div class="huge"><?php echo htmlspecialchars($stats['active_voters']); ?></div>
                                        <div>Active Users</div>
                                    </div>
                                </div>
                            </div>
                             <a href="voters.php?account_status=active">
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
                                    <i class="fa fa-pause-circle fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo htmlspecialchars($stats['inactive_voters']); ?></div>
                                        <div>Inactive Users</div>
                                    </div>
                                </div>
                            </div>
                            <a href="voters.php?account_status=inactive">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php if ($stats['locked_accounts_count'] > 0): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card danger">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                    <i class="fa fa-lock fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo htmlspecialchars($stats['locked_accounts_count']); ?></div>
                                        <div>Locked Accounts</div>
                                    </div>
                                </div>
                            </div>
                            <a href="locked_accounts_view.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- /.row -->

                <!-- Charts and Lists Row 2 -->
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-venus-mars fa-fw"></i> By Gender (Role 3)</h3>
                            </div>
                            <div class="panel-body">
                                <canvas id="genderPieChart" style="max-height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                     <div class="col-lg-6 col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-layer-group fa-fw"></i> By Batch (Role 3)</h3>
                            </div>
                            <div class="panel-body" style="max-height: 290px; overflow-y: auto;">
                                <?php if (!empty($stats['batch_counts'])): ?>
                                <canvas id="batchBarChart" style="min-height: 250px;"></canvas>
                                <?php else: ?>
                                <p>No batch data available for users with role ID 3.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Quick Links and Recent Reg Row 3 -->
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-link fa-fw"></i> Quick Links</h3>
                            </div>
                            <div class="panel-body quick-links">
                                <a href="voters.php" class="btn btn-default btn-lg btn-block"><i class="fa fa-users fa-fw"></i> View Voters/Staff List</a>
                                <a href="activate_accounts.php" class="btn btn-success btn-lg btn-block"><i class="fa fa-toggle-on fa-fw"></i> Activate Accounts</a>
                                <a href="deactivate_accounts.php" class="btn btn-danger btn-lg btn-block"><i class="fa fa-toggle-off fa-fw"></i> Deactivate Accounts</a>

                                <!-- Add more links as needed -->
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-history fa-fw"></i> Recent Registrations (Role 3)</h3>
                            </div>
                            <div class="panel-body" style="max-height: 330px; overflow-y: auto;">
                                <div class="list-group">
                                    <?php if (!empty($stats['recent_registrations'])): ?>
                                        <?php foreach ($stats['recent_registrations'] as $reg): ?>
                                            <div class="list-group-item"> <!-- Changed to div for better control if links are not needed -->
                                                <i class="fa fa-user fa-fw"></i> <?php echo htmlspecialchars($reg['firstname'] . " " . $reg['lastname']); ?>
                                                <span class="pull-right text-muted small"><em><?php echo date("M d, Y", strtotime($reg['registration_date'])); ?></em></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="list-group-item">No recent registrations for users with role ID 3.</p>
                                    <?php endif; ?>
                                </div>
                                <?php if(count($stats['recent_registrations']) >=5 ): ?>
                                <a href="voters.php?sort=registration_date_desc" class="btn btn-default btn-block">View All Registrations</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins if your template uses them) -->
    <script src="js/jquery.js"></script> <!-- Adjust path as needed -->
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script> <!-- Adjust path as needed -->

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gender Pie Chart
        const genderCtx = document.getElementById('genderPieChart');
        if (genderCtx && <?php echo $genderChartData; ?>.reduce((a, b) => a + b, 0) > 0) { // Only render if there's data
            new Chart(genderCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo $genderChartLabels; ?>,
                    datasets: [{
                        label: 'Users by Gender',
                        data: <?php echo $genderChartData; ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)', // Blueish
                            'rgba(255, 99, 132, 0.8)', // Pinkish
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: false,
                        }
                    }
                }
            });
        } else if(genderCtx) {
            genderCtx.getContext('2d').fillText("No gender data available for role ID 3.", genderCtx.width / 2 - 60, genderCtx.height / 2);
        }

        // Batch Bar Chart
        const batchCtx = document.getElementById('batchBarChart');
        if (batchCtx && <?php echo $batchChartData; ?>.length > 0) { // Only render if there's data
             new Chart(batchCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $batchChartLabels; ?>,
                    datasets: [{
                        label: 'Users per Batch',
                        data: <?php echo $batchChartData; ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Makes it a horizontal bar chart if many batches
                    scales: {
                        x: { // Changed from y to x for horizontal bar
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                         title: {
                            display: false,
                        }
                    }
                }
            });
        } else if (batchCtx) {
             batchCtx.getContext('2d').fillText("No batch data available for role ID 3.", batchCtx.width / 2 - 60, batchCtx.height / 2);
        }
    });
    </script>
</body>
</html>