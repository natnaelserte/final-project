<?php
// session_start(); // Start session if not already started by an include
require_once 'dbcon.php'; // Your database connection
include 'head.php';      // Assuming this includes Bootstrap CSS etc.
include 'side_bar.php';  // Your navigation

// Initialize messages and redirection pages
$message = '';
$page_title = "Deactivate Faculty Accounts";
$current_page_script_name = basename($_SERVER['PHP_SELF']); // e.g., 'deactivate_faculty.php'
$page_for_final_success = 'manage_users.php'; // Page to go to after successful deactivation

// --- Fetch distinct departments for Faculty for the dropdown ---
$departments = [];
try {
    // Fetch departments specifically for users with role_id = 5 (Faculty)
    $stmt_dept = $pdo->query("SELECT DISTINCT department FROM users WHERE role_id = 5 AND department IS NOT NULL AND department != '' ORDER BY department ASC");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching faculty departments for deactivation page: " . $e->getMessage());
    // Non-critical, form can still be shown, but department dropdown might be empty
    // $_SESSION['message'] = ['type' => 'warning', 'text' => 'Could not load department list.'];
}

// --- Start: Handle Conditional Deactivation for Faculty ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['deactivate_all_faculty_flag'])) {
    $gender_filter = $_POST['gender_filter'] ?? '';
    $department_filter = $_POST['department_filter'] ?? '';

    // Always filter by role_id = 5 for faculty on this page
    $conditions = ["role_id = :role_id"];
    $params = [':role_id' => 5];

    // --- Build WHERE conditions for Gender and Department ---
    if (!empty($gender_filter) && in_array($gender_filter, ['Male', 'Female', 'Other'])) {
        $conditions[] = "gender = :gender";
        $params[':gender'] = $gender_filter;
    }

    if (!empty($department_filter)) {
        $conditions[] = "department = :department";
        $params[':department'] = $department_filter;
    }

    // --- Execute Update for Conditional Deactivation ---
    // Ensure at least one additional filter (gender or department) is selected
    // if not using the "Deactivate All Faculty" button.
    if (count($conditions) > 1) { // At least one specific filter beyond just role_id = 5
        $sql = "UPDATE users SET account = 'Inactive'";
        $sql .= " WHERE " . implode(" AND ", $conditions);
        // Optionally add: AND account = 'Active' to only deactivate those currently active
        // $sql .= " AND account = 'Active'";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $affected_rows = $stmt->rowCount();

            if ($affected_rows > 0) {
                $message_text = "Successfully deactivated " . $affected_rows . " faculty account(s) based on your criteria.";
                $_SESSION['message'] = ['type' => 'success', 'text' => $message_text];
            } else {
                $message_text = "No faculty accounts matched your criteria or they were already inactive.";
                $_SESSION['message'] = ['type' => 'info', 'text' => $message_text];
            }
            // Redirect to the user list page to see the results
            header("Location: " . $page_for_final_success);
            exit;

        } catch (PDOException $e) {
            error_log("Error deactivating faculty accounts: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params));
            $_SESSION['message'] = ['type' => 'danger', 'text' => "Database error during deactivation. Please try again."];
            header("Location: " . $current_page_script_name); // Redirect back to this form page
            exit;
        }
    } else {
        // This 'else' means only role_id=5 was the condition (no specific gender/dept filter from form)
        // which should be handled by the "Deactivate All Faculty" button instead.
        $_SESSION['message'] = ['type' => 'warning', 'text' => "Please select at least one filter (Gender or Department) to deactivate specific faculty, or use the 'Deactivate ALL Faculty' button."];
        header("Location: " . $current_page_script_name);
        exit;
    }
}
// --- End: Handle Conditional Deactivation for Faculty ---


// --- Start: Handle "Deactivate All Faculty" Flag ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_all_faculty_flag']) && $_POST['deactivate_all_faculty_flag'] === 'true') {
    try {
        // Deactivate only users with role_id = 5
        $stmt = $pdo->prepare("UPDATE users SET account = 'Inactive' WHERE role_id = :role_id");
        // Optionally add: AND account = 'Active'
        // $stmt = $pdo->prepare("UPDATE users SET account = 'Inactive' WHERE role_id = :role_id AND account = 'Active'");
        $stmt->execute([':role_id' => 5]);
        $affected_rows_all = $stmt->rowCount();

        if ($affected_rows_all > 0) {
             $_SESSION['message'] = ['type' => 'success', 'text' => "All faculty accounts ({$affected_rows_all}) have been deactivated successfully!"];
        } else {
             $_SESSION['message'] = ['type' => 'info', 'text' => "No faculty accounts needed deactivation or no faculty found."];
        }
        header("Location: " . $page_for_final_success);
        exit;

    } catch (PDOException $e) {
        error_log("Error deactivating all faculty accounts: " . $e->getMessage());
        $_SESSION['message'] = ['type' => 'danger', 'text' => "Error deactivating all faculty accounts: " . htmlspecialchars($e->getMessage())];
        header("Location: " . $page_for_final_success); // Or redirect to current page with error
        exit;
    }
}
// --- End: Handle "Deactivate All Faculty" Flag ---

// Display session messages if redirected back to this page (e.g., after a warning)
if (isset($_SESSION['message'])) {
    $msg_type_display = $_SESSION['message']['type'] ?? 'info';
    $msg_text_display = $_SESSION['message']['text'] ?? '';
    $message_html = "<div class='alert alert-{$msg_type_display} alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                    " . htmlspecialchars($msg_text_display) . "
                  </div>";
    unset($_SESSION['message']); // Clear the message after displaying
} else {
    $message_html = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- head.php should include Bootstrap CSS, FontAwesome, etc. -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Basic styling - integrate with your theme/SB Admin 2 */
        body { font-family: 'Roboto', sans-serif; margin: 0; background-color: #f8f9fa; /* Light gray background */ }
        /* SB Admin 2 uses #f8f8f8 by default for page-wrapper */
        #page-wrapper { padding: 15px; background-color: #f8f8f8; }
        .container-deactivate-faculty {
            max-width: 750px; /* Max width for the form container */
            margin: 20px auto; /* Center the container */
            background: #fff;
            padding: 20px 30px; /* More padding */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); /* Softer shadow */
        }
        .container-deactivate-faculty h2 {
            text-align: center;
            color: #d9534f; /* Danger color for deactivation theme */
            margin-bottom: 25px;
            font-weight: 500;
        }
        .instructions {
            text-align: center;
            margin-bottom: 25px;
            color: #555;
            font-size: 0.95em;
            line-height: 1.6;
        }
        .form-group { margin-bottom: 25px; }
        .form-group legend {
            font-size: 1.2em;
            font-weight: 500;
            margin-bottom: 12px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        .form-group .radio-group label {
            margin-right: 20px;
            font-weight: normal;
            display: inline-flex; /* Align checkbox and text nicely */
            align-items: center;
        }
        .form-group .radio-group input[type="radio"] {
            margin-right: 5px;
            transform: scale(1.1); /* Slightly larger radio buttons */
        }
        .form-control { /* For select dropdown */
            display: block;
            width: 100%;
            height: 40px; /* Consistent height */
            padding: 8px 12px;
            font-size: 1em;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
        }
        .btn-submit-deactivate, .btn-deactivate-all-faculty { /* Common styling for submit buttons */
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.05em;
            font-weight: 500;
            width: 100%;
            display: block;
            text-align: center;
            transition: background-color 0.2s ease;
        }
        .btn-submit-deactivate { background-color: #d9534f; /* Bootstrap danger */ }
        .btn-submit-deactivate:hover { background-color: #c9302c; }
        .btn-deactivate-all-faculty { background-color: #c9302c; /* Darker red for "ALL" */ margin-top: 10px; }
        .btn-deactivate-all-faculty:hover { background-color: #ac2925; }

        hr { margin-top: 30px; margin-bottom: 25px; border-top: 1px solid #e0e0e0; }
        .alert { margin-top: 20px; margin-bottom: 20px; }
        .text-danger { color: #a94442; } /* Bootstrap 3 danger text */
        .panel-primary > .panel-heading { background-color: #d9534f; border-color: #d43f3a; color: white; } /* Match deactivation theme */
        .panel-primary { border-color: #d43f3a; }
    </style>
</head>
<body>
<div id="wrapper">
    <!-- Sidebar -->
    <?php // include('side_bar.php'); - Already included at the top ?>

    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary"> <!-- Changed to panel-danger for theme -->
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-times-circle fa-fw"></i> <?php echo htmlspecialchars($page_title); ?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="container-deactivate-faculty">
                                <?php if (!empty($message_html)): echo $message_html; endif; ?>

                                <p class="instructions">
                                    Select criteria to deactivate specific faculty accounts (Role ID: 5).
                                    If multiple filters are selected, faculty must match ALL criteria.
                                    This action will mark their account as 'Inactive'.
                                </p>

                                <form action="<?php echo htmlspecialchars($current_page_script_name); ?>" method="POST">
                                    <div class="form-group">
                                        <legend><i class="fa fa-venus-mars"></i> Filter by Gender</legend>
                                        <div class="radio-group">
                                            <label for="gender_any">
                                                <input type="radio" id="gender_any" name="gender_filter" value="" checked> Any
                                            </label>
                                            <label for="gender_male">
                                                <input type="radio" id="gender_male" name="gender_filter" value="Male"> Male
                                            </label>
                                            <label for="gender_female">
                                                <input type="radio" id="gender_female" name="gender_filter" value="Female"> Female
                                            </label>
                                            <label for="gender_other">
                                                <input type="radio" id="gender_other" name="gender_filter" value="Other"> Other
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="department_filter_select"><i class="fa fa-building"></i> Filter by Department</label>
                                        <select id="department_filter_select" name="department_filter" class="form-control">
                                            <option value="" selected>Any Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo htmlspecialchars($dept); ?>">
                                                    <?php echo htmlspecialchars($dept); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if (empty($departments)): ?>
                                                <option value="" disabled>No departments found for faculty</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn-submit-deactivate">Deactivate Selected Faculty</button>
                                </form>

                                <hr>

                                <p style="text-align: center; margin-bottom: 10px; font-weight:500;">Alternatively:</p>
                                <button type="button" class="btn-deactivate-all-faculty" onclick="deactivateAllFaculty()">Deactivate ALL Faculty Accounts</button>
                            </div> <!-- /.container-deactivate-faculty -->
                        </div> <!-- /.panel-body -->
                    </div> <!-- /.panel -->
                </div> <!-- /.col-lg-12 -->
            </div> <!-- /.row -->
        </div> <!-- /.container-fluid -->
    </div> <!-- /#page-wrapper -->
</div> <!-- /#wrapper -->

<?php include 'script.php'; // For DataTables, Bootstrap JS etc. if needed by layout ?>
<script>
function deactivateAllFaculty() {
    if (confirm('Are you sure you want to DEACTIVATE ALL FACULTY user accounts (Role ID: 5)? This will mark them as inactive and may prevent them from logging in.')) {
        // Create a form dynamically to submit the flag
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo htmlspecialchars($current_page_script_name); ?>'; // Submits to the current page

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'deactivate_all_faculty_flag'; // Changed flag name
        hiddenInput.value = 'true';
        form.appendChild(hiddenInput);

        document.body.appendChild(form); // Append to body to be submittable
        form.submit();
    }
}

// Optional: Client-side check to ensure at least one filter is chosen for specific deactivation
$(document).ready(function() {
    $('form[action="<?php echo htmlspecialchars($current_page_script_name); ?>"]').on('submit', function(e) {
        // Check if this is the conditional form submission (not the "deactivate all" one)
        if (!$('input[name="deactivate_all_faculty_flag"]', this).length) {
            var genderSelected = $('input[name="gender_filter"]:checked').val();
            var departmentSelected = $('#department_filter_select').val();

            if (genderSelected === "" && departmentSelected === "") {
                alert("Please select at least one filter (Gender or Department) for specific deactivation, or use the 'Deactivate ALL Faculty' button.");
                e.preventDefault(); // Prevent form submission
                return false;
            }
        }
    });
});
</script>

</body>
</html>