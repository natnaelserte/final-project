<?php
session_start();
include('admin/dbcon.php'); // Ensure dbcon.php is updated for PDO and accessible
include('head.php'); // Assuming head.php contains common head elements like meta tags, but not full HTML structure

$errors = [];
$success_message = "";
$username_from_session = $_SESSION['username'] ?? ''; // Used to prefill username if available

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    // Sanitize and validate inputs
    $complainant_type = filter_input(INPUT_POST, 'complainant_type', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $voting_event_name = filter_input(INPUT_POST, 'voting_event_name', FILTER_SANITIZE_STRING); // Event name as text
    $complaint_category = filter_input(INPUT_POST, 'complaint_category', FILTER_SANITIZE_STRING);
    $complaint_subject = filter_input(INPUT_POST, 'complaint_subject', FILTER_SANITIZE_STRING);
    $complaint_details = filter_input(INPUT_POST, 'complaint_details', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($complainant_type)) $errors[] = "Please enter your role (e.g., Voter, Candidate).";
    if (empty($username)) $errors[] = "Please enter your username.";
    if (empty($voting_event_name)) $errors[] = "Please enter the Voting Event name.";
    if (empty($complaint_category)) $errors[] = "Please enter a complaint category (e.g., Fraud, Mismanagement).";
    if (empty($complaint_subject)) $errors[] = "Please provide a subject for your complaint.";
    if (empty($complaint_details)) $errors[] = "Please provide the details of your complaint.";

    // If no errors, proceed to insert
    if (empty($errors)) {
        try {
            // IMPORTANT: Assumes 'voting_event_id' column in 'report_complaints'
            // is now VARCHAR or TEXT to store the event name.
            $stmt = $pdo->prepare("INSERT INTO report_complaints (username, voting_event_id, category, subject, description, created_at) 
                VALUES ((SELECT id_number FROM users WHERE username = :username LIMIT 1), :voting_event_name, :category, :subject, :description, NOW())");
        
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':voting_event_name', $voting_event_name, PDO::PARAM_STR); // Bind event name as string
            $stmt->bindParam(':category', $complaint_category, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $complaint_subject, PDO::PARAM_STR);
            $stmt->bindParam(':description', $complaint_details, PDO::PARAM_STR);
        
            if ($stmt->execute()) {
                $complaint_id = $pdo->lastInsertId();
                $success_message = "Complaint submitted successfully! Your Complaint ID is: COMP-" . htmlspecialchars($complaint_id);
                // Clear $_POST to prevent re-submission and clear form fields
                $_POST = []; 
            } else {
                $errors[] = "Error submitting complaint. Please try again.";
            }
        } catch (PDOException $e) {
            // Log the detailed error for administrators
            error_log("Complaint submission database error: " . $e->getMessage());
            // Show a generic error to the user
            $errors[] = "A database error occurred while submitting your complaint. Please try again later.";
        }
    }
}

// Example list of current voting events for user guidance.
// In a real application, you might fetch these from a database if you have a list of active events.
$current_voting_events_examples = [
    'class representative Election',
    'Faculty Representative Election ',
    'infoken leader election',
];

// Get user status for sidebar menu logic
$id_number_for_status_check = $_SESSION['username'] ?? null; // Using session username to check status
$can_vote = false;

if ($id_number_for_status_check) {
    try {
        // Assuming 'username' is the column in 'users' table that matches $_SESSION['username']
        $stmt_user_status = $pdo->prepare("SELECT status, account FROM users WHERE username = :username_session_val");
        $stmt_user_status->bindParam(':username_session_val', $id_number_for_status_check, PDO::PARAM_STR);
        $stmt_user_status->execute();
        $user_status_data = $stmt_user_status->fetch(PDO::FETCH_ASSOC);

        if ($user_status_data) {
            $status = trim($user_status_data['status'] ?? '');
            $account = trim($user_status_data['account'] ?? '');
            // Check if user is eligible to vote
            $can_vote = (strtolower($status) === 'unvoted' && strtolower($account) === 'active');
        }
    } catch (PDOException $e) {
        // Silently log error, don't break page for this
        error_log("Error fetching user status for sidebar: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report a Voting Complaint</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 3.3.7 CSS -->
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin2/bootstrap-3.3.7/dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- Custom CSS for SB Admin 2 -->
    <link href="admin2/css/sb-admin-2.css" rel="stylesheet">
    
    <style>
        /* Custom styles for dark blue header */
        .navbar {
            background-color: #0a3d62 !important; /* Dark blue color */
            border-color: #0a3d62 !important;
        }
        .navbar-brand, 
        .navbar-top-links li a {
            color: white !important;
        }
        .navbar-toggle .icon-bar {
            background-color: white !important;
        }
        
        /* Form styling */
        .form-step-title {
            font-size: 1.25rem; /* 20px */
            font-weight: 600;
            color: #343a40; /* Dark gray */
            margin-bottom: 1rem; /* 16px */
            border-bottom: 2px solid #0a3d62; /* Dark blue border */
            padding-bottom: 0.5rem; /* 8px */
        }
        
        .btn-submit {
            background-color: #0a3d62; /* Dark blue */
            color: #fff;
            font-weight: 500;
            padding: 10px 20px;
            font-size: 1rem; /* 16px */
        }
        
        .btn-submit:hover {
            background-color: #0c2461; /* Darker blue */
            color: #fff;
        }
        .help-block {
            font-size: 0.875rem; /* 14px */
            color: #777;
        }
    </style>
</head>

<body>

<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0;">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="voter_home.php">
                <i class="fa fa-home"></i> Voter Portal
            </a>
        </div>

        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i>Welcome: <?= htmlspecialchars($username_from_session) ?></i>
                    <i class="fa fa-caret-down"></i>
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
                        <a href="voter_home.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <li>
                        <?php if ($can_vote): ?>
                            <a href="vote.php"><i class="fa fa-check-circle fa-fw"></i> Vote Now</a>
                        <?php else: ?>
                            <a href="#" class="disabled" onclick="showNotEligibleMessage(); return false;"><i class="fa fa-times-circle fa-fw"></i> Vote (Not Eligible)</a>
                        <?php endif; ?>
                    </li>
                    <li class="active">
                        <a href="report_complaint.php"><i class="fa fa-comment fa-fw"></i> Report Complaint</a>
                    </li>
                    <li>
                        <a href="voter_home.php#complaintsSection"><i class="fa fa-list fa-fw"></i> View My Complaints</a>
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
                    <h2 class="page-header"><i class="fa fa-bullhorn"></i> Report a Voting Complaint</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Please correct the following errors:</strong>
                            <ul style="margin-bottom: 0;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                        <p><a href="voter_home.php#complaintsSection" class="btn btn-info">View My Complaints</a> 
                           <a href="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-default">Submit Another Complaint</a></p>
                    <?php endif; ?>

                    <?php if (!$success_message): // Only show form if no success message ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Complaint Form</h3>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                
                                <div class="form-step-title"><i class="fa fa-user-tag"></i> Step 1: Identify Yourself</div>
                                <div class="form-group">
                                    <label for="complainant_type" class="control-label">I am a:</label>
                                    <input type="text" class="form-control" name="complainant_type" id="complainant_type"
                                           value="<?= htmlspecialchars($_POST['complainant_type'] ?? '') ?>" 
                                           placeholder="Enter your role (e.g., Voter, Candidate, Observer)" required>
                                </div>
                                <div class="form-group">
                                    <label for="username" class="control-label">Username:</label>
                                    <input type="text" name="username" id="username" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['username'] ?? $username_from_session) ?>" 
                                           placeholder="Your registered username" required>
                                </div>
                                <hr>

                                <div class="form-step-title"><i class="fa fa-calendar-check"></i> Step 2: Voting Event</div>
                                <div class="form-group">
                                    <label for="voting_event_name" class="control-label">Name of the Voting Event:</label>
                                    <input type="text" class="form-control" name="voting_event_name" id="voting_event_name"
                                           value="<?= htmlspecialchars($_POST['voting_event_name'] ?? '') ?>" 
                                           placeholder="Enter the official name of the voting event" required>
                                    <?php if (!empty($current_voting_events_examples)): ?>
                                    <small class="help-block">
                                        For example: 
                                        <?php 
                                        $examples_output = [];
                                        foreach ($current_voting_events_examples as $event_example) {
                                            $examples_output[] = htmlspecialchars($event_example);
                                        }
                                        echo implode(', ', $examples_output);
                                        ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <hr>

                                <div class="form-step-title"><i class="fa fa-file-alt"></i> Step 3: Complaint Details</div>
                                <div class="form-group">
                                    <label for="complaint_category" class="control-label">Complaint Category:</label>
                                    <input type="text" class="form-control" name="complaint_category" id="complaint_category"
                                           value="<?= htmlspecialchars($_POST['complaint_category'] ?? '') ?>" 
                                           placeholder="e.g., Voter Intimidation, Ballot Tampering, Misinformation, Access Issue, Other" required>
                                </div>
                                <div class="form-group">
                                    <label for="complaint_subject" class="control-label">Subject of Complaint:</label>
                                    <input type="text" name="complaint_subject" id="complaint_subject" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['complaint_subject'] ?? '') ?>" 
                                           placeholder="A brief title for your complaint" required>
                                </div>
                                <div class="form-group">
                                    <label for="complaint_details" class="control-label">Detailed Description of Complaint:</label>
                                    <textarea name="complaint_details" id="complaint_details" class="form-control" rows="6" 
                                              placeholder="Please provide as much detail as possible, including dates, times, locations, and names of individuals involved if known." 
                                              required><?= htmlspecialchars($_POST['complaint_details'] ?? '') ?></textarea>
                                </div>
                                <hr>
                                
                                <div class="text-center" style="margin-top: 20px;">
                                    <button type="submit" name="submit_complaint" class="btn btn-primary btn-submit">
                                        <i class="fa fa-paper-plane"></i> Submit Complaint
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; // End of if(!$success_message) ?>
                </div> <!-- /.col-md-10 -->
            </div> <!-- /.row -->
        </div> <!-- /.container-fluid -->
    </div> <!-- /#page-wrapper -->
</div> <!-- /#wrapper -->

<!-- jQuery and Bootstrap JS -->
<script src="admin2/js/jquery.js"></script>
<script src="admin2/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="admin2/js/plugins/metisMenu/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript for SB Admin 2 -->
<script src="admin2/js/sb-admin-2.js"></script>

<script>
function showNotEligibleMessage() {
    alert("You are not eligible to vote at this time. Your voting status must be 'Unvoted' and your account must be 'Active'. Please check your dashboard for more details.");
}
</script>

</body>
</html>