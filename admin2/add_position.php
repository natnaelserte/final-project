<?php
include('session.php'); // Assuming you have session management
include('head.php');    // Include head.php for CSS and other header elements
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Position Management - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>  <!-- Include the sidebar -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="modern-page-header">
                    <h1><i class="fa fa-sitemap"></i> Manage Election Positions</h1>
                </div>
            </div>
        </div>

        <!-- Success or Error Alert Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success alert-dismissible' role='alert'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-check-circle'></i> " . $_SESSION['success_message'] . "
                  </div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger alert-dismissible' role='alert'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-exclamation-circle'></i> " . $_SESSION['error_message'] . "
                  </div>";
            unset($_SESSION['error_message']);
        }

        // Get statistics
        require 'dbcon.php';
        $total_positions = 0;
        $total_candidates = 0;
        try {
            $stats_query = $pdo->query("SELECT COUNT(*) as position_count FROM position");
            $total_positions = $stats_query->fetch(PDO::FETCH_ASSOC)['position_count'];

            $candidates_query = $pdo->query("SELECT COUNT(*) as candidate_count FROM candidate");
            $total_candidates = $candidates_query->fetch(PDO::FETCH_ASSOC)['candidate_count'];
        } catch (PDOException $e) {
            // Handle error silently for stats
        }
        ?>

        <!-- Statistics Cards -->
        <div class="row" style="margin-bottom: 25px;">
            <div class="col-lg-6 col-md-6">
                <div class="panel modern-stat-card primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-sitemap fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $total_positions; ?></div>
                                <div>Total Positions</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="panel modern-stat-card success">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $total_candidates; ?></div>
                                <div>Total Candidates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="panel modern-filter-panel">
                    <div class="panel-heading">
                        <i class="fa fa-plus-circle"></i> Add New Position
                    </div>
                    <div class="panel-body">
                        <form method="post">
                            <div class="form-group">
                                <label><i class="fa fa-tag"></i> Position Name</label>
                                <input type="text" class="form-control" name="position_name" placeholder="Enter position name..." required>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_position" class="btn btn-success">
                                    <i class="fa fa-plus"></i> Add Position
                                </button>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAllPositionsModal">
                                    <i class="fa fa-trash"></i> Delete All Positions
                                </button>
                            </div>
                        </form>
                        <?php include('delete_all_positions_modal.php'); ?>

                        <?php
                        require 'dbcon.php';

                        if (isset($_POST['add_position'])) {
                            $position_name = $_POST['position_name'];

                            try {
                                $stmt = $pdo->prepare("INSERT INTO position (position_name) VALUES (?)");
                                $stmt->execute([$position_name]);
                                echo "<div class='alert alert-success'>Position added successfully!</div>";
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>Error adding position: " . htmlspecialchars($e->getMessage()) . "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel modern-table-panel">
                    <div class="panel-heading">
                        <i class="fa fa-list"></i> Existing Positions & Candidates
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover modern-table">
                                <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Registered Candidates</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                require 'dbcon.php';
                                try {
                                    $position_query = $pdo->query("SELECT * FROM position ORDER BY position_id ASC");
                                    while ($position_row = $position_query->fetch(PDO::FETCH_ASSOC)) {
                                        $position_id = $position_row['position_id'];

                                        $candidate_count_query = $pdo->prepare("SELECT COUNT(*) AS count FROM candidate WHERE position = ?");
                                        $candidate_count_query->execute([$position_id]);
                                        $candidate_count_row = $candidate_count_query->fetch(PDO::FETCH_ASSOC);
                                        $candidate_count = $candidate_count_row['count'];

                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($position_row['position_name']) . "</strong></td>";
                                        echo "<td><span class='badge badge-info'>" . htmlspecialchars($candidate_count) . "</span></td>";
                                        echo "<td style='text-align: center; white-space: nowrap;'>
                                                <a href='edit_position.php?id=" . htmlspecialchars($position_id) . "'
                                                   class='btn btn-success btn-xs'
                                                   title='Edit Position'>
                                                   <i class='fa fa-pencil'></i>
                                                </a>
                                                <a href='delete_position.php?id=" . htmlspecialchars($position_id) . "'
                                                   class='btn btn-danger btn-xs'
                                                   title='Delete Position'
                                                   onclick=\"return confirm('Are you sure you want to delete this position? This action cannot be undone.');\">
                                                   <i class='fa fa-trash'></i>
                                                </a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='3'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
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

<?php include('script.php'); ?>  <!-- Include scripts -->
</body>
</html>
