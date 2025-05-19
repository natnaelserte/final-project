<?php
session_start();
include('admin/dbcon.php'); // Ensure dbcon.php is updated for PDO
include('head.php');

$errors = [];
$success_message = "";
$username_from_session = $_SESSION['username'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    $complainant_type = filter_input(INPUT_POST, 'complainant_type', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $voting_event_id = filter_input(INPUT_POST, 'voting_event_id', FILTER_VALIDATE_INT);
    $complaint_category = filter_input(INPUT_POST, 'complaint_category', FILTER_SANITIZE_STRING);
    $complaint_subject = filter_input(INPUT_POST, 'complaint_subject', FILTER_SANITIZE_STRING);
    $complaint_details = filter_input(INPUT_POST, 'complaint_details', FILTER_SANITIZE_STRING);

    if (empty($complainant_type)) $errors[] = "Please select your role.";
    if (empty($username)) $errors[] = "Please enter your username.";
    if (empty($complaint_category)) $errors[] = "Please select a complaint category.";
    if (empty($complaint_subject)) $errors[] = "Please provide a subject.";
    if (empty($complaint_details)) $errors[] = "Please provide complaint details.";

    try {
        // Insert complaint using a subquery to get the user ID directly
        $stmt = $pdo->prepare("INSERT INTO report_complaints (username, voting_event_id, category, subject, description, created_at) 
            VALUES ((SELECT id_number FROM users WHERE username = :username LIMIT 1), :voting_event_id, :category, :subject, :description, NOW())");
    
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':voting_event_id', $voting_event_id, PDO::PARAM_INT);
        $stmt->bindParam(':category', $complaint_category);
        $stmt->bindParam(':subject', $complaint_subject);
        $stmt->bindParam(':description', $complaint_details);
    
        if ($stmt->execute()) {
            $complaint_id = $pdo->lastInsertId();
            $success_message = "Complaint submitted successfully! Your Complaint ID is: COMP-$complaint_id";
            $_POST = []; // Clear form
        } else {
            $errors[] = "Error submitting complaint.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
    
}
$voting_events = [
    ['id' => 1, 'title' => 'Community Hall Renovation Vote 2024'],
    ['id' => 2, 'title' => 'Leadership Election 2024'],
];

// Get user status for sidebar menu
$id_number = $_SESSION['username'] ?? null;
$can_vote = false;

if ($id_number) {
    try {
        $stmt = $pdo->prepare("SELECT status, account FROM users WHERE username = :id_number");
        $stmt->bindParam(':id_number', $id_number, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Normalize to lowercase for consistency
        $status = trim($user['status'] ?? '');
        $account = trim($user['account'] ?? '');

        // Logic without strtolower
        $can_vote = ($status === 'Unvoted' && $account === 'Active');
    } catch (PDOException $e) {
        // Silently handle error
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
    <!-- Custom CSS -->
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
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 1rem;
            border-bottom: 2px solid #0a3d62;
            padding-bottom: 0.5rem;
        }
        
        .btn-submit {
            background-color: #0a3d62;
            color: #fff;
            font-weight: 500;
        }
        
        .btn-submit:hover {
            background-color: #0c2461;
            color: #fff;
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
            <a class="navbar-brand" href="#">
                <i class="fa fa-home"></i> Voter Portal
            </a>
        </div>

        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i>Welcome: <?= htmlspecialchars($id_number) ?></i>
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
                            <a href="#" class="disabled"><i class="fa fa-times-circle fa-fw"></i> Vote (Not Eligible)</a>
                        <?php endif; ?>
                    </li>
                    <li class="active">
                        <a href="report_complaint.php"><i class="fa fa-comment fa-fw"></i> Report Complaint</a>
                    </li>
                    <li>
                        <a href="voter_home.php#complaintsSection"><i class="fa fa-list fa-fw"></i> View Complaints</a>
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
                    <h2 class="page-header">Report a Voting Complaint</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                    <?php endif; ?>

                    <?php if (!$success_message): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Complaint Form</h3>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                                <!-- Step 1 -->
                                <div class="form-group">
                                    <div class="form-step-title"><i class="fa fa-user-tag me-2"></i>Step 1: Identify Yourself</div>
                                    <div class="form-group">
                                        <label for="complainant_type" class="control-label">I am a:</label>
                                        <select class="form-control" name="complainant_type" required>
                                            <option value="">-- Select Your Role --</option>
                                            <option value="voter" <?= @$_POST['complainant_type'] === 'voter' ? 'selected' : '' ?>>Voter</option>
                                            <option value="candidate" <?= @$_POST['complainant_type'] === 'candidate' ? 'selected' : '' ?>>Candidate</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Username:</label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? $username_from_session) ?>" required>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="form-group">
                                    <div class="form-step-title"><i class="fa fa-calendar-check me-2"></i>Step 2: Voting Event</div>
                                    <div class="form-group">
                                        <label class="control-label">Select Voting Event:</label>
                                        <select class="form-control" name="voting_event_id" required>
                                            <option value="">-- Select Event --</option>
                                            <?php foreach ($voting_events as $event): ?>
                                                <option value="<?= $event['id'] ?>" <?= @$_POST['voting_event_id'] == $event['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($event['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="form-group">
                                    <div class="form-step-title"><i class="fa fa-file-alt me-2"></i>Step 3: Complaint Details</div>
                                    <div class="form-group">
                                        <label class="control-label">Complaint Category:</label>
                                        <select class="form-control" name="complaint_category" required>
                                            <option value="">-- Select Category --</option>
                                            <option value="Fraud" <?= @$_POST['complaint_category'] === 'Fraud' ? 'selected' : '' ?>>Fraud</option>
                                            <option value="Mismanagement" <?= @$_POST['complaint_category'] === 'Mismanagement' ? 'selected' : '' ?>>Mismanagement</option>
                                            <option value="Intimidation" <?= @$_POST['complaint_category'] === 'Intimidation' ? 'selected' : '' ?>>Intimidation</option>
                                            <option value="Other" <?= @$_POST['complaint_category'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Subject:</label>
                                        <input type="text" name="complaint_subject" class="form-control" value="<?= htmlspecialchars($_POST['complaint_subject'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Complaint Details:</label>
                                        <textarea name="complaint_details" class="form-control" rows="5" required><?= htmlspecialchars($_POST['complaint_details'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" name="submit_complaint" class="btn btn-primary btn-submit">
                                        <i class="fa fa-paper-plane"></i> Submit Complaint
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="admin2/js/jquery.js"></script>
<script src="admin2/bootstrap-3.3.7/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="admin2/js/plugins/metisMenu/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="admin2/js/sb-admin-2.js"></script>

<script>
function showNotEligibleMessage() {
    alert("You are not eligible to vote. Your status must be 'Unvoted' and your account must be 'Active'.");
}
</script>

</body>
</html>
