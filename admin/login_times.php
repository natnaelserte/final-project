<?php
include('session.php'); // Ensures user is logged in and session is active
include('head.php');    // Includes common head elements, CSS, etc.

require 'dbcon.php'; // Database connection

// --- Date Range Filtering Logic ---
$default_days_range = 7;
$date_from_input = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_SPECIAL_CHARS);
$date_to_input = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_SPECIAL_CHARS);

$date_from = ($date_from_input && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date_from_input))
             ? $date_from_input
             : date('Y-m-d', strtotime("-$default_days_range days"));
$date_to = ($date_to_input && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date_to_input))
           ? $date_to_input
           : date('Y-m-d');

// --- User & Role Filtering Logic ---
$filter_username_or_name = isset($_GET['filter_user']) ? trim(filter_input(INPUT_GET, 'filter_user', FILTER_SANITIZE_SPECIAL_CHARS)) : '';
$filter_role_id_input = filter_input(INPUT_GET, 'filter_role_id', FILTER_VALIDATE_INT);
$filter_role_id = ($filter_role_id_input !== false && $filter_role_id_input !== null) ? $filter_role_id_input : '';

// --- Prepare SQL conditions and parameters for login log table ---
$log_conditions = ["l.login_time BETWEEN :date_from_start AND :date_to_end"];
$log_params = [
    ':date_from_start' => $date_from . ' 00:00:00',
    ':date_to_end' => $date_to . ' 23:59:59'
];

if (!empty($filter_username_or_name)) {
    $log_conditions[] = "(u.username LIKE :user_search OR u.firstname LIKE :user_search OR u.lastname LIKE :user_search)";
    $log_params[':user_search'] = "%" . $filter_username_or_name . "%";
}
if ($filter_role_id !== '') {
    $log_conditions[] = "u.role_id = :role_id";
    $log_params[':role_id'] = $filter_role_id;
}

$log_where_clause = "";
if (!empty($log_conditions)) {
    $log_where_clause = "WHERE " . implode(" AND ", $log_conditions);
}

// --- Data for Charts (will be fetched based on date range) ---
$timeSegments = ['Morning (6-12)' => 0, 'Afternoon (12-17)' => 0, 'Evening (17-21)' => 0, 'Night (21-6)' => 0];
$hourlyLogins = array_fill(0, 24, 0);
$dailyLogins = [];

try {
    $current_date_obj = new DateTime($date_from);
    $end_date_obj = new DateTime($date_to);
    while ($current_date_obj <= $end_date_obj) {
        $dailyLogins[$current_date_obj->format('Y-m-d')] = 0;
        $current_date_obj->modify('+1 day');
    }
} catch (Exception $e) {
    error_log("Date parsing error for charts: " . $e->getMessage());
    $date_from = date('Y-m-d', strtotime("-7 days")); // Fallback
    $date_to = date('Y-m-d');                      // Fallback
    $current_date_obj = new DateTime($date_from);
    $end_date_obj = new DateTime($date_to);
     while ($current_date_obj <= $end_date_obj) {
        $dailyLogins[$current_date_obj->format('Y-m-d')] = 0;
        $current_date_obj->modify('+1 day');
    }
}

try {
    $chart_query_params = [
        ':date_from_start' => $date_from . ' 00:00:00',
        ':date_to_end' => $date_to . ' 23:59:59'
    ];
    $chart_stmt = $pdo->prepare("SELECT l.login_time FROM login l WHERE l.login_time BETWEEN :date_from_start AND :date_to_end");
    $chart_stmt->execute($chart_query_params);

    while ($row = $chart_stmt->fetch(PDO::FETCH_ASSOC)) {
        $login_timestamp = strtotime($row['login_time']);
        $hour = (int)date('G', $login_timestamp);
        $day_date = date('Y-m-d', $login_timestamp);

        $hourlyLogins[$hour]++;
        if (array_key_exists($day_date, $dailyLogins)) {
            $dailyLogins[$day_date]++;
        }

        if ($hour >= 6 && $hour < 12) $timeSegments['Morning (6-12)']++;
        elseif ($hour >= 12 && $hour < 17) $timeSegments['Afternoon (12-17)']++;
        elseif ($hour >= 17 && $hour < 21) $timeSegments['Evening (17-21)']++;
        else $timeSegments['Night (21-6)']++;
    }
} catch (PDOException $e) {
    $chart_error = "Error fetching chart data: " . htmlspecialchars($e->getMessage());
    error_log($chart_error);
}

// Fetch roles for filter dropdown
$roles_for_filter = [];
try {
    $role_stmt = $pdo->query("SELECT role_id, role_name FROM role_table ORDER BY role_name");
    $roles_for_filter = $role_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching roles from role_table: " . $e->getMessage());
    $role_filter_error = "Could not load role filter options.";
}
?>

<!DOCTYPE html> <!-- This might be in head.php. If so, remove it here. -->
<html lang="en"> <!-- This might be in head.php. If so, remove it here. -->
<head>
    <!-- Meta tags, main CSS for admin template are usually in head.php -->
    <title>System Usage Time Tracker</title>
    <!-- DataTables CSS (adjust path if using local files in 'plugin' folder) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <!-- Your other CSS from head.php -->
    <style>
        /* Define primary color variables */
        :root {
            --primary-color: #90D1CA;
            --primary-dark: #75B5AE;
            --primary-light: #A8DCD6;
            --primary-very-light: #E5F4F2;
            --text-on-primary: #333333;
        }

        /* Modern form styling */
        .modern-filter-panel {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .modern-filter-panel .panel-heading {
            background: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }

        .modern-filter-panel .panel-body {
            padding: 25px;
            border-radius: 0 0 12px 12px;
        }

        /* Modern form controls */
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(144, 209, 202, 0.25);
            outline: none;
        }

        .input-group-addon {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
            color: var(--text-on-primary);
            border-radius: 8px 0 0 8px;
            font-weight: 500;
        }

        .input-group .form-control:first-child {
            border-radius: 0 8px 8px 0;
        }

        /* Modern buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--text-on-primary);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-default {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }

        .btn-default:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Modern table panel */
        .modern-table-panel {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .modern-table-panel .panel-heading {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
            color: white !important;
            border: none !important;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 16px;
        }

        .modern-table-panel .panel-body {
            padding: 0;
            background: white;
        }

        /* Modern table styling */
        #loginLogTable {
            margin-bottom: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        #loginLogTable thead th {
            background: var(--primary-light) !important;
            color: var(--text-on-primary) !important;
            border: none !important;
            padding: 15px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        #loginLogTable tbody td {
            padding: 12px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        #loginLogTable tbody tr:hover {
            background-color: var(--primary-very-light) !important;
        }

        /* Modern chart panels */
        .modern-chart-panel {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .modern-chart-panel .panel-heading {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            padding: 15px 20px;
            border-bottom: 3px solid var(--primary-color);
        }

        .modern-chart-panel .panel-title {
            color: #495057;
            font-weight: 600;
            margin: 0;
        }

        .modern-chart-panel .panel-body {
            padding: 20px;
            background: white;
        }

        /* Page header styling */
        .page-header {
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: #495057;
            font-weight: 300;
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
            .form-inline .form-group {
                margin-bottom: 15px;
                width: 100%;
            }

            .form-inline .form-control {
                width: 100%;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Minor style adjustments */
        .form-inline .form-group { margin-bottom: 15px; }
        #loginLogTable_wrapper .row:first-child > div { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include('side_bar.php'); ?>

        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">System Usage Time Tracker</h1>
                    </div>
                </div>

                <!-- Filtering Form -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel modern-filter-panel">
                            <div class="panel-heading">
                                <i class="fa fa-filter fa-fw"></i> Advanced Filters
                            </div>
                            <div class="panel-body">
                                <form method="GET" action="login_times.php" class="form-inline">
                                    <div class="form-group" style="margin-right: 10px;">
                                        <label for="date_from" class="sr-only">From:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">From</div>
                                            <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>" max="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-right: 10px;">
                                        <label for="date_to" class="sr-only">To:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">To</div>
                                            <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>" max="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-right: 10px;">
                                        <label for="filter_user" class="sr-only">User:</label>
                                        <input type="text" id="filter_user" name="filter_user" class="form-control" placeholder="Username or Name" value="<?php echo htmlspecialchars($filter_username_or_name); ?>">
                                    </div>
                                    <?php if (!empty($roles_for_filter)): ?>
                                    <div class="form-group" style="margin-right: 10px;">
                                        <label for="filter_role_id" class="sr-only">Role:</label>
                                        <select id="filter_role_id" name="filter_role_id" class="form-control">
                                            <option value="">All Roles</option>
                                            <?php foreach ($roles_for_filter as $role_item): ?>
                                                <option value="<?php echo htmlspecialchars($role_item['role_id']); ?>" <?php echo ($filter_role_id !== '' && $filter_role_id == $role_item['role_id'] ? 'selected' : ''); ?>>
                                                    <?php echo htmlspecialchars($role_item['role_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php elseif(isset($role_filter_error)): ?>
                                        <div class="form-group" style="margin-right: 10px;">
                                            <p class="text-danger"><?php echo htmlspecialchars($role_filter_error); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Apply</button>
                                    <a href="login_times.php" class="btn btn-default"><i class="fa fa-refresh"></i> Clear</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel modern-table-panel">
                            <div class="panel-heading">
                                <i class="fa fa-users fa-fw"></i> System User Login Log
                                <small style="opacity: 0.9; margin-left: 10px;">(<?php echo date("M d, Y", strtotime($date_from)) . " - " . date("M d, Y", strtotime($date_to)); ?>)</small>
                                <a href="export_login_log.php?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&filter_user=<?php echo urlencode($filter_username_or_name); ?>&filter_role_id=<?php echo urlencode($filter_role_id); ?>" class="btn btn-success btn-xs pull-right" style="margin-top: -3px; border-radius: 6px;"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                            </div>
                            <div class="panel-body">
                                <!-- Table is now fully client-side managed by DataTables if many rows -->
                                <table class="table table-striped table-bordered table-hover" id="loginLogTable" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Login Timestamp</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $log_sql = "SELECT l.login_time, l.ip_address,
                                                               u.firstname, u.lastname, u.username as u_username, u.role_id as user_role_id,
                                                               rt.role_name
                                                        FROM login l
                                                        LEFT JOIN users u ON l.user_id = u.user_id
                                                        LEFT JOIN role_table rt ON u.role_id = rt.role_id
                                                        $log_where_clause
                                                        ORDER BY l.login_time DESC";
                                                        // Removed LIMIT - DataTables handles pagination on client side
                                                        // For very large datasets, server-side processing for DataTables is recommended.
                                            $log_stmt = $pdo->prepare($log_sql);
                                            $log_stmt->execute($log_params);

                                            if ($log_stmt->rowCount() > 0) {
                                                while ($row = $log_stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $fullName = htmlspecialchars(trim($row['firstname'] . " " . $row['lastname']));
                                                    $username_display = htmlspecialchars($row['u_username'] ?? $row['username'] ?? 'N/A');
                                                    $roleName_display = htmlspecialchars($row['role_name'] ?? ($row['user_role_id'] ? 'Role ID: '.$row['user_role_id'] : 'N/A'));
                                                    $login_time_formatted = htmlspecialchars(date("Y-m-d H:i:s", strtotime($row['login_time'])));
                                                    $ip_address_display = htmlspecialchars($row['ip_address'] ?? 'N/A'); // Assumes 'ip_address' column in 'login' table
                                            ?>
                                                    <tr>
                                                        <td><?php echo $fullName ?: ($username_display ?: 'N/A'); ?></td>
                                                        <td><?php echo $username_display; ?></td>
                                                        <td><?php echo $roleName_display; ?></td>
                                                        <td><?php echo $login_time_formatted; ?></td>
                                                        <td><?php echo $ip_address_display; ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                // This row won't be visible if DataTables is active and there are no rows,
                                                // DataTables will show its own "No data available in table" message.
                                                // echo "<tr><td colspan='5' class='text-center'>No login records found for the selected criteria.</td></tr>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<tr><td colspan='5' class='text-center text-danger'>Error fetching log: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                            error_log("Error fetching login log table: " . $e->getMessage());
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-pie-chart fa-fw"></i> Login Time Distribution</h3></div>
                            <div class="panel-body"><canvas id="loginPieChart" style="max-height: 300px;"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="panel modern-chart-panel">
                             <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-bar-chart fa-fw"></i> Hourly Login Activity <small style="opacity: 0.7;">(Selected Range)</small></h3></div>
                           <div class="panel-body"><canvas id="loginLineChart" style="max-height: 300px;"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel modern-chart-panel">
                            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-line-chart fa-fw"></i> Daily Login Trends <small style="opacity: 0.7;">(Selected Range)</small></h3></div>
                            <div class="panel-body"><canvas id="dailyLoginChart" style="max-height: 300px;"></canvas></div>
                        </div>
                    </div>
                </div>

            </div> <!-- /.container-fluid -->
        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <?php include('script.php'); // Common scripts like jQuery, Bootstrap JS ?>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables JS CDN (after jQuery) -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTables for the login log table
        // Ensure jQuery is loaded before this script block executes (e.g., in script.php or above these DataTables CDN links)
        if (typeof $ !== 'undefined' && $('#loginLogTable').length) {
             $('#loginLogTable').DataTable({
                "responsive": true,
                "order": [[ 3, "desc" ]], // Default sort: 4th column (Login Timestamp) descending
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                // You can customize language options here if needed
            });
        } else {
            console.warn("jQuery or #loginLogTable not found. DataTables not initialized.");
        }


        const pieChartColors = ['#90D1CA', '#75B5AE', '#A8DCD6', '#E5F4F2', '#FFA87D', '#FFD166'];

        function renderChartOrMessage(ctx, chartConfig, dataValues) {
            // Ensure dataValues is an array of numbers
            const numericDataValues = Array.isArray(dataValues) ? dataValues.map(Number).filter(n => !isNaN(n)) : [];

            if (numericDataValues.reduce((a, b) => a + b, 0) > 0) { // Sum of data must be > 0
                new Chart(ctx, chartConfig);
            } else {
                ctx.font = "16px Arial";
                ctx.textAlign = "center";
                ctx.fillStyle = "#777";
                ctx.fillText("No data available for this period", ctx.canvas.width / 2, ctx.canvas.height / 2);
            }
        }

        const pieCtx = document.getElementById('loginPieChart')?.getContext('2d');
        if (pieCtx) {
            const pieDataValues = <?php echo json_encode(array_values($timeSegments)); ?>;
            const pieConfig = {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($timeSegments)); ?>,
                    datasets: [{ data: pieDataValues, backgroundColor: pieChartColors, borderWidth: 1, borderColor: '#fff' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            };
            renderChartOrMessage(pieCtx, pieConfig, pieDataValues);
        }

        const lineCtx = document.getElementById('loginLineChart')?.getContext('2d');
        if (lineCtx) {
            const hourlyDataValues = [<?php echo implode(',', $hourlyLogins); ?>];
            const lineConfig = {
                type: 'bar',
                data: {
                    labels: [<?php for ($i = 0; $i < 24; $i++) echo "'".str_pad($i, 2, '0', STR_PAD_LEFT).":00',"; ?>],
                    datasets: [{
                        label: 'Logins', data: hourlyDataValues,
                        backgroundColor: 'rgba(144, 209, 202, 0.6)', borderColor: '#90D1CA', borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Logins' } }, x: { title: { display: true, text: 'Hour of Day' } } } }
            };
            renderChartOrMessage(lineCtx, lineConfig, hourlyDataValues);
        }

        const dailyCtx = document.getElementById('dailyLoginChart')?.getContext('2d');
        if (dailyCtx) {
            const dailyDataValues = <?php echo json_encode(array_values($dailyLogins)); ?>;
            const dailyConfig = {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_keys($dailyLogins)); ?>,
                    datasets: [{
                        label: 'Total Logins', data: dailyDataValues,
                        borderColor: '#75B5AE', backgroundColor: 'rgba(144, 209, 202, 0.2)', fill: true, tension: 0.1, borderWidth: 3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Logins' } }, x: { title: { display: true, text: 'Date' } } } }
            };
            renderChartOrMessage(dailyCtx, dailyConfig, dailyDataValues);
        }

        <?php if (isset($chart_error)): ?>
        console.error("Chart Data Error: <?php echo addslashes(htmlspecialchars($chart_error)); ?>");
        <?php endif; ?>
    });
    </script>
</body>
</html>