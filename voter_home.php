<?php
session_start();
include('admin/dbcon.php');

$id_number = $_SESSION['username'] ?? null;

if (!$id_number) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status, account FROM users WHERE username = :id_number");
    $stmt->bindParam(':id_number', $id_number, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Normalize to lowercase for consistency
    $status = trim($user['status'] ?? '');
    $account = trim($user['account'] ?? '');

    // Logic without strtolower
    $can_vote = ($status === 'Unvoted' && $account === 'Active' );



    $stmt = $pdo->prepare("SELECT * FROM report_complaints WHERE username = :id_number");
    $stmt->bindParam(':id_number', $id_number, PDO::PARAM_INT);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Voter Dashboard</title>
    <!-- Bootstrap 3.3.7 CSS -->
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- Custom CSS -->
    <link href="admin2/css/sb-admin-2.css" rel="stylesheet">
    
    <style>
        /* Mobile-first responsive styles */
        @media (max-width: 767px) {
            /* Wrapper and page content */
            #wrapper {
                padding-left: 0;
            }
            
            #page-wrapper {
                margin: 0;
                padding: 10px;
                min-height: calc(100vh - 50px);
            }
            
            /* Sidebar adjustments */
            .sidebar {
                width: 100%;
                position: static;
                margin-top: 50px;
                z-index: 1;
            }
            
            .sidebar-nav {
                padding-bottom: 0;
            }
            
            /* Navigation */
            .navbar-top-links {
                margin-right: 15px;
            }
            
            .navbar-top-links li a {
                padding: 10px;
                min-height: 50px;
            }
            
            /* Content adjustments */
            .page-header {
                font-size: 20px;
                margin-top: 15px;
                padding-bottom: 10px;
            }
            
            /* Panel adjustments */
            .panel {
                margin-bottom: 15px;
            }
            
            .panel-heading {
                padding: 10px 15px;
            }
            
            .panel-body {
                padding: 10px;
            }
            
            /* Table adjustments */
            .table-responsive {
                border: none;
                margin-bottom: 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table {
                font-size: 12px;
                margin-bottom: 0;
            }
            
            .table > thead > tr > th,
            .table > tbody > tr > td {
                padding: 8px 5px;
                white-space: nowrap;
            }
            
            /* Button adjustments */
            .btn-lg {
                padding: 8px 16px;
                font-size: 14px;
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
            
            /* Fix for collapsed navbar */
            .navbar-collapse {
                max-height: none;
            }
            
            .navbar-collapse.collapse {
                display: none !important;
            }
            
            .navbar-collapse.collapse.in {
                display: block !important;
            }
            
            .navbar-header .navbar-toggle {
                display: block;
            }
            
            .navbar-header {
                float: none;
            }
        }
        
        /* Small tablet adjustments */
        @media (min-width: 768px) and (max-width: 991px) {
            #page-wrapper {
                margin-left: 200px;
            }
            
            .sidebar {
                width: 200px;
            }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0; background-color: rgba(30, 110, 157);">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#" style="color:white;">
                <i class="fa fa-home"></i> Voter Portal
            </a>
        </div>

        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: white;">
                    <i>Welcome: <?= htmlspecialchars($id_number) ?></i>
                </a>
                <ul class="dropdown-menu dropdown-user">
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
                        <a href="#"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <?php if ($can_vote): ?>
                            <a href="vote.php"><i class="fa fa-check-circle fa-fw"></i> Vote Now</a>
                        <?php else: ?>
                            <a href="javascript:void(0);" onclick="showNotEligibleMessage()"><i class="fa fa-times-circle fa-fw"></i> Vote</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <a href="report_complaint.php"><i class="fa fa-comment fa-fw"></i> Report Complaint</a>
                    </li>
                    <li>
                        <a href="#complaintsSection"><i class="fa fa-list fa-fw"></i> View Complaints</a>
                    </li>
                    <li>
                        <a href="update_password.php"><i class="fa fa-key fa-fw"></i> Change Password</a>
                    </li>
                    <li>
                        <a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Welcome to Voter Dashboard</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Your Complaints & Responses</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($complaints)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Complaint</th>
                                                <th>Response</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($complaints as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['subject']) ?></td>
                                                    <td><?= htmlspecialchars($row['response']) ?: '<em>No response yet</em>' ?></td>
                                                    <td>
                                                        <?php
                                                            $status = strtolower($row['status']);
                                                            $label = match ($status) {
                                                                'resolved' => 'success',
                                                                'in_progress' => 'warning',
                                                                'pending' => 'default',
                                                                default => 'default'
                                                            };
                                                        ?>
                                                        <span class="label label-<?= $label ?>"><?= ucfirst($status) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No complaints found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 text-center" style="margin-top: 20px; margin-bottom: 20px;">
                    <?php if ($can_vote): ?>
                        <a href="vote.php" class="btn btn-success btn-lg">
                            <i class="fa fa-check-circle"></i> Vote Now
                        </a>
                    <?php else : ?>
                        <button class="btn btn-default btn-lg" disabled>
                            <i class="fa fa-times-circle"></i> You are not eligible to vote
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="admin2/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="admin2/js/plugins/metisMenu/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="admin2/js/sb-admin-2.js"></script>

<script>
    // Mobile-specific enhancements
    $(document).ready(function() {
        // Handle sidebar menu item clicks on mobile
        if ($(window).width() < 768) {
            $('#side-menu > li > a').on('click', function() {
                if (!$(this).attr('href') || $(this).attr('href') === '#') {
                    return false;
                }
                
                // Close the sidebar menu after clicking a link
                if ($('.navbar-toggle').is(':visible')) {
                    $('.navbar-toggle').click();
                }
            });
            
            // Smooth scroll to complaint section
            $('a[href="#complaintsSection"]').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('#complaintsSection').offset().top - 60
                }, 500);
                
                // Close the sidebar menu
                if ($('.navbar-toggle').is(':visible')) {
                    $('.navbar-toggle').click();
                }
            });
        }
    });
    
    // Function to show message when user is not eligible to vote
    function showNotEligibleMessage() {
        alert("You are not eligible to vote at this time. This could be because you have already voted or your account is inactive.");
    }
</script>
</body>
</html>
