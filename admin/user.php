<?php
// Ensure session is started and user is authenticated
// session.php should handle session_start(), authentication, and setting $_SESSION['user_id'], $_SESSION['role']
include('session.php');
include('head.php');

// Get the logged-in user's role and ID from the session
$loggedInUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$loggedInUserRole = isset($_SESSION['role']) ? (int)$_SESSION['role'] : null;

// --- Debugging: Output session role ---
// echo "<!-- DEBUG: Logged-in User Role from session: " . htmlspecialchars($loggedInUserRole ?? 'Not Set') . " -->";

if ($loggedInUserId === null || $loggedInUserRole === null) {
    // Redirect to login or show an error if session data is missing
    // For now, let's assume session.php handles this, but good to be defensive
    echo "<p>Error: User not authenticated. Please log in.</p>";
    // include('footer.php'); // Or however your script ends
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Users - Admin Panel</title>
    <?php
    // head.php includes CSS
    ?>
    <style>
        /* Define primary color variables */
        :root {
            --primary-color: #90D1CA;
            --primary-dark: #75B5AE;
            --primary-light: #A8DCD6;
            --primary-very-light: #E5F4F2;
            --text-on-primary: #333333;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        /* Modern page header */
        .modern-page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modern-page-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }

        .modern-page-header i {
            margin-right: 10px;
            opacity: 0.9;
        }

        /* Modern action buttons container */
        .action-buttons-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }

        .action-buttons-container h4 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
        }

        /* Modern buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #20c997 0%, var(--success-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, var(--danger-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-disabled-custom.disabled {
            pointer-events: none;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none !important;
        }

        /* Modern table panel */
        .modern-table-panel {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
        }

        .modern-table-panel .panel-heading {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
            color: white !important;
            border: none !important;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 18px;
        }

        .modern-table-panel .panel-body {
            padding: 0;
            background: white;
        }

        /* Modern table styling */
        #dataTables-example {
            margin-bottom: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        #dataTables-example thead th {
            background: var(--primary-light) !important;
            color: var(--text-on-primary) !important;
            border: none !important;
            padding: 15px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            position: relative;
        }

        #dataTables-example tbody td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
            font-size: 14px;
        }

        #dataTables-example tbody tr:hover {
            background-color: var(--primary-very-light) !important;
        }

        /* Action column styling */
        #dataTables-example th.action-column,
        #dataTables-example td.action-column {
            width: 180px;
            min-width: 160px;
            text-align: center;
            white-space: nowrap;
        }

        /* Modern action buttons in table */
        .btn-xs {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            margin: 2px;
        }

        .btn-outline {
            border: 2px solid;
            background: transparent;
        }

        .btn-success.btn-outline {
            border-color: var(--success-color);
            color: var(--success-color);
        }

        .btn-success.btn-outline:hover {
            background: var(--success-color);
            color: white;
        }

        .btn-danger.btn-outline {
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .btn-danger.btn-outline:hover {
            background: var(--danger-color);
            color: white;
        }

        /* Role badge styling */
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-badge.admin {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .role-badge.faculty {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .role-badge.student {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .role-badge.staff {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }

        .role-badge.default {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            color: white;
        }

        /* Alert styling */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-dismissible .close {
            position: relative;
            top: -2px;
            right: -21px;
            color: inherit;
        }

        /* No actions text */
        .text-muted.no-actions {
            font-style: italic;
            color: #6c757d;
            font-size: 12px;
        }

        .icon-large-red-x {
            font-size: 1.3em;
            vertical-align: middle;
        }

        /* DataTables modern styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            border: 2px solid #e9ecef;
            padding: 6px 10px;
        }

        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-light) !important;
            border-color: var(--primary-light) !important;
            color: var(--text-on-primary) !important;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .modern-page-header {
                padding: 20px;
                text-align: center;
            }

            .action-buttons-container {
                padding: 15px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
                margin-right: 0;
            }

            #dataTables-example th.action-column,
            #dataTables-example td.action-column {
                min-width: 120px;
            }
        }

        /* Custom divider */
        .modern-divider {
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border: none;
            border-radius: 2px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include('side_bar.php'); ?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="modern-page-header">
                        <h3><i class="fa fa-users fa-fw"></i> System User Management</h3>
                    </div>

                    <?php
                    if (isset($_SESSION['user_message'])) {
                        $message_type = $_SESSION['user_message']['type'] ?? 'info';
                        $message_text = $_SESSION['user_message']['text'] ?? '';
                        echo "<div class='alert alert-{$message_type} alert-dismissible' role='alert'>";
                        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>";
                        echo htmlspecialchars($message_text);
                        echo "</div>";
                        unset($_SESSION['user_message']);
                    }
                    ?>

                    <div class="action-buttons-container">
                        <h4><i class="fa fa-plus-circle"></i> Quick Actions</h4>
                        <?php if ($loggedInUserRole === 1) : ?>
                            <a href="add_user_id.php" class="btn btn-success">
                                <i class="fa fa-user-plus"></i> Add Staff/Committee
                            </a>
                        <?php else : ?>
                            <a href="javascript:void(0);" class="btn btn-danger disabled btn-disabled-custom" title="You do not have permission to add staff">
                                <i class="fa fa-ban icon-large-red-x"></i> Add Staff/Committee (No Access)
                            </a>
                        <?php endif; ?>

                        <?php if ($loggedInUserRole === 1 || $loggedInUserRole === 4) : // Super Admin or Mini Admin can add students ?>
                            <a href="add_student.php" class="btn btn-success">
                                <i class="fa fa-graduation-cap"></i> Add Student/Faculty
                            </a>
                        <?php else: ?>
                             <a href="javascript:void(0);" class="btn btn-danger disabled btn-disabled-custom" title="You do not have permission to add students">
                                <i class="fa fa-ban icon-large-red-x"></i> Add Student (No Access)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modern-divider"></div>
                <div class="col-lg-12">
                    <div class="panel modern-table-panel">
                        <div class="panel-heading">
                            <i class="fa fa-table fa-fw"></i> System User Directory
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Firstname</th>
                                            <th>Lastname</th>
                                            <th>Contact</th>
                                            <th>Gender</th>
                                            <th>Role</th> <!-- Changed from Role ID for better readability -->
                                            <th class="action-column">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'dbcon.php';

                                        try {
                                            $sql = "";
                                            $params = []; // Initialize params array

                                            if ($loggedInUserRole === 1) { // Super Admin sees everyone
                                                // Join with role_table to get role_name
                                                $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.phone, u.gender, u.role_id, u.email, r.role_name
                                                        FROM users u
                                                        LEFT JOIN role_table r ON u.role_id = r.role_id
                                                        ORDER BY u.user_id DESC";
                                            } elseif ($loggedInUserRole === 4) { // Mini Admin (role 4)
                                                // Can see roles other than 1 and 4.
                                                $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.phone, u.gender, u.role_id, u.email, r.role_name
                                                        FROM users u
                                                        LEFT JOIN role_table r ON u.role_id = r.role_id
                                                        WHERE u.role_id NOT IN (1, 4)
                                                        ORDER BY u.user_id DESC";
                                            } elseif ($loggedInUserRole === 5) { // Faculty (role 5) sees Students (role 3)
                                                $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.phone, u.gender, u.role_id, u.email, r.role_name
                                                        FROM users u
                                                        LEFT JOIN role_table r ON u.role_id = r.role_id
                                                        WHERE u.role_id = :role_to_view";
                                                $params[':role_to_view'] = 3; // Faculty views role 3 (Students)
                                            } elseif ($loggedInUserRole === 3) { // Student (role 3) sees Faculty (role 5)
                                                $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.phone, u.gender, u.role_id, u.email, r.role_name
                                                        FROM users u
                                                        LEFT JOIN role_table r ON u.role_id = r.role_id
                                                        WHERE u.role_id = :role_to_view";
                                                $params[':role_to_view'] = 5; // Student views role 5 (Faculty)
                                            } else { // Other roles see no one by default
                                                $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.phone, u.gender, u.role_id, u.email, r.role_name
                                                        FROM users u
                                                        LEFT JOIN role_table r ON u.role_id = r.role_id
                                                        WHERE 1=0"; // Returns no rows
                                            }

                                            $stmt = $pdo->prepare($sql);
                                            $stmt->execute($params); // Pass params array here
                                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            if ($users) {
                                                foreach ($users as $row) {
                                                    $user_id_html = htmlspecialchars($row['user_id']);
                                                    $row_user_id_int = (int)$row['user_id'];
                                                    $row_user_role_id_int = (int)$row['role_id'];
                                                    $role_name_display = htmlspecialchars($row['role_name'] ?? 'N/A'); // Display role name

                                                    // Determine role badge class
                                                    $role_badge_class = 'default';
                                                    switch($row_user_role_id_int) {
                                                        case 1:
                                                        case 4:
                                                            $role_badge_class = 'admin';
                                                            break;
                                                        case 2:
                                                            $role_badge_class = 'staff';
                                                            break;
                                                        case 3:
                                                            $role_badge_class = 'student';
                                                            break;
                                                        case 5:
                                                            $role_badge_class = 'faculty';
                                                            break;
                                                    }

                                                    $canEditDeleteThisUser = false;

                                                    if ($loggedInUserRole === 1) { // Super Admin
                                                        if ($loggedInUserId !== $row_user_id_int) { // Cannot act on self
                                                            $canEditDeleteThisUser = true;
                                                        }
                                                    } elseif ($loggedInUserRole === 4) { // Mini Admin
                                                        // Can act on roles 2, 3, 5 (Staff, Student, Faculty) - assuming these are not 1 or 4
                                                        if (in_array($row_user_role_id_int, [2, 3, 5])) { // Adjusted to include faculty
                                                             $canEditDeleteThisUser = true;
                                                        }
                                                    }
                                                    // Add logic for Faculty (5) or Student (3) to edit/delete if needed
                                                    // For now, only Admins can edit/delete.
                                                    // Example: Faculty can edit Students they see
                                                    /*
                                                    elseif ($loggedInUserRole === 5 && $row_user_role_id_int === 3) {
                                                        $canEditDeleteThisUser = true; // Faculty can edit students
                                                    }
                                                    */
                                        ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge badge-secondary">
                                                            <?php echo htmlspecialchars($row['gender']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="role-badge <?php echo $role_badge_class; ?>">
                                                            <?php echo $role_name_display; ?>
                                                        </span>
                                                        <small class="text-muted d-block" style="margin-top: 4px;">ID: <?php echo htmlspecialchars($row_user_role_id_int); ?></small>
                                                    </td>
                                                    <td class="action-column">
                                                        <?php if ($canEditDeleteThisUser) : ?>
                                                            <a rel="tooltip" title="Edit User"
                                                               href="#edit_user<?php echo $user_id_html; ?>"
                                                               data-target="#edit_user<?php echo $user_id_html; ?>"
                                                               data-toggle="modal" class="btn btn-success btn-outline btn-xs">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            <?php // Pass $row to the modal if it needs more than just user_id
                                                                // Or fetch user details again within the modal based on user_id
                                                                // For simplicity, assuming modal fetches its own data or $row is scoped if include is direct
                                                                // It's better if edit_user_modal.php fetches fresh data based on $user_id_html
                                                                $_GET['user_id_for_modal'] = $row['user_id']; // A way to pass id, but modal needs to use it
                                                                include('edit_user_modal.php'); // Make sure this modal can work with the passed ID or fetches data
                                                            ?>

                                                            <a rel="tooltip" title="Delete User"
                                                               href="#delete_admin<?php echo $user_id_html; ?>"
                                                               data-target="#delete_admin<?php echo $user_id_html; ?>"
                                                               data-toggle="modal" class="btn btn-danger btn-outline btn-xs">
                                                                <i class="fa fa-trash-o"></i> Delete
                                                            </a>
                                                            <?php // Similar to edit modal for delete modal
                                                                $_GET['user_id_for_modal'] = $row['user_id'];
                                                                include('delete_user_modal.php');
                                                            ?>
                                                        <?php else: ?>
                                                            <span class="text-muted no-actions">No Actions</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                        <?php
                                                } // End foreach
                                            } else {
                                                $colspan = 7; // Username, Firstname, Lastname, Contact, Gender, Role, Action
                                                echo "<tr><td colspan='{$colspan}' class='text-center'>No users found matching your criteria.</td></tr>";
                                            }
                                        } catch (PDOException $e) {
                                            $colspan = 7;
                                            echo "<tr><td colspan='{$colspan}' class='text-center text-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                            error_log("User List Page - Database Error: " . $e->getMessage());
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('script.php'); ?>
    <script>
        $(document).ready(function() {
            if ( ! $.fn.DataTable.isDataTable( '#dataTables-example' ) ) {
                $('#dataTables-example').DataTable({
                    responsive: true,
                    // "order": [[ 1, "asc" ]] // Example: Order by Firstname
                });
            }
            // Ensure modals are correctly handled if you're including them multiple times inside the loop
            // It's generally better to have one edit modal and one delete modal outside the loop
            // and populate them dynamically with JavaScript when an edit/delete button is clicked.
            // The current include('modal.php') inside the loop can lead to multiple modals with same IDs if not careful.
            // The data-target="#edit_user<?php echo $user_id_html; ?>" unique ID helps, but ensure modal PHP is fine with it.
        });
    </script>
</body>
</html>