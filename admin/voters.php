<?php
include('session.php'); // Handles session_start() and sets $_SESSION['user_id'] (ensure it's robust)
include('head.php');    // Includes Bootstrap CSS, Font Awesome, Theme CSS, etc.
require_once 'dbcon.php'; // Your PDO database connection object $pdo

// Ensure PDO object is available
if (!isset($pdo)) {
    error_log("FATAL ERROR: Database connection object (\$pdo) not available in manage_users.php.");
    die("<div style='padding:20px; background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb;'>
            <strong>Critical Error:</strong> Database connection failed. Please contact the system administrator.
         </div>");
}

// Fetch data for "Initiate/Edit Voting" Modal
$editMode = false;
$votingEventTitle = '';
$votingEventHours = '';
try {
    $queryVotingEvent = $pdo->prepare("SELECT * FROM voting_events WHERE is_active = 1");
    $queryVotingEvent->execute();
    $votingEvent = $queryVotingEvent->fetch(PDO::FETCH_ASSOC);
    $editMode = (bool)$votingEvent;
    if ($editMode && $votingEvent) {
        $votingEventTitle = $votingEvent['title'] ?? '';
        $votingEventHours = $votingEvent['duration_hours'] ?? '';
    }
} catch (PDOException $e) {
    error_log("Error fetching voting event in manage_users.php: " . $e->getMessage());
}

// Fetch counts for dashboard boxes and charts
$activeUserCount = 0;
$inactiveUserCount = 0;
$studentTotalCount = 0;
$facultyTotalCount = 0;

try {
    $activeUserCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Active' AND role_id IN (3, 5)");
    if($activeUserCountStmt) $activeUserCount = $activeUserCountStmt->fetchColumn();

    $inactiveUserCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE account = 'Inactive' AND role_id IN (3, 5)");
    if($inactiveUserCountStmt) $inactiveUserCount = $inactiveUserCountStmt->fetchColumn();

    $studentTotalCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3");
    if($studentTotalCountStmt) $studentTotalCount = $studentTotalCountStmt->fetchColumn();

    $facultyTotalCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 5");
    if($facultyTotalCountStmt) $facultyTotalCount = $facultyTotalCountStmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error fetching user counts in manage_users.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="css/modern-admin.css">
</head>
<body>
    <div id="wrapper">

        <?php include('side_bar.php'); ?>

        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="modern-page-header">
                            <h1><i class="fa fa-users"></i> User Management Dashboard</h1>
                        </div>
                         <?php
                        // Display session messages
                        if (isset($_SESSION['message'])) { /* ... existing message display ... */ }
                        if (isset($_GET['error_msg'])) { /* ... existing message display ... */ }
                        if (isset($_GET['success_msg'])) { /* ... existing message display ... */ }
                        ?>
                    </div>
                </div>

                <!-- Action Buttons Row (Top) -->
                <div class="action-buttons-container">
                    <h4><i class="fa fa-cogs"></i> Quick Actions</h4>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <?php
                            if ($editMode) {
                                echo '<button type="button" class="btn btn-warning btn-lg btn-block" data-toggle="modal" data-target="#votingModal">
                                        <i class="fa fa-pencil-square-o"></i> Edit Voting Event
                                      </button>';
                            } else {
                                echo '<button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#votingModal">
                                        <i class="fa fa-check-square-o"></i> Initiate Voting Event
                                      </button>';
                            }
                            ?>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <a href="voters_excel.php" class="btn btn-success btn-lg btn-block">
                                <i class="fa fa-file-excel-o"></i> Export Users to Excel
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <button type="button" class="btn btn-success btn-lg btn-block" data-toggle="modal" data-target="#roleActionModal" data-action-type="activate">
                                <i class="fa fa-check-circle"></i> Activate Accounts
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                             <button type="button" class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#roleActionModal" data-action-type="deactivate">
                                <i class="fa fa-times-circle"></i> Deactivate Accounts
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Count Boxes -->
                <div class="row" style="margin-bottom: 20px;">
                     <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3"><i class="fa fa-graduation-cap fa-2x"></i></div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo $studentTotalCount; ?></div>
                                        <div>Total Students</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3"><i class="fa fa-briefcase fa-2x"></i></div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo $facultyTotalCount; ?></div>
                                        <div>Total Faculty</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card success">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3"><i class="fa fa-check-circle fa-2x"></i></div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo $activeUserCount; ?></div>
                                        <div>Active Users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel modern-stat-card danger">
                           <div class="panel-heading">
                               <div class="row">
                                   <div class="col-xs-3"><i class="fa fa-times-circle fa-2x"></i></div>
                                   <div class="col-xs-9 text-right">
                                       <div class="huge"><?php echo $inactiveUserCount; ?></div>
                                       <div>Inactive Users</div>
                                   </div>
                               </div>
                           </div>
                        </div>
                    </div>
                </div>

                <!-- Toggle User List Button -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-lg-12 text-center">
                        <button type="button" class="btn btn-default btn-lg" id="toggleUserListBtn">
                            <i class="fa fa-list"></i> <span id="toggleUserListBtnText">Show User List</span> <i class="fa fa-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <!-- User Table (Initially Hidden) -->
                <div class="row" id="userListContainer" style="display:none;">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-users fa-fw"></i> User List (Students & Faculty)
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>ID Number</th>
                                                <th>Names</th>
                                                <th>Role</th>
                                                <th>Gender</th>
                                                <th>Phone</th>
                                                <th>Department</th>
                                                <th>Club Membership</th>
                                                <th>Representative?</th>
                                                <th>Vote Status</th>
                                                <th>Account Status</th>
                                                <th>Registered On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $queryUsers = $pdo->query("SELECT user_id, id_number, firstname, lastname, role_id, gender, phone, email, department, club_membership, is_class_representative, status, account, registration_date FROM users WHERE role_id IN (3, 5) ORDER BY user_id DESC");
                                                if ($queryUsers->rowCount() > 0) {
                                                    while ($rowUser = $queryUsers->fetch(PDO::FETCH_ASSOC)) {
                                                        $user_id_html = htmlspecialchars($rowUser['user_id']);
                                                        $account_status = htmlspecialchars($rowUser['account']);
                                                        $vote_status = htmlspecialchars($rowUser['status']);
                                                        $role_display_name = ($rowUser['role_id'] == 3) ? 'Student' : (($rowUser['role_id'] == 5) ? 'Faculty' : 'Unknown');
                                                        $is_rep_display = ($rowUser['is_class_representative'] == 1 || strtolower($rowUser['is_class_representative'] ?? '') === 'yes') ? 'Yes' : 'No';
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rowUser['id_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowUser['firstname'] . " " . $rowUser['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($role_display_name); ?></td>
                                                    <td><?php echo htmlspecialchars($rowUser['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($rowUser['phone'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($rowUser['department'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($rowUser['club_membership'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $is_rep_display; ?></td>
                                                    <td><?php echo $vote_status; ?></td>
                                                    <td><?php echo $account_status; ?></td>
                                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($rowUser['registration_date']))); ?></td>
                                                    <td>
                                                        <?php if ($account_status == 'Inactive') : ?>
                                                            <a href="activate_voter.php?user_id=<?php echo $user_id_html; ?>&role_id=<?php echo $rowUser['role_id']; ?>" class="btn btn-success btn-xs" title="Activate Account"><i class="fa fa-check"></i></a>
                                                        <?php else : ?>
                                                            <a href="deactivate_voter.php?user_id=<?php echo $user_id_html; ?>&role_id=<?php echo $rowUser['role_id']; ?>" class="btn btn-warning btn-xs" title="Deactivate Account"><i class="fa fa-times"></i></a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='12' class='text-center'>No Student or Faculty users found.</td></tr>";
                                                }
                                            } catch (PDOException $e) {
                                                echo "<tr><td colspan='12' class='text-center text-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                                error_log("Error fetching users (role 3 & 5) in manage_users.php: " . $e->getMessage());
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row" style="margin-top: 20px;">
                     <div class="col-md-6 col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fa fa-pie-chart fa-fw"></i> Account Status (Students & Faculty)</div>
                            <div class="panel-body"><div id="userAccountPieChart" style="width: 100%; height: 350px;"></div></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fa fa-bar-chart-o fa-fw"></i> Role Distribution</div>
                            <div class="panel-body"><div id="userRoleBarChart" style="width: 100%; height: 350px;"></div></div>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:20px; margin-bottom: 50px; text-align:center;">
                    <div class="col-lg-12">
                        <button class="btn btn-info" onclick="exportChartAsImage('userAccountPieChart')"><i class="fa fa-download"></i> Account Status Chart</button>
                        <button class="btn btn-info" onclick="exportChartAsImage('userRoleBarChart')"><i class="fa fa-download"></i> Role Distribution Chart</button>
                        <!-- Add buttons for other charts if you re-enable them -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (Voting Modal, Role Action Modal - remain the same) -->
    <div class="modal fade" id="votingModal" tabindex="-1" role="dialog" aria-labelledby="votingModalLabel" aria-hidden="true">
        <!-- ... Voting Modal Content (from previous full code) ... -->
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="initiateVotingForm" onsubmit="return false;">
                    <div class="modal-header"> <h4 class="modal-title" id="votingModalLabel"><?php echo $editMode ? "Edit Voting Event" : "Initiate Voting Event"; ?></h4> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button> </div>
                    <div class="modal-body">
                        <div class="form-group"> <label for="votingTitle">Voting Title <span class="text-danger">*</span></label> <input type="text" class="form-control" id="votingTitle" placeholder="e.g., Student Council Elections 2024" required value="<?php echo htmlspecialchars($votingEventTitle); ?>"> </div>
                        <div class="form-group"> <label for="votingHours">Voting Duration (Hours) <span class="text-danger">*</span></label> <input type="number" class="form-control" id="votingHours" placeholder="e.g., 8" min="1" max="720" required value="<?php echo htmlspecialchars($votingEventHours); ?>"> </div>
                    </div>
                    <div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button> <button type="button" class="btn btn-primary" id="confirmVoting"><?php echo $editMode ? "Update Event" : "Confirm & Start"; ?></button> </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roleActionModal" tabindex="-1" role="dialog" aria-labelledby="roleActionModalLabel" aria-hidden="true">
        <!-- ... Role Action Modal Content (from previous full code) ... -->
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header"> <h4 class="modal-title" id="roleActionModalLabel">Select User Type</h4> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button> </div>
                <div class="modal-body"> <p>Which user group do you want to <strong id="actionTypeTextModal"></strong> accounts for?</p> </div>
                <div class="modal-footer"> <button type="button" class="btn btn-primary btn-block" id="confirmRoleStudentModal">Students</button> <button type="button" class="btn btn-info btn-block" id="confirmRoleFacultyModal" style="margin-top:10px;">Faculty</button> <button type="button" class="btn btn-default btn-block" data-dismiss="modal" style="margin-top:10px;">Cancel</button> </div>
            </div>
        </div>
    </div>

    <?php include('script.php'); ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    $(document).ready(function () {
        // Function to initialize DataTable
        function initializeUserListDataTable() {
            // Check if DataTable has ALREADY been initialized on this element
            if ($.fn.dataTable && !$.fn.DataTable.isDataTable('#dataTables-example')) {
                // console.log("Initializing DataTables for #dataTables-example"); // For debugging
                $('#dataTables-example').DataTable({
                    responsive: true,
                    "pageLength": 10,
                    "order": [[10, "desc"]], // Order by "Registered On" (11th column, index 10)
                    "columnDefs": [
                        { "orderable": false, "targets": 11 } // Disable ordering on "Actions" column (12th column, index 11)
                    ]
                });
            }
        }

        // Click handler for the "Show/Hide User List" button
        $('#toggleUserListBtn').on('click', function(e) {
            e.preventDefault();
            var $userListContainer = $('#userListContainer');
            var $buttonText = $('#toggleUserListBtnText');
            var $buttonIcon = $(this).find('.fa-chevron-down, .fa-chevron-up');

            $userListContainer.slideToggle(400, function() {
                if ($userListContainer.is(':visible')) {
                    initializeUserListDataTable(); // Attempt to initialize if visible
                    $buttonText.text('Hide User List');
                    $buttonIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    $('html, body').animate({
                        scrollTop: $userListContainer.offset().top - 70
                    }, 500);
                } else {
                    $buttonText.text('Show User List');
                    $buttonIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }
            });
        });

        // Voting Modal AJAX (Copied from your previous version)
        $('#confirmVoting').click(function () {
            const title = $('#votingTitle').val().trim();
            const hours = $('#votingHours').val().trim();
            if (title === '' || hours === '' || parseInt(hours) < 1 || parseInt(hours) > 720) {
                alert('Please fill in all fields correctly. Duration must be between 1 and 720 hours.');
                return;
            }
            $('#confirmVoting').prop('disabled', true).text('Processing...');
            $.ajax({
                type: 'POST', url: 'initiate_voting.php',
                data: { title: title, hours: hours, edit: <?php echo $editMode ? 'true' : 'false'; ?> },
                dataType: 'json',
                success: function (response) { if (response.status === 'success') { alert(response.message); $('#votingModal').modal('hide'); window.location.reload(); } else { alert('Error: ' + (response.message || 'An unknown error occurred.')); } },
                error: function (xhr, status, error) { console.error("AJAX Error for voting: ", status, error, xhr.responseText); alert("An error occurred. Details: " + xhr.responseText); },
                complete: function() { $('#confirmVoting').prop('disabled', false).text(<?php echo $editMode ? "'Update Event'" : "'Confirm & Start'"; ?>); }
            });
        });

        // Role Action Modal Logic (Copied from your previous version)
        var currentActionTypeForBulk = '';
        $('#roleActionModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); currentActionTypeForBulk = button.data('action-type'); var modal = $(this);
            if (currentActionTypeForBulk === 'activate') { modal.find('#roleActionModalLabel').text('Activate Accounts For...'); modal.find('#actionTypeTextModal').text('activate');}
            else if (currentActionTypeForBulk === 'deactivate') { modal.find('#roleActionModalLabel').text('Deactivate Accounts For...'); modal.find('#actionTypeTextModal').text('deactivate');}
        });
        $('#confirmRoleStudentModal').click(function() {
            let targetPage = (currentActionTypeForBulk === 'activate') ? 'activate_accounts.php' : 'deactivate_accounts.php';
            if (targetPage) window.location.href = targetPage;
            $('#roleActionModal').modal('hide');
        });
        $('#confirmRoleFacultyModal').click(function() {
            let targetPage = (currentActionTypeForBulk === 'activate') ? 'activate_faculty.php' : 'deactivate_faculty.php';
            if (targetPage) window.location.href = targetPage;
            $('#roleActionModal').modal('hide');
        });

        // Google Charts
        google.charts.load('current', {'packages':['corechart', 'bar']}); // Keep 'bar' if userRoleBarChart uses it
        google.charts.setOnLoadCallback(drawAllCharts);

        function drawAllCharts() {
            drawAccountPieChart();
            drawRoleBarChart();
            // drawGenderChart(); // Call these if you re-add their divs
            // drawVotingStatusChart();
            // drawRegistrationDateChart();
        }

        function drawAccountPieChart() {
            var data = google.visualization.arrayToDataTable([
                ['Account Status', 'Count'],
                ['Active Users', <?php echo intval($activeUserCount); ?>],
                ['Inactive Users', <?php echo intval($inactiveUserCount); ?>]
            ]);
            var options = {
                title: 'User Account Status (Students & Faculty)',
                is3D: false,
                colors: ['#90D1CA', '#dc3545'], // Changed to teal for active users
                pieSliceText: 'percentage',
                legend: { position: 'bottom', alignment: 'center' },
                chartArea: {left:20,top:40,width:'90%',height:'75%'}
            };
            var chart = new google.visualization.PieChart(document.getElementById('userAccountPieChart'));
            if (<?php echo intval($activeUserCount) + intval($inactiveUserCount); ?> > 0) {
                chart.draw(data, options);
            }
            else {
                $('#userAccountPieChart').html('<div class="text-center" style="padding:50px 10px; color:#777;">No account data available to display chart.</div>');
            }
        }

        function drawRoleBarChart() {
            var data = google.visualization.arrayToDataTable([
                ['Role', 'Count', { role: 'style' }],
                ['Students', <?php echo intval($studentTotalCount); ?>, 'color: #90D1CA'], // Changed to teal
                ['Faculty', <?php echo intval($facultyTotalCount); ?>, 'color: #75B5AE']  // Changed to darker teal
            ]);
            var options = {
                title: 'User Distribution by Role',
                legend: { position: "none" },
                hAxis: { title: 'Role',titleTextStyle: {italic: false} },
                vAxis: { title: 'Number of Users', minValue: 0, format: '0', gridlines: { count: -1 }, textStyle:{fontSize: 12} },
                bars: 'vertical',
                bar: { groupWidth: "40%" },
                chartArea: {left:60,top:40,width:'85%',height:'70%'}
            };
            var chart = new google.visualization.BarChart(document.getElementById('userRoleBarChart'));
            if (<?php echo intval($studentTotalCount) + intval($facultyTotalCount); ?> > 0) {
                chart.draw(data, options);
            }
            else {
                $('#userRoleBarChart').html('<div class="text-center" style="padding:50px 10px; color:#777;">No role data available to display chart.</div>');
            }
        }

        // You can add back drawGenderChart, drawVotingStatusChart, drawRegistrationDateChart functions
        // if you add their corresponding <div id="..."> containers back into the HTML.
        // For example:
        /*
        function drawGenderChart() { ... }
        */

        var resizeTimer;
        $(window).resize(function(){ clearTimeout(resizeTimer); resizeTimer = setTimeout(drawAllCharts, 250); });
    }); // End document ready

    function exportChartAsImage(chartId) { /* ... your existing exportChartAsImage function ... */
        const chartElement = document.getElementById(chartId); const chartTitleElement = $(chartElement).closest('.panel').find('.panel-heading'); let chartTitle = chartTitleElement.text().trim().replace(/\s+/g, '_').replace(/[^\w_]/g, '') || chartId;
        if (chartElement && (chartElement.innerHTML.includes('google-visualization') || chartElement.querySelector('svg') || chartElement.querySelector('canvas'))) {
            html2canvas(chartElement, { allowTaint: true, useCORS: true, logging: false })
            .then(canvas => { var link = document.createElement('a'); link.download = chartTitle + '.png'; link.href = canvas.toDataURL('image/png'); document.body.appendChild(link); link.click(); document.body.removeChild(link); })
            .catch(err => { console.error('Error exporting chart:', err); alert('Could not export chart. See console. Error: ' + err.message); });
        } else { alert('Chart is not drawn or data is not available for export.'); }
    }
    </script>
</body>
</html>
