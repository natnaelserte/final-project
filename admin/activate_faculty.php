<?php
require_once 'dbcon.php'; // Your database connection
include 'head.php';      // Assuming this includes Bootstrap CSS etc.
include 'side_bar.php';  // Your navigation

$message = '';
$page_to_redirect_after_action = 'activate_accounts.php'; // Or specific faculty management page
$page_for_final_success = 'manage_users.php'; // Example: page listing users (students & faculty)

// --- Fetch distinct departments for Faculty for the dropdown ---
$departments = [];
try {
    // Fetch departments specifically for users with role_id = 5 (Faculty)
    $stmt_dept = $pdo->query("SELECT DISTINCT department FROM users WHERE role_id = 5 AND department IS NOT NULL AND department != '' ORDER BY department ASC");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching faculty departments: " . $e->getMessage());
    $message = "Error loading department list. Please try again."; // Inform admin
}

// --- Start: Handle Conditional Activation for Faculty ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['activate_all_faculty_flag'])) {
    $gender_filter = $_POST['gender_filter'] ?? '';
    $department_filter = $_POST['department_filter'] ?? '';

    $conditions = ["role_id = :role_id"]; // ALWAYS filter by role_id = 5 for faculty
    $params = [':role_id' => 5];

    // --- Build WHERE conditions for Gender and Department ---
    if (!empty($gender_filter) && in_array($gender_filter, ['Male', 'Female', 'Other'])) { // Adjusted values
        $conditions[] = "gender = :gender";
        $params[':gender'] = $gender_filter;
    }

    if (!empty($department_filter)) {
        $conditions[] = "department = :department";
        $params[':department'] = $department_filter;
    }

    // --- Execute Update for Conditional Activation ---
    // Ensure at least one additional filter (gender or department) is selected,
    // or if only role_id = 5 is the condition, it means activate all faculty matching this.
    // The current logic requires at least one *specific* filter if not using "Activate All".
    // If you want to allow activating all faculty without specific filters via this form, adjust the check.
    if (count($conditions) > 1) { // More than just the role_id = 5 condition
        $sql = "UPDATE users SET account = 'Active'";
        $sql .= " WHERE " . implode(" AND ", $conditions);
        // Optionally add: AND account = 'Inactive' to only activate those currently inactive
        // $sql .= " AND account = 'Inactive'";


        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $affected_rows = $stmt->rowCount();

            if ($affected_rows > 0) {
                $message = "Successfully activated " . $affected_rows . " faculty account(s) based on your criteria.";
                $_SESSION['message'] = ['type' => 'success', 'text' => $message];
                echo "<script>window.location = '" . $page_for_final_success . "';</script>";
            } else {
                $message = "No faculty accounts matched your criteria or they were already active.";
                $_SESSION['message'] = ['type' => 'info', 'text' => $message];
                echo "<script>window.location = '" . $page_to_redirect_after_action . "';</script>";
            }
            exit;

        } catch (PDOException $e) {
            $message = "Error activating faculty accounts: " . $e->getMessage();
            $_SESSION['message'] = ['type' => 'danger', 'text' => "Error: " . htmlspecialchars($e->getMessage())];
            echo "<script>window.location = '" . $page_to_redirect_after_action . "';</script>";
            exit;
        }
    } else {
        $message = "Please select at least one filter criterion (Gender or Department) to activate specific faculty accounts, or use the 'Activate ALL Faculty' button.";
        $_SESSION['message'] = ['type' => 'warning', 'text' => $message];
         echo "<script>window.location = '" . $page_to_redirect_after_action . "';</script>";
        exit;
    }
}
// --- End: Handle Conditional Activation for Faculty ---


// --- Start: Handle "Activate All Faculty" Flag ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_all_faculty_flag']) && $_POST['activate_all_faculty_flag'] === 'true') {
    try {
        // Update only users with role_id = 5
        $stmt = $pdo->prepare("UPDATE users SET account = 'Active' WHERE role_id = :role_id");
        // Optionally add: AND account = 'Inactive'
        // $stmt = $pdo->prepare("UPDATE users SET account = 'Active' WHERE role_id = :role_id AND account = 'Inactive'");
        $stmt->execute([':role_id' => 5]);
        $affected_rows_all = $stmt->rowCount();

        if ($affected_rows_all > 0) {
             $_SESSION['message'] = ['type' => 'success', 'text' => "All faculty accounts ({$affected_rows_all}) have been activated successfully!"];
        } else {
             $_SESSION['message'] = ['type' => 'info', 'text' => "No faculty accounts needed activation or no faculty found."];
        }
        echo "<script>window.location = '" . $page_for_final_success . "';</script>";

    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "Error activating all faculty accounts: " . htmlspecialchars($e->getMessage())];
        echo "<script>window.location = '" . $page_for_final_success . "';</script>"; // Or redirect to current page with error
    }
    exit;
}
// --- End: Handle "Activate All Faculty" Flag ---

// Display session messages if redirected back to this page
if (isset($_SESSION['message'])) {
    $msg_type = $_SESSION['message']['type'] ?? 'info';
    $msg_text = $_SESSION['message']['text'] ?? '';
    $message = "<div class='alert alert-{$msg_type} alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                    " . htmlspecialchars($msg_text) . "
                  </div>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Faculty Accounts</title>
    <!-- Assuming head.php includes necessary CSS. If custom styles are in style_activate.css, ensure it's linked. -->
    <!-- <link rel="stylesheet" href="css/style_activate.css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Basic styling, adapt as needed from your style_activate.css or Bootstrap */
        body { font-family: 'Roboto', sans-serif; margin: 0; background-color: #f4f7f6; }
        #page-wrapper { padding: 20px; } /* Assuming SB Admin structure */
        .container-activate { max-width: 700px; margin: 20px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container-activate h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .instructions { text-align: center; margin-bottom: 20px; color: #555; font-size: 0.9em; }
        .form-group { margin-bottom: 20px; }
        .form-group legend { font-size: 1.1em; font-weight: 500; margin-bottom: 10px; color: #444; border-bottom: 1px solid #eee; padding-bottom: 5px;}
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        .form-group select, .form-group input[type="text"], .form-group input[type="radio"] { margin-right: 5px; }
        .form-group .radio-group label { margin-right: 15px; font-weight: normal; display: inline-block; }
        .form-group select.form-control, .form-group input[type="text"].form-control { /* Assuming Bootstrap classes */
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        input[type="submit"], button.activate-all-faculty {
            background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; width: 100%; display: block; text-align: center; margin-top: 10px;
        }
        input[type="submit"]:hover, button.activate-all-faculty:hover { background-color: #218838; }
        button.activate-all-faculty { background-color: #007bff; }
        button.activate-all-faculty:hover { background-color: #0056b3; }
        hr { margin-top: 30px; margin-bottom: 20px; border-top: 1px solid #eee; }
        .alert { margin-top: 15px;}
        .text-danger { color: #dc3545; } /* Bootstrap error color */
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div class="container-activate">
            <h2>Activate Faculty Accounts</h2>

            <?php if (!empty($message)): echo $message; endif; ?>

            <p class="instructions">Select criteria to activate faculty accounts. If multiple filters are selected, faculty must match ALL criteria. <br>This action will only affect faculty (Role ID: 5).</p>

            <form action="activate_accounts.php" method="POST">
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

                <input type="submit" value="Activate Selected Faculty">
            </form>

            <hr>

            <p style="text-align: center; margin-bottom: 10px; font-weight:500;">Alternatively:</p>
            <button type="button" class="activate-all-faculty" onclick="activateAllFaculty()">Activate ALL Faculty Accounts</button>
        </div>
    </div> <!-- /#page-wrapper -->
</div> <!-- /#wrapper -->

<?php include 'script.php'; // For DataTables, Bootstrap JS etc. if needed here ?>
<script>
function activateAllFaculty() {
    if (confirm('Are you sure you want to activate ALL FACULTY user accounts (Role ID: 5)? This may affect many records.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'activate_accounts.php'; // Submits to the current page

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'activate_all_faculty_flag'; // Changed flag name
        hiddenInput.value = 'true';
        form.appendChild(hiddenInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>