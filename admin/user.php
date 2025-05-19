<!-- <?php include('session.php'); ?> -->
<?php include('head.php'); ?>
<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">System User List</h3>
                </div>

                <a href="add_user_id.php" class="btn btn-success ">
                        <i class="glyphicon glyphicon-save"></i> Add staff
                    </a>
                    <a href="add_student.php" class="btn btn-success ">
                        <i class="glyphicon glyphicon-save"></i> Add student
                    </a>
                <!-- /.col-lg-12 -->

                <hr />

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="modal-title" id="myModalLabel">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    System User List
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
                                        <th>Username</th>
                                        <th>Firstname</th>
                                        <th>Lastname</th>
                                        <th>Contact</th>
                                        <th>Gender</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require 'dbcon.php';

                                    try {
                                        // Fetch users securely using PDO
                                        $query = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                            $user_id = htmlspecialchars($row['user_id']);
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                <td style="text-align:center">
                                                    <a rel="tooltip" title="Delete" id="<?php echo $user_id; ?>" href="#delete_admin<?php echo $user_id; ?>" data-target="#delete_admin<?php echo $user_id; ?>" data-toggle="modal" class="btn btn-danger btn-outline">
                                                        <i class="fa fa-trash-o"></i> Delete
                                                    </a>
                                                    <?php include('delete_user_modal.php'); ?>
                                                    <a rel="tooltip" title="Edit" id="<?php echo $user_id; ?>" href="#edit_user<?php echo $user_id; ?>" data-toggle="modal" class="btn btn-success btn-outline">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                    <?php include('edit_user_modal.php'); ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='6'>Error fetching users: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
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

</html>

