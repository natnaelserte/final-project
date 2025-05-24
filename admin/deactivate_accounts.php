<?php
require_once 'dbcon.php'; // Your database connection
include 'head.php';
include 'side_bar.php';
$message = ''; // To store success or error messages
$page_to_redirect_after_action = 'deactivate_accounts.php'; // Default page to reload for adjustments
$page_for_final_success = 'index.php'; // Page to go to after successful deactivation

// --- Fetch distinct departments for the dropdown ---
$departments = [];
try {
    $stmt_dept = $pdo->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching departments: " . $e->getMessage());
}

// --- Fetch distinct club memberships for the dropdown ---
$club_memberships = [];
try {
    $stmt_club = $pdo->query("SELECT DISTINCT club_membership FROM users WHERE club_membership IS NOT NULL AND club_membership != '' AND club_membership != 'None' ORDER BY club_membership ASC");
    $club_memberships = $stmt_club->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching club memberships: " . $e->getMessage());
}
// --- End Fetch club memberships ---


// --- Start: Handle Conditional Deactivation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['deactivate_all_flag'])) {
    $gender_filter = $_POST['gender_filter'] ?? '';
    $batch_filter = $_POST['batch_filter'] ?? '';
    $department_filter = $_POST['department_filter'] ?? '';
    $club_membership_filter = $_POST['club_membership_filter'] ?? ''; // <-- ADDED
    $representative_filter = $_POST['representative_filter'] ?? '';   // <-- ADDED

    $conditions = [];
    $params = [];

    // --- Build WHERE conditions ---
    if (!empty($gender_filter) && in_array($gender_filter, ['male', 'female'])) {
        $conditions[] = "gender = :gender";
        $params[':gender'] = $gender_filter;
    }

    if (!empty($batch_filter)) {
        if (in_array($batch_filter, ['13', '14', '15', '16', '17'])) {
            $conditions[] = "RIGHT(id_number, 2) = :batch_ending";
            $params[':batch_ending'] = $batch_filter;
        } elseif ($batch_filter === 'other_batch') {
            $conditions[] = "RIGHT(id_number, 2) NOT IN ('13', '14', '15', '16', '17')";
        }
    }

    if (!empty($department_filter)) {
        $conditions[] = "department = :department";
        $params[':department'] = $department_filter;
    }

    if (!empty($club_membership_filter)) { // <-- ADDED: Club membership condition
        if ($club_membership_filter === 'IsMember') {
            $conditions[] = "club_membership != :club_none AND club_membership IS NOT NULL AND club_membership != ''";
            $params[':club_none'] = 'None';
        } elseif ($club_membership_filter === 'NotMember') {
             $conditions[] = "(club_membership = :club_none OR club_membership IS NULL OR club_membership = '')";
             $params[':club_none'] = 'None';
        } elseif ($club_membership_filter !== 'Any') {
            $conditions[] = "club_membership = :club_membership";
            $params[':club_membership'] = $club_membership_filter;
        }
    }

    if (!empty($representative_filter) && in_array($representative_filter, ['Yes', 'No'])) { // <-- ADDED: Representative condition
        $conditions[] = "is_class_representative = :is_representative";
        $params[':is_representative'] = $representative_filter;
    }

    // --- Execute Update for Conditional Deactivation ---
    if (!empty($conditions)) {
        $sql = "UPDATE users SET account = 'Inactive'";
        $sql .= " WHERE " . implode(" AND ", $conditions);

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $affected_rows = $stmt->rowCount();

            if ($affected_rows > 0) {
                $message = "Successfully deactivated " . $affected_rows . " user account(s) based on your criteria.";
                echo "<script>
                    alert('" . htmlspecialchars($message) . "');
                    window.location = '" . $page_for_final_success . "';
                </script>";
            } else {
                $message = "No user accounts matched your criteria or they were already inactive.";
                echo "<script>
                    alert('" . htmlspecialchars($message) . "');
                    window.location = '" . $page_to_redirect_after_action . "';
                </script>";
            }
            exit;

        } catch (PDOException $e) {
            $message = "Error deactivating accounts: " . $e->getMessage();
            echo "<script>
                alert('Error: " . htmlspecialchars($e->getMessage()) . "');
                window.location = '" . $page_to_redirect_after_action . "';
            </script>";
            exit;
        }
    } else {
        $message = "Please select at least one specific filter criterion (gender, batch, department, club, or representative status) to deactivate accounts, or use the 'Deactivate ALL Users' button."; // <-- MODIFIED
         echo "<script>
            alert('" . htmlspecialchars($message) . "');
            window.location = '" . $page_to_redirect_after_action . "';
        </script>";
        exit;
    }
}
// --- End: Handle Conditional Deactivation ---


// --- Start: Handle "Deactivate All" Flag ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_all_flag']) && $_POST['deactivate_all_flag'] === 'true') {
    try {
        $stmt = $pdo->prepare("UPDATE users SET account = 'Inactive'");
        $stmt->execute();
        echo "<script>
            alert('All user accounts have been deactivated successfully!');
            window.location = '" . $page_for_final_success . "';
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            alert('Error deactivating all accounts: " . htmlspecialchars($e->getMessage()) . "');
            window.location = '" . $page_for_final_success . "';
        </script>";
    }
    exit;
}
// --- End: Handle "Deactivate All" Flag ---

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deactivate User Accounts</title>
    <link rel="stylesheet" href="css/style_deactivate.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .radio-group label { margin-right: 15px; font-weight: normal;}
    </style>
</head>
<body>

<div class="container">
    <h2>Deactivate User Accounts</h2>

    <?php if (!empty($message) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <div class="message <?php echo (strpos(strtolower($message), 'error') !== false || strpos(strtolower($message), 'please select') !== false) ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <p class="instructions">Select criteria to deactivate user accounts. If multiple filters are selected, users must match ALL criteria.</p>

    <form action="deactivate_accounts.php" method="POST">
        <div class="form-group">
            <legend>Filter by Gender</legend>
            <label for="gender_any">
                <input type="radio" id="gender_any" name="gender_filter" value="" checked> Any
            </label>
            <label for="gender_male">
                <input type="radio" id="gender_male" name="gender_filter" value="male"> Male
            </label>
            <label for="gender_female">
                <input type="radio" id="gender_female" name="gender_filter" value="female"> Female
            </label>
        </div>

        <div class="form-group">
            <label for="batch_filter_select">Filter by Batch (last 2 digits of ID Number)</label>
            <select id="batch_filter_select" name="batch_filter">
                <option value="" selected>Any Batch</option>
                <option value="13">Ends in 13</option>
                <option value="14">Ends in 14</option>
                <option value="15">Ends in 15</option>
                <option value="16">Ends in 16</option>
                <option value="17">Ends in 17</option>
                <option value="other_batch">Other (Not 13-17)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="department_filter_select">Filter by Department</label>
            <select id="department_filter_select" name="department_filter">
                <option value="" selected>Any Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>">
                        <?php echo htmlspecialchars($dept); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Club Membership Filter Dropdown -->
        <div class="form-group">
            <label for="club_membership_filter_select">Filter by Club Membership</label>
            <select id="club_membership_filter_select" name="club_membership_filter">
                <option value="Any" selected>Any Club Status</option>
                <option value="IsMember">Is a Club Member (Any Club)</option>
                <option value="NotMember">Not a Club Member (None)</option>
                <?php foreach ($club_memberships as $club): ?>
                    <option value="<?php echo htmlspecialchars($club); ?>">
                        <?php echo htmlspecialchars($club); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- End Club Membership Filter -->

        <!-- Class Representative Filter -->
        <div class="form-group">
            <legend>Filter by Class Representative Status</legend>
            <div class="radio-group">
                <label for="representative_any">
                    <input type="radio" id="representative_any" name="representative_filter" value="" checked> Any Status
                </label>
                <label for="representative_yes">
                    <input type="radio" id="representative_yes" name="representative_filter" value="Yes"> Is Representative
                </label>
                <label for="representative_no">
                    <input type="radio" id="representative_no" name="representative_filter" value="No"> Not Representative
                </label>
            </div>
        </div>
        <!-- End Class Representative Filter -->

        <input type="submit" value="Deactivate Selected Users">
    </form>

    <hr>

    <p style="text-align: center; margin-bottom: 10px; font-weight:500;">Alternatively:</p>
    <button type="button" class="deactivate-all" onclick="deactivateAllUsers()">Deactivate ALL User Accounts</button>
</div>

<script>
function deactivateAllUsers() {
    if (confirm('Are you sure you want to DEACTIVATE ALL user accounts? This action will mark them as inactive.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'deactivate_accounts.php';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'deactivate_all_flag';
        hiddenInput.value = 'true';
        form.appendChild(hiddenInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>