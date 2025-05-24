<?php
// Ensure session is started and user is authenticated
// session.php should handle session_start(), authentication, and setting $_SESSION['user_id'], $_SESSION['role']
include('session.php'); 
include('head.php'); 

// Get the logged-in user's role and ID from the session
$loggedInUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$loggedInUserRole = isset($_SESSION['role']) ? (int)$_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Users - Admin Panel</title>
    <?php 
    // head.php should include:
    // - Bootstrap CSS
    // - Font Awesome CSS (if using fa- icons)
    // - SB Admin 2 Theme CSS
    // - Any other global CSS or custom stylesheets (like the one for attractive modals if you aren't using Bootstrap for it)
    ?>
    
    <style>
        /* Custom CSS for disabled "Add staff" button */
        .btn-disabled-custom.disabled {
            pointer-events: none; 
            cursor: not-allowed; 
        }
        .icon-large-red-x {
            font-size: 1.3em; 
            vertical-align: middle;
        }
        /* Style for "No Actions" text */
        .text-muted.no-actions { /* Added a class for more specific targeting if needed */
            font-style: italic;
            color: #777; 
        }
        /* Ensure action column has enough width */
        #dataTables-example th.action-column,
        #dataTables-example td.action-column {
            width: 150px; /* Adjust as needed */
            min-width: 130px;
            text-align: center;
            white-space: nowrap;
        }
        /* Ensure alert messages are styled correctly if using Bootstrap 3 */
        .alert-dismissible .close {
            position: relative;
            top: -2px;
            right: -21px;
            color: inherit;
        }
    </style>
</head>
<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header"><i class="fa fa-users fa-fw"></i> System User List</h3>

                    <?php
                    // Display session messages (from delete_user.php, update_user.php, etc.)
                    if (isset($_SESSION['user_message'])) {
                        $message_type = $_SESSION['user_message']['type'] ?? 'info'; 
                        $message_text = $_SESSION['user_message']['text'] ?? '';
                        echo "<div class='alert alert-{$message_type} alert-dismissible' role='alert'>"; // Removed fade in for BS3
                        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>";
                        echo htmlspecialchars($message_text);
                        echo "</div>";
                        unset($_SESSION['user_message']); 
                    }
                    ?>

                    <?php
                    // "Add staff" button logic (Super Admin role 1 only)
                    if ($loggedInUserRole === 1) :
                    ?>
                        <a href="add_user_id.php" class="btn btn-success">
                            <i class="glyphicon glyphicon-plus"></i> Add Staff
                        </a>
                    <?php else : // Disabled for other roles
                    ?>
                        <a href="javascript:void(0);" class="btn btn-danger disabled btn-disabled-custom" title="You do not have permission to add staff">
                            <i class="glyphicon glyphicon-remove icon-large-red-x"></i> Add Staff (No Access)
                        </a>
                    <?php endif; ?>
                    
                    <a href="add_student.php" class="btn btn-success"> 
                        <i class="glyphicon glyphicon-user"></i> Add Student
                    </a>
                </div>
                <hr style="width: 97%; margin-left: 15px; margin-right: 15px;">


                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title" style="padding:0; margin:0;"> <?/* Removed modal-title and myModalLabel here */?>
                                <div class="panel panel-primary" style="margin-bottom:0;">
                                    <div class="panel-heading">
                                        <i class="fa fa-list fa-fw"></i> System User List
                                    </div>
                                </div>
                            </h4>
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
                                            <th>Role ID</th>
                                            <th class="action-column">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'dbcon.php'; 

                                        try {
                                            $sql = "";
                                            $params = []; // For potential future use with query parameters

                                            if ($loggedInUserRole === 1) { // Super Admin sees everyone
                                                $sql = "SELECT user_id, username, firstname, lastname, phone, gender, role_id, email FROM users ORDER BY user_id DESC";
                                            } elseif ($loggedInUserRole === 4) { // Mini Admin (role 4)
                                                $sql = "SELECT user_id, username, firstname, lastname, phone, gender, role_id, email FROM users
                                                        WHERE role_id NOT IN (1, 4)
                                                        ORDER BY user_id DESC";
                                            } else { // Other roles see no one in this specific list by default
                                                $sql = "SELECT user_id, username, firstname, lastname, phone, gender, role_id, email FROM users WHERE 1=0"; // Returns no rows
                                            }

                                            $stmt = $pdo->prepare($sql);
                                            $stmt->execute($params); 
                                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            if ($users) {
                                                foreach ($users as $row) { // $row is now available to included modals
                                                    $user_id = htmlspecialchars($row['user_id']); // String version for HTML attributes/includes
                                                    $row_user_id_int = (int)$row['user_id']; // Integer for PHP comparisons
                                                    $row_user_role_id_int = (int)$row['role_id']; // Integer for PHP comparisons

                                                    $canEditDeleteThisUser = false; // Default permission

                                                    if ($loggedInUserRole === 1) { // Logged-in is Super Admin
                                                        if ($loggedInUserId !== $row_user_id_int) { // Cannot act on self
                                                            $canEditDeleteThisUser = true;
                                                        }
                                                    } elseif ($loggedInUserRole === 4) { // Logged-in is Mini Admin
                                                        // Can act on roles 2 and 3 (already filtered by SQL for visibility)
                                                        if (in_array($row_user_role_id_int, [2, 3])) {
                                                             $canEditDeleteThisUser = true;
                                                        }
                                                    }
                                        ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($row_user_role_id_int); ?></td>
                                                    <td class="action-column">
                                                        <?php if ($canEditDeleteThisUser) : ?>
                                                            <a rel="tooltip" title="Edit User" 
                                                               id="edit_trigger_<?php echo $user_id; ?>" 
                                                               href="#edit_user<?php echo $user_id; ?>" 
                                                               data-target="#edit_user<?php echo $user_id; ?>" 
                                                               data-toggle="modal" class="btn btn-success btn-outline btn-xs">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            <?php include('edit_user_modal.php'); ?>
                                                            
                                                            <a rel="tooltip" title="Delete User" 
                                                               id="delete_trigger_<?php echo $user_id; ?>" 
                                                               href="#delete_admin<?php echo $user_id; ?>" 
                                                               data-target="#delete_admin<?php echo $user_id; ?>" 
                                                               data-toggle="modal" class="btn btn-danger btn-outline btn-xs">
                                                                <i class="fa fa-trash-o"></i> Delete
                                                            </a>
                                                            <?php include('delete_user_modal.php'); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted no-actions">No Actions</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                        <?php
                                                } // End foreach
                                            } else {
                                                $colspan = count(['Username', 'Firstname', 'Lastname', 'Contact', 'Gender', 'Role ID', 'Action']);
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
            // Initialize DataTables only if it hasn't been initialized yet for this table
            if ( ! $.fn.DataTable.isDataTable( '#dataTables-example' ) ) {
                $('#dataTables-example').DataTable({
                    responsive: true,
                    // You can add other DataTables options here:
                    // "order": [[ 1, "asc" ]], // Example: Order by second column (Firstname) ascending
                    // "pageLength": 10,
                    // "language": {
                    //     "emptyTable": "No users available in table",
                    //     "zeroRecords": "No matching users found"
                    // }
                });
            }
        });
    </script>
</body>
</html>