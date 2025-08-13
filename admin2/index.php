<!-- <?php
include('session.php');
include('head.php');

// Initialize messages
$success_message = null;
$error_message = null;

// Check if the backup button was clicked
if (isset($_POST['backup_database'])) {
    // Include the backup script
    $backupResult = include('Backup/backup_db.php');

    // Set session messages based on the backup result
    if ($backupResult && $backupResult['success']) {
        $success_message = $backupResult['message'];
    } else {
        $error_message = $backupResult['message'];
    }
}

// Display messages
if (isset($success_message)) {
    echo '<div class="alert alert-success">' . $success_message . '</div>';
}

if (isset($error_message)) {
    echo '<div class="alert alert-danger">' . $error_message . '</div>';
}
?>

<body>
<div id="wrapper">

    <!-- Navigation -->
    <?php include('side_bar.php'); ?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Candidate List</h3>
            </div>
            <!-- /.col-lg-12 -->
        </div>

        <div class="row">
            <div class="col-lg-12">
                
                <button class="btn btn-success" data-toggle="modal" data-target="#myModal">Add Candidate</button>
				<?php include('add_candidate_modal.php'); ?>
				<!-- Delete All Candidates Button -->
                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal">Delete All Candidates</button>
				<?php include('delete_all_candidate_modal.php'); ?>

                <!-- Backup Button -->
                <form method="post" class="pull-right" style="margin-right: 20px;">
                    <button type="submit" class="btn btn-primary" name="backup_database">Backup Database</button>
                </form>

                <hr/>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="modal-title" id="myModalLabel">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    Candidate List
                                </div>
                            </div>
                        </h4>
                    </div>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Position</th>
                                    <th>Party</th>
                                    <th>Firstname</th>
                                    <th>Lastname</th>
                                    <th>Year Level</th>
                                    <th>Gender</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                require 'dbcon.php'; // It's good to keep this at the top for easier debugging

                                try {
                                    $stmt = $pdo->query("SELECT * FROM candidate ORDER BY candidate_id DESC");

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $candidate_id = $row['candidate_id'];
                                        $position_id = $row['position']; // Get the position_id from the candidate table

                                        // Prepare a statement to fetch the position name based on position_id
                                        $stmt1 = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
                                        $stmt1->execute([$position_id]); // Execute the query with the position_id
                                        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

                                        // Safely get the position name, providing a default if not found
                                        $position_name = ($row1 && isset($row1['position_name'])) ? htmlspecialchars($row1['position_name']) : 'Unknown Position';
                                        ?>
                                        <tr>
                                            <td width="50"><img src="<?php echo htmlspecialchars($row['img']); ?>" width="50" height="50" class="img-rounded"></td>
                                            <td><?php echo $position_name; ?></td>
                                            <td><?php echo htmlspecialchars($row['party']); ?></td>
                                            <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                                            <td><?php echo htmlspecialchars($row['gender']); ?></td>


                                            <td style="text-align:center">

                                                <a rel="tooltip" title="Delete" id="<?php echo $candidate_id; ?>"
                                                   href="#delete_user<?php echo $candidate_id; ?>"
                                                   data-target="#delete_user<?php echo $candidate_id ?>" data-toggle="modal"
                                                   class="btn btn-danger btn-outline"><i class="fa fa-trash-o"></i> Delete</a>
                                                <?php include('delete_candidate_modal.php'); ?>
                                                <a rel="tooltip" title="Edit" id="<?php echo htmlspecialchars($row['candidate_id']) ?>"
                                                   href="#edit_candidate<?php echo htmlspecialchars($row['candidate_id']) ?>" data-toggle="modal"
                                                   class="btn btn-success btn-outline"><i class="fa fa-pencil"></i> Edit</a>

                                            </td>

                                            <?php
                                            require 'edit_candidate_modal.php';
                                            ?>
                                        </tr>

                                    <?php }
                                } catch (PDOException $e) {
                                    echo "Database Error: " . htmlspecialchars($e->getMessage());
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.table-responsive -->

                    </div>
                    <!-- /.panel-body -->
                </div>
                <!-- /.panel -->

            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <?php include('script.php'); ?>

</body>

</html> -->