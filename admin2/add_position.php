<?php 
include('session.php'); // Assuming you have session management
include('head.php');    // Include head.php for CSS and other header elements
?>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>  <!-- Include the sidebar -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Manage Election Positions</h3>
            </div>
        </div>

        <!-- Success or Error Alert Messages -->
        <?php
      
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success alert-dismissible' role='alert'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    " . $_SESSION['success_message'] . "
                  </div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger alert-dismissible' role='alert'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    " . $_SESSION['error_message'] . "
                  </div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add New Position
                    </div>
                    <div class="panel-body">
                        <form method="post">
                            <div class="form-group">
                                <label>Position Name</label>
                                <input type="text" class="form-control" name="position_name" required>
                            </div>
                            <button type="submit" name="add_position" class="btn btn-primary">Add Position</button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAllPositionsModal">Delete All Positions</button>
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
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Existing Positions and Registered Candidates
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
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
                                        echo "<td>" . htmlspecialchars($position_row['position_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($candidate_count) . "</td>";
                                        echo "<td>
                                                <a href='edit_position.php?id=" . htmlspecialchars($position_id) . "' class='btn btn-success btn-xs'>Edit</a>
                                                <a href='delete_position.php?id=" . htmlspecialchars($position_id) . "' 
                                                   class='btn btn-danger btn-xs' 
                                                   onclick=\"return confirm('Are you sure you want to delete this position?');\">
                                                   Delete</a>
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
