<?php
session_start();
include('admin/dbcon.php');

$session_login_username = $_SESSION['username'] ?? null;
$session_login_user_id=$_SESSION['user_id']??null;

if (!$session_login_username & !$session_login_user_id) {
    header("Location: login.php");
    exit;
}

$can_vote = false;
$all_user_complaints = [];
$num_pending = 0;
$num_in_progress = 0;
$num_resolved = 0;
$first_name_last_name = "Faculty Member";

try {
    $stmt_user = $pdo->prepare("SELECT status, account, id_number, firstname, lastname FROM users WHERE username = :login_username");
    $stmt_user->bindParam(':login_username', $session_login_username, PDO::PARAM_STR);
    $stmt_user->execute();
    $user_details = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_details) {
        $status_user = trim($user_details['status'] ?? '');
        $account_user = trim($user_details['account'] ?? '');
        $user_actual_id_number = $user_details['id_number'] ?? null;
        $first_name_last_name = htmlspecialchars(($user_details['firstname'] ?? '') . ' ' . ($user_details['lastname'] ?? 'User'));

        $can_vote = ($status_user === 'Unvoted' && $account_user === 'Active');

        if ($user_actual_id_number) {
            $stmt_complaints = $pdo->prepare("SELECT subject, response, status FROM report_complaints WHERE username = :user_id_for_complaints");
            $param_type_id_number = is_numeric($user_actual_id_number) && (strval(intval($user_actual_id_number)) === strval($user_actual_id_number)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt_complaints->bindParam(':user_id_for_complaints', $user_actual_id_number, $param_type_id_number);
            $stmt_complaints->execute();
            $all_user_complaints = $stmt_complaints->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        error_log("Faculty user not found in DB: " . $session_login_username);
    }

    foreach ($all_user_complaints as $complaint) {
        $c_status = strtolower(trim($complaint['status'] ?? ''));
        if ($c_status === 'resolved' || $c_status === 'closed') {
            $num_resolved++;
        } elseif ($c_status === 'in_progress' || $c_status === 'forwarded') {
            $num_in_progress++;
        } elseif ($c_status === 'pending' || $c_status === 'submitted' || $c_status === '') {
            $num_pending++;
        }
    }

} catch (PDOException $e) {
    error_log("Database error in faculty_home.php: " . $e->getMessage());
    echo "<div style='padding:20px; background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb;'>
            An error occurred while fetching data. Please try again later or contact support.
          </div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Faculty Dashboard</title>
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="admin2/css/sb-admin-2.css" rel="stylesheet">

    <style>
        /* Define primary color variables */
        :root {
            --primary-color: #90D1CA;
            --primary-dark: #75B5AE;
            --primary-light: #A8DCD6;
            --primary-very-light: #E5F4F2;
            --text-on-primary: #333333;

            /* Additional colors for complaint statistics */
            --pending-color: #FFA87D;      /* Orange for pending complaints */
            --in-progress-color: #FFD166;  /* Yellow/amber for in-progress complaints */
            --resolved-color: #06D6A0;     /* Green for resolved complaints */
        }

        /* Mobile-first responsive styles (from original) */
        @media (max-width: 767px) {
            #wrapper { padding-left: 0; }
            #page-wrapper { margin: 0; padding: 10px; min-height: calc(100vh - 50px); } /* 50px is approx navbar height */
            .sidebar { width: 100%; position: static; margin-top: 50px; z-index: 1; }
            .sidebar-nav { padding-bottom: 0; }
            .navbar-top-links { margin-right: 15px; }
            .navbar-top-links li a { padding: 10px; min-height: 50px; }
            .page-header { font-size: 20px; margin-top: 15px; padding-bottom: 10px; }
            .panel { margin-bottom: 15px; }
            .panel-heading { padding: 10px 15px; }
            .panel-body { padding: 10px; }
            .table-responsive { border: none; margin-bottom: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table { font-size: 12px; margin-bottom: 0; }
            .table > thead > tr > th, .table > tbody > tr > td { padding: 8px 5px; white-space: nowrap; }
            .btn-lg { padding: 8px 16px; font-size: 14px; display: block; width: 100%; margin-bottom: 10px; }
            .navbar-collapse { max-height: none; } /* Allow full height for mobile menu */
            .navbar-collapse.collapse { display: none !important; }
            .navbar-collapse.collapse.in { display: block !important; }
            .navbar-header .navbar-toggle { display: block; }
            .navbar-header { float: none; }
        }
        @media (min-width: 768px) and (max-width: 991px) {
            #page-wrapper { margin-left: 200px; }
            .sidebar { width: 200px; }
        }
        /* Custom styles for new dashboard elements */
        .dashboard-welcome {
            margin-bottom: 25px;
            padding-top: 10px;
        }
        .dashboard-welcome h2 {
            margin-top: 0;
            font-weight: 300;
            font-size: 26px;
            display: flex;
            align-items: center;
        }
        .dashboard-welcome h2 .fa-check-circle {
            color: var(--primary-color); /* Changed from #5cb85c */
            margin-right: 12px;
            font-size: 0.8em;
        }
        .dashboard-welcome p {
            font-size: 1.05em;
            color: #555;
            margin-top: 5px;
        }

        .section-header {
            font-size: 1.6em;
            font-weight: 300;
            margin-top: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .section-header i {
            margin-right: 10px;
            color: var(--primary-color); /* Changed from #5cb85c */
            font-size: 0.9em;
        }

        .stat-box {
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            min-height: 100px;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-box-icon {
            font-size: 2.5em;
            opacity: 0.9;
            margin-right: 15px;
            width: 50px;
            text-align: center;
        }
        .stat-box-content {
            flex-grow: 1;
        }
        .stat-box .huge {
            font-size: 2.2em;
            font-weight: bold;
            line-height: 1;
        }
        .stat-box .stat-title {
            font-size: 0.95em;
            margin-top: 3px;
        }
        .stat-box-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 3px 15px;
            background-color: rgba(0,0,0,0.15);
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            text-align: right;
        }
        .stat-box-footer a {
            color: white;
            text-decoration: none;
            font-size: 0.85em;
        }
        .stat-box-footer a:hover {
            text-decoration: underline;
        }

        .stat-box-pending {
            background-color: var(--pending-color); /* Orange for pending */
        }
        .stat-box-in-progress {
            background-color: var(--in-progress-color); /* Yellow for in progress */
        }
        .stat-box-resolved {
            background-color: var(--resolved-color); /* Green for resolved */
        }

        .quick-links-section {
            margin-top: 0;
            margin-bottom: 30px;
        }
        .quick-link-item {
            background-color: #ffffff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: all 0.2s ease-in-out;
        }
        .quick-link-item:hover {
            background-color: #f5f5f5;
            text-decoration: none;
            color: var(--primary-color); /* Changed from #005cbf */
            border-color: #c5c5c5;
        }
        .quick-link-icon {
            font-size: 1.6em;
            margin-right: 15px;
            color: var(--primary-color); /* Changed from #005cbf */
            width: 35px;
            text-align: center;
        }
        .quick-link-text strong {
            display: block;
            font-size: 1.05em;
            margin-bottom: 2px;
        }
        .quick-link-text span {
            font-size: 0.85em;
            color: #666;
        }
        /* Complaints Table Specific Styling */
        #complaintsSection .panel-primary .panel-heading {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
        }
        #complaintsSection .panel-primary {
            border-color: var(--primary-color) !important;
        }

        /* Panel body background */
        #complaintsSection .panel-primary .panel-body {
            background-color: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
        }

        /* Table styling for primary background */
        #complaintsSection .table {
            background-color: white !important;
            margin-bottom: 0 !important;
        }

        /* Table header styling */
        #complaintsSection .table thead th {
            background-color: var(--primary-dark) !important;
            color: white !important;
            border-color: var(--primary-dark) !important;
            font-weight: 600 !important;
        }

        /* Table body styling */
        #complaintsSection .table tbody td {
            background-color: white !important;
            color: #333 !important;
            border-color: var(--primary-color) !important;
        }

        /* Table border styling */
        #complaintsSection .table-bordered {
            border-color: var(--primary-color) !important;
        }

        #complaintsSection .table-bordered > thead > tr > th,
        #complaintsSection .table-bordered > tbody > tr > th,
        #complaintsSection .table-bordered > tfoot > tr > th,
        #complaintsSection .table-bordered > thead > tr > td,
        #complaintsSection .table-bordered > tbody > tr > td,
        #complaintsSection .table-bordered > tfoot > tr > td {
            border-color: var(--primary-color) !important;
        }

        /* Table row hover effect */
        #complaintsSection .table-striped > tbody > tr:hover {
            background-color: var(--primary-very-light) !important;
        }

        /* Table striped rows with primary color theme */
        #complaintsSection .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(144, 209, 202, 0.15) !important;
        }

        /* Text styling for "No complaints found" message */
        #complaintsSection .text-muted {
            color: var(--text-on-primary) !important;
            font-style: italic;
        }

        @media (max-width: 991px) {
            .stat-box { flex-direction: row; }
        }
        @media (max-width: 767px) {
             .stat-box {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            .stat-box-icon { margin-bottom: 10px; font-size: 2.2em; }
            .stat-box .huge { font-size: 2em; }
            .section-header { font-size: 1.4em; }
        }

        /* Modal specific styles (if needed, Bootstrap usually handles this well) */
        #changePasswordModal .modal-header {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
        }
        #changePasswordModal .modal-title {
            font-weight: 300;
        }
        #changePasswordModal .help-block {
            font-size: 0.9em;
        }
        #otpDisplay {
            font-weight: bold;
            color: var(--primary-dark); /* Changed from #31708f */
        }

        /* Override Bootstrap colors */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-dark);
            color: var(--text-on-primary);
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: var(--text-on-primary);
        }

        .text-info {
            color: var(--primary-dark) !important;
        }

        .text-success {
            color: var(--primary-color) !important;
        }

        .panel-primary {
            border-color: var(--primary-color) !important;
        }
        .panel-primary > .panel-heading {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
        }
    </style>
</head>
<body>

<div id="wrapper">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom:0; background-color: #90D1CA;">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar" style="background-color: white;"></span>
                <span class="icon-bar" style="background-color: white;"></span>
                <span class="icon-bar" style="background-color: white;"></span>
            </button>
            <a class="navbar-brand" href="faculty_home.php" style="color:#333333;">
                <i class="fa fa-university"></i> Faculty Portal
            </a>
        </div>

        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: #333333;">
                    <i class="fa fa-user"></i> Welcome, <?php echo $first_name_last_name; ?> <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-user">
                    <li>
                        <a href="#" data-toggle="modal" data-target="#changePasswordModal"><i class="fa fa-key fa-fw"></i> Change Password</a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
            </li>
        </ul>

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="faculty_home.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <?php if ($can_vote): ?>
                            <a href="vote.php"><i class="fa fa-check-circle fa-fw"></i> Vote Now</a>
                        <?php else: ?>
                            <a href="javascript:void(0);" onclick="showNotEligibleMessage()"><i class="fa fa-times-circle fa-fw"></i> Vote</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <a href="report_complaint.php"><i class="fa fa-comment fa-fw"></i> Report Issue/Concern</a>
                    </li>
                    <li>
                        <a href="#complaintsSection"><i class="fa fa-list fa-fw"></i> View My Reports</a>
                    </li>
                    <li>
                        <a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 dashboard-welcome">
                    <h2>Welcome, <?php echo $first_name_last_name; ?>!</h2>
                    <p>This dashboard provides an overview of your submitted reports, account actions, and voting status. You can view report statuses, submit new ones, and manage your settings.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <h3 class="section-header">
                        <i class="fa fa-pie-chart"></i>Report Statistics
                    </h3>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="stat-box stat-box-pending">
                        <div class="stat-box-icon"><i class="fa fa-inbox"></i></div>
                        <div class="stat-box-content">
                            <div class="huge"><?= $num_pending ?></div>
                            <div class="stat-title">Submitted / Pending</div>
                        </div>
                        <div class="stat-box-footer"><a href="#complaintsSection">View Details <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="stat-box stat-box-in-progress">
                        <div class="stat-box-icon"><i class="fa fa-share-square-o"></i></div>
                        <div class="stat-box-content">
                            <div class="huge"><?= $num_in_progress ?></div>
                            <div class="stat-title">In Progress</div>
                        </div>
                        <div class="stat-box-footer"><a href="#complaintsSection">View Details <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="stat-box stat-box-resolved">
                        <div class="stat-box-icon"><i class="fa fa-check-square-o"></i></div>
                        <div class="stat-box-content">
                            <div class="huge"><?= $num_resolved ?></div>
                            <div class="stat-title">Resolved / Closed</div>
                        </div>
                        <div class="stat-box-footer"><a href="#complaintsSection">View Details <i class="fa fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <h3 class="section-header"><i class="fa fa-link"></i>Quick Links</h3>
                </div>
            </div>
            <div class="row quick-links-section">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <a href="#complaintsSection" class="quick-link-item">
                        <div class="quick-link-icon"><i class="fa fa-list-alt"></i></div>
                        <div class="quick-link-text"><strong>My Reports</strong><span>View all your submitted reports and their statuses.</span></div>
                    </a>
                </div>
                 <div class="col-lg-6 col-md-6 col-sm-12">
                    <a href="#" data-toggle="modal" data-target="#changePasswordModal" class="quick-link-item">
                        <div class="quick-link-icon"><i class="fa fa-cog"></i></div>
                        <div class="quick-link-text"><strong>Account Settings</strong><span>Manage your account security settings.</span></div>
                    </a>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <a href="report_complaint.php" class="quick-link-item">
                        <div class="quick-link-icon"><i class="fa fa-pencil-square-o"></i></div>
                        <div class="quick-link-text"><strong>Submit New Report</strong><span>Submit a new issue, concern, or feedback.</span></div>
                    </a>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                     <?php if ($can_vote): ?>
                        <a href="vote.php" class="quick-link-item">
                            <div class="quick-link-icon" style="color: #4cae4c;"><i class="fa fa-check-circle-o"></i></div>
                            <div class="quick-link-text"><strong>Vote Now</strong><span>Cast your vote in current elections.</span></div>
                        </a>
                    <?php else: ?>
                        <a href="javascript:void(0);" onclick="showNotEligibleMessage()" class="quick-link-item" style="opacity: 0.7; cursor: not-allowed;">
                             <div class="quick-link-icon" style="color: #d9534f;"><i class="fa fa-times-circle-o"></i></div>
                            <div class="quick-link-text"><strong>Vote</strong><span>Check your voting eligibility or status.</span></div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row" id="complaintsSection">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-list"></i> Your Reports & Responses</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($all_user_complaints)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead><tr><th>Report Subject</th><th>Response</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($all_user_complaints as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['subject']) ?></td>
                                                    <td><?= htmlspecialchars($row['response'] ?? '<em>No response yet</em>') ?></td>
                                                    <td>
                                                        <?php
                                                            $status_complaint = strtolower(trim($row['status'] ?? ''));
                                                            $label_class = 'default';
                                                            if ($status_complaint === 'resolved' || $status_complaint === 'closed') $label_class = 'success';
                                                            elseif ($status_complaint === 'in_progress' || $status_complaint === 'forwarded') $label_class = 'warning';
                                                            elseif ($status_complaint === 'pending' || $status_complaint === 'submitted' || $status_complaint === '') $label_class = 'info';
                                                            $display_status = $row['status'] ? $row['status'] : 'Pending';
                                                        ?>
                                                        <span class="label label-<?= $label_class ?>"><?= ucfirst(htmlspecialchars($display_status)) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No reports found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="changePasswordModalLabel">Change Password</h4>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" method="post">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        <div id="passwordStrength" class="help-block"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        <div id="passwordMatch" class="help-block"></div>
                    </div>
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="otp" name="otp" required>
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="getOtpButton">Get Code</button>
                            </span>
                        </div>
                        <div id="otpStatus" class="help-block"></div>
                        <div id="otpDisplay" class="help-block"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="admin2/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
<script src="admin2/js/plugins/metisMenu/metisMenu.min.js"></script>
<script src="admin2/js/sb-admin-2.js"></script>

<script>
    $(document).ready(function() {
        function handleMenuClick(e) {
            var linkHref = $(this).attr('href');
            if (!linkHref || linkHref === '#') {
                if (linkHref !== '#complaintsSection') return false;
            }

            if (linkHref === '#complaintsSection') {
                e.preventDefault();
                var targetOffset = $('#complaintsSection').offset().top;
                var navHeight = $('.navbar-static-top').outerHeight() || 50;
                $('html, body').animate({
                    scrollTop: targetOffset - navHeight - 10
                }, 500);
            }

            if ($('.navbar-toggle').is(':visible')) {
                if (linkHref !== '#complaintsSection' && linkHref !== '#' && !$(this).attr('data-toggle')) {
                     $('.navbar-collapse.in').collapse('hide');
                } else if (linkHref === '#complaintsSection') {
                    setTimeout(function() {
                        if ($('.navbar-collapse.in').length > 0) {
                           $('.navbar-collapse.in').collapse('hide');
                        }
                    }, 550);
                }
            }
        }

        $('#side-menu > li > a').on('click', handleMenuClick);
        $('.dropdown-menu a[href="#complaintsSection"]').on('click', handleMenuClick);


        if ($(window).width() >= 768) {
             $('a[href="#complaintsSection"]').on('click', function(e) {
                e.preventDefault();
                var targetOffset = $('#complaintsSection').offset().top;
                var navHeight = $('.navbar-static-top').outerHeight() || 50;
                $('html, body').animate({
                    scrollTop: targetOffset - navHeight - 10
                }, 500);
            });
        }

        var otpSent = false;
        var timerInterval;
        var timeLeft = 120;

        $('#newPassword').keyup(function() {
            var password = $(this).val();
            var strengthText = '';
            var strength = 0;

            if (password.length < 8) {
                strengthText = 'Password must be at least 8 characters.';
            } else {
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!*?%^()_+=\-\[\]{};':"\\|,.<>\/~`]+/)) strength++;

                if (strength < 2) strengthText = 'Weak: Include uppercase, numbers, and/or symbols.';
                else if (strength == 2) strengthText = 'Fair: Consider adding more complexity.';
                else if (strength == 3) strengthText = 'Good';
                else strengthText = 'Strong';
            }
            $('#passwordStrength').text(strengthText).removeClass('text-success text-warning text-danger').addClass(
                strength >= 3 ? 'text-success' : (strength === 2 ? 'text-warning' : (password.length > 0 ? 'text-danger' : ''))
            );
        });

        $('#confirmPassword').keyup(function() {
            var newPassword = $('#newPassword').val();
            var confirmPassword = $(this).val();
            if (newPassword.length > 0 && confirmPassword.length > 0) {
                if (newPassword != confirmPassword) {
                    $('#passwordMatch').text('Passwords do not match.').removeClass('text-success').addClass('text-danger');
                } else {
                    $('#passwordMatch').text('Passwords match.').removeClass('text-danger').addClass('text-success');
                }
            } else {
                $('#passwordMatch').text('');
            }
        });

        $('#getOtpButton').click(function() {
            var button = $(this);
            var action = otpSent ? 'resendOtp' : 'getOtp';

            button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $('#otpStatus').text('');
            $('#otpDisplay').text('');


            $.ajax({
                url: 'update_password_api.php',
                type: 'POST',
                data: { action: action },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        otpSent = true;
                        $('#otpStatus').text('OTP ' + (action === 'getOtp' ? 'sent' : 'resent') + ' to phone ' + response.phone + '. Check below.').addClass('text-info');
                        timeLeft = 120;
                        startOtpTimer(button);
                        if(response.otp) {
                            $('#otpDisplay').text('Simulated OTP: ' + response.otp).addClass('text-info');
                        }
                    } else {
                        $('#otpStatus').text('Error: ' + response.message).addClass('text-danger');
                        button.prop('disabled', false).text(otpSent ? 'Resend Code' : 'Get Code');
                    }
                },
                error: function(xhr) {
                    $('#otpStatus').text('Error communicating with server. Please try again. ' + xhr.responseText).addClass('text-danger');
                    button.prop('disabled', false).text(otpSent ? 'Resend Code' : 'Get Code');
                }
            });
        });

        function startOtpTimer(button) {
            clearInterval(timerInterval);
            button.prop('disabled', true);
            timerInterval = setInterval(function() {
                timeLeft--;
                button.text('Resend Code in ' + timeLeft);
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    button.text('Resend Code').prop('disabled', false);
                }
            }, 1000);
        }

        $('#changePasswordForm').submit(function(event) {
            event.preventDefault();

            var currentPassword = $('#currentPassword').val();
            var newPassword = $('#newPassword').val();
            var confirmPassword = $('#confirmPassword').val();
            var otp = $('#otp').val();

            if (!currentPassword || !newPassword || !confirmPassword || !otp) {
                alert('Please fill in all fields.');
                return;
            }
            if (newPassword.length < 8) {
                alert('New password must be at least 8 characters.');
                return;
            }
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
                return;
            }

            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: 'update_password_api.php',
                type: 'POST',
                data: $(this).serialize() + '&action=updatePassword',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#changePasswordModal').modal('hide');
                         $('#changePasswordForm')[0].reset();
                         $('#passwordStrength, #passwordMatch, #otpStatus, #otpDisplay').text('');
                         clearInterval(timerInterval);
                         $('#getOtpButton').text('Get Code').prop('disabled', false);
                         otpSent = false;
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error updating password. Please try again. ' + xhr.responseText);
                },
                complete: function() {
                     $('#changePasswordForm').find('button[type="submit"]').prop('disabled', false).text('Update Password');
                }
            });
        });

        $('#changePasswordModal').on('hidden.bs.modal', function () {
            $('#changePasswordForm')[0].reset();
            $('#passwordStrength, #passwordMatch, #otpStatus, #otpDisplay').text('');
            clearInterval(timerInterval);
            $('#getOtpButton').text('Get Code').prop('disabled', false);
            otpSent = false;
            timeLeft = 120;
            $('#passwordStrength, #passwordMatch, #otpStatus').removeClass('text-success text-warning text-danger text-info');
        });

    });

    function showNotEligibleMessage() {
        alert("You are not eligible to vote at this time. This could be because you have already voted, the election is not open, or your account is inactive.");
    }
</script>
</body>
</html>