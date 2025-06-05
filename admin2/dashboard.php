<?php
// Start session if not already started (session.php usually handles this)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

 // For session management
include('head.php');   // For HTML head, CSS, etc.
require 'dbcon.php';   // For database connection

// Initialize counts and data
$val_total_candidates = 0;
$val_total_voters = 0;
$val_total_positions = 0;
$val_total_voted = 0;
$val_total_unvoted = 0;
$announcements = [];
$complaint_stats = ['pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'total' => 0]; // For new complaint chart
$error_message_db = null;
$current_date = date('Y-m-d H:i:s');

// --- Fetch data for Summary Cards, Charts, Announcements ---
try {
    // For Summary Cards & General Charts
    $val_total_candidates = $pdo->query("SELECT COUNT(*) FROM candidate")->fetchColumn();
    $val_total_voters = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $val_total_positions = $pdo->query("SELECT COUNT(*) FROM position")->fetchColumn();
    $val_total_voted = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Voted'")->fetchColumn();
    $val_total_unvoted = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Unvoted' OR status IS NULL OR status = ''")->fetchColumn();

    // For New Complaint Statistics Chart
    $stmt_complaints_stats = $pdo->query("SELECT status, COUNT(*) as count FROM report_complaints GROUP BY status");
    while ($row = $stmt_complaints_stats->fetch(PDO::FETCH_ASSOC)) {
        if (isset($complaint_stats[strtolower($row['status'])])) {
            $complaint_stats[strtolower($row['status'])] = (int)$row['count'];
        }
        $complaint_stats['total'] += (int)$row['count'];
    }


} catch (PDOException $e) {
    $error_message_db = "Database error fetching stats: " . htmlspecialchars($e->getMessage());
}

// --- Handle Announcement Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement_action'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $posted_by = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_POST['posted_by']) ? $_POST['posted_by'] : 'Staff Admin');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, posted_by, start_date, end_date)
                               VALUES (:title, :message, :posted_by, :start_date, :end_date)");
        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':posted_by' => $posted_by,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $_SESSION['success_announcement'] = "Announcement posted successfully!";
        header("Location: dashboard.php"); // Refresh
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_announcement'] = "Error posting announcement: " . $e->getMessage();
    }
}

// --- Fetch Active Announcements ---
try {
    $sql_announcements = "SELECT * FROM announcements WHERE end_date >= :current_date ORDER BY created_at DESC";
    $stmt_announcements = $pdo->prepare($sql_announcements);
    $stmt_announcements->execute([':current_date' => $current_date]);
    $announcements = $stmt_announcements->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message_db = ($error_message_db ? $error_message_db . "<br>" : "") . "Database error fetching announcements: " . htmlspecialchars($e->getMessage());
}

?>

<!-- Load Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawDashboardCharts);

  function drawDashboardCharts() {
    // Data for Voted vs Unvoted Pie Chart
    var votedData = google.visualization.arrayToDataTable([
      ['Status', 'Number of Voters'],
      ['Voted', <?php echo (int)$val_total_voted; ?>],
      ['Unvoted', <?php echo (int)$val_total_unvoted; ?>]
    ]);
    var votedOptions = {
      title: 'Voter Turnout',
      pieHole: 0.4,
      colors: ['#90D1CA', '#dc3545'], // Primary color for voted
      backgroundColor: 'transparent',
      legend: { position: 'bottom' }
    };
    var votedChart = new google.visualization.PieChart(document.getElementById('voted_pie_chart'));
    votedChart.draw(votedData, votedOptions);

    // Data for Bar Chart (Candidates, Voters, Positions)
    var overviewData = google.visualization.arrayToDataTable([
        ["Entity", "Total Count", { role: "style" } ],
        ["Candidates", <?php echo (int)$val_total_candidates; ?>, "#90D1CA"], // Primary color
        ["Registered Voters", <?php echo (int)$val_total_voters; ?>, "#75B5AE"], // Darker primary
        ["Election Positions", <?php echo (int)$val_total_positions; ?>, "#A8DCD6"] // Lighter primary
    ]);
    var overviewOptions = {
        title: "Election Overview",
        bar: {groupWidth: "70%"},
        legend: { position: "none" },
        hAxis: { title: 'Total Count', minValue: 0 },
        vAxis: { title: 'Entity' },
        backgroundColor: 'transparent',
        animation:{ duration: 1000, easing: 'out', startup: true }
    };
    var overviewChart = new google.visualization.BarChart(document.getElementById('overview_bar_chart'));
    overviewChart.draw(overviewData, overviewOptions);

    // Data for Complaint Status Pie Chart
    var complaintData = google.visualization.arrayToDataTable([
      ['Status', 'Number of Complaints'],
      ['Pending', <?php echo (int)$complaint_stats['pending']; ?>],
      ['In Progress', <?php echo (int)$complaint_stats['in_progress']; ?>],
      ['Resolved', <?php echo (int)$complaint_stats['resolved']; ?>]
    ]);
    var complaintOptions = {
      title: 'Complaint Status',
      pieHole: 0.4,
      colors: ['#ffc107', '#fd7e14', '#90D1CA'], // Primary color for resolved
      backgroundColor: 'transparent',
      legend: { position: 'bottom' }
    };
    var complaintChart = new google.visualization.PieChart(document.getElementById('complaint_status_chart'));
    complaintChart.draw(complaintData, complaintOptions);
  }
  // Redraw charts on window resize
  $(window).resize(function(){
    drawDashboardCharts();
  });
</script>

<style>
    /* Custom styles with #90D1CA as primary color */
    :root {
        --primary-color: #90D1CA;
        --primary-dark: #75B5AE;  /* Darker shade for hover/active states */
        --primary-light: #A8DCD6; /* Lighter shade for backgrounds */
        --primary-very-light: #E5F4F2; /* Very light shade for subtle backgrounds */
        --text-on-primary: #333333; /* Dark text on primary color */
        --text-light: #ffffff;
        --text-dark: #333333;
        --accent-color: #FF9E80; /* Complementary accent if needed */
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
    }

    .chart-container {
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        min-height: 350px; /* Ensure containers have some height */
    }
    .panel-heading { font-weight: bold; }
    .panel-footer .pull-left { line-height: 1.5; }

    .announcement-card-display {
        background: var(--primary-very-light);
        padding: 15px;
        border-left: 4px solid var(--primary-color);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    .announcement-card-display h5 {
        margin-top: 0;
        font-weight: bold;
        color: var(--primary-dark);
    }
    .announcement-card-display .meta {
        font-size: 0.9em;
        color: #6c757d;
        margin-top: 10px;
    }

    /* Style for the complaint link panel */
    .complaints-link-panel .panel-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 150px; /* Give it some height */
    }
    .complaints-link-panel .huge-icon {
        font-size: 3em;
        margin-bottom: 10px;
    }

    /* Override Bootstrap panel colors */
    .panel-primary {
        border-color: var(--primary-color);
    }
    .panel-primary > .panel-heading {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--text-dark);
    }
    .panel-primary a {
        color: var(--primary-dark);
    }
    .panel-primary a:hover {
        color: var(--primary-color);
    }

    /* Override button colors */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-dark);
        color: var(--text-dark);
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        color: var(--text-dark);
    }

    /* Override alert colors */
    .alert-info {
        background-color: var(--primary-very-light);
        border-color: var(--primary-light);
        color: var(--text-dark);
    }

    /* Panel success override */
    .panel-success {
        border-color: var(--primary-color);
    }
    .panel-success > .panel-heading {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--text-dark);
    }

    /* Button success override */
    .btn-success {
        background-color: var(--primary-color);
        border-color: var(--primary-dark);
        color: var(--text-dark);
    }
    .btn-success:hover, .btn-success:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        color: var(--text-dark);
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Election Management</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
    <link rel="icon" type="image/jpg" href="img/favicon.ico" />
</head>
<body>
<div id="wrapper">

    <?php include('side_bar.php'); // Include the sidebar ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="modern-page-header">
                    <h1><i class="fa fa-dashboard"></i> Admin Dashboard</h1>
                </div>
            </div>
        </div>

        <?php if ($error_message_db): ?>
            <div class="alert alert-danger"><?php echo $error_message_db; ?></div>
        <?php endif; ?>

        <!-- Summary Cards Row -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3"><i class="fa fa-users fa-2x"></i></div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo htmlspecialchars($val_total_candidates); ?></div>
                                <div>Total Candidates</div>
                            </div>
                        </div>
                    </div>
                    <a href="candidate.php">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3"><i class="fa fa-group fa-2x"></i></div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo htmlspecialchars($val_total_voters); ?></div>
                                <div>Total Voters</div>
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
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3"><i class="fa fa-sitemap fa-2x"></i></div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo htmlspecialchars($val_total_positions); ?></div>
                                <div>Total Positions</div>
                            </div>
                        </div>
                    </div>
                    <a href="add_position.php">
                        <div class="panel-footer">
                            <span class="pull-left">Manage Positions</span>
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
                            <div class="col-xs-3"><i class="fa fa-check-circle fa-2x"></i></div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo htmlspecialchars($val_total_voted); ?></div>
                                <div>Voters Voted</div>
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
        </div>
        <hr>

        <!-- Row for Charts -->
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-bar-chart"></i> Election Overview</h3>
                    </div>
                    <div class="panel-body">
                        <div id="overview_bar_chart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Voter Turnout</h3>
                    </div>
                    <div class="panel-body">
                        <div id="voted_pie_chart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-comments"></i> Complaint Status</h3>
                    </div>
                    <div class="panel-body">
                        <div id="complaint_status_chart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <!-- Row for Announcements and Complaint Link -->
        <div class="row">
            <!-- Announcements Section -->
            <div class="col-lg-8"> <!-- Adjusted width for Announcements -->
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-bullhorn fa-fw"></i> Announcements</h3>
                    </div>
                    <div class="panel-body" style="max-height: 450px; overflow-y: auto;">
                        <?php if (!empty($_SESSION['success_announcement'])): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_announcement']); unset($_SESSION['success_announcement']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['error_announcement'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_announcement']); unset($_SESSION['error_announcement']); ?></div>
                        <?php endif; ?>

                        <button class="btn btn-primary btn-sm pull-right" type="button" data-toggle="collapse" data-target="#postAnnouncementForm" aria-expanded="false" aria-controls="postAnnouncementForm" style="margin-bottom:10px;">
                            Post New Announcement
                        </button>
                        <div class="clearfix"></div>

                        <div class="collapse" id="postAnnouncementForm">
                            <div class="well">
                                <form method="POST" action="dashboard.php">
                                    <input type="hidden" name="post_announcement_action" value="1">
                                    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                                    <div class="form-group"><label>Message</label><textarea name="message" class="form-control" rows="3" required></textarea></div>
                                    <div class="form-group"><label>Posted By (Your Name/Dept)</label><input type="text" name="posted_by" class="form-control" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Staff Admin'; ?>" required></div>
                                    <div class="form-group"><label>Start Date & Time</label><input type="datetime-local" name="start_date" class="form-control" required value="<?php echo date('Y-m-d\TH:i'); ?>"></div>
                                    <div class="form-group"><label>End Date & Time</label><input type="datetime-local" name="end_date" class="form-control" required></div>
                                    <button type="submit" class="btn btn-success">Post Announcement</button>
                                </form>
                            </div>
                        </div>

                        <?php if (count($announcements) > 0): ?>
                            <?php foreach ($announcements as $a): ?>
                                <?php if (strtotime($a['start_date']) <= strtotime($current_date) && strtotime($a['end_date']) >= strtotime($current_date)): ?>
                                <div class="announcement-card-display">
                                    <h5><?= htmlspecialchars($a['title']) ?></h5>
                                    <p><?= nl2br(htmlspecialchars($a['message'])) ?></p>
                                    <div class="meta">Posted by: <?= htmlspecialchars($a['posted_by']) ?> | Active: <?= date('M d, Y H:i', strtotime($a['start_date'])) ?> to <?= date('M d, Y H:i', strtotime($a['end_date'])) ?></div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No active announcements at the moment.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Complaint Link Section -->
            <div class="col-lg-4"> <!-- Adjusted width for Complaint Link -->
                <div class="panel modern-chart-panel complaints-link-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-comments-o fa-fw"></i> Complaints Overview</h3>
                    </div>
                    <div class="panel-body text-center">
                        <i class="fa fa-exclamation-triangle fa-3x huge-icon" style="color: #ffc107; margin-bottom: 15px;" aria-hidden="true"></i>
                        <h4 style="color: #495057; margin-bottom: 20px;">Total Complaints: <?php echo htmlspecialchars($complaint_stats['total']); ?></h4>
                        <div style="margin-bottom: 20px;">
                            <div style="margin-bottom: 8px;">
                                <span class="badge" style="background-color: #ffc107; color: black; padding: 6px 12px;">
                                    Pending: <?php echo htmlspecialchars($complaint_stats['pending']); ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span class="badge" style="background-color: #fd7e14; padding: 6px 12px;">
                                    In Progress: <?php echo htmlspecialchars($complaint_stats['in_progress']); ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span class="badge" style="background-color: #28a745; padding: 6px 12px;">
                                    Resolved: <?php echo htmlspecialchars($complaint_stats['resolved']); ?>
                                </span>
                            </div>
                        </div>
                        <a href="staff_complaint.php" class="btn btn-success btn-block">
                            <i class="fa fa-external-link"></i> Manage Complaints
                        </a>
                    </div>
                </div>
            </div>
        </div> <!-- /.row for Announcements and Complaint Link -->

    </div> <!-- /#page-wrapper -->
</div> <!-- /#wrapper -->

<?php include('script.php'); // Include JavaScript files (should include jQuery and Bootstrap JS) ?>
</body>
</head>
</html>
