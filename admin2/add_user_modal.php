<div class="modal fade" id="add_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <center>Assign System User</center>
                        </div>
                    </div>
                </h4>
            </div>

            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" name="username" placeholder="Username" required="true">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="text" name="password" placeholder="Password" required="true">
                    </div>
                    <div class="form-group">
                        <label>Firstname</label>
                        <input class="form-control" type="text" name="firstname" placeholder="Firstname" required="true">
                    </div>
                    <div class="form-group">
                        <label>Lastname</label>
                        <input class="form-control" type="text" name="lastname" placeholder="Please enter lastname" required="true">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input class="form-control" type="number" name="Phone" placeholder="Please enter contact number" required="true">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" type="email" name="email" placeholder="Please enter email address">
                    </div>

                    <button name="ok" type="submit" class="btn btn-primary">Save Data</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

            <?php
            require_once 'dbcon.php'; // Ensure $pdo is initialized in this file
            if (isset($_POST['ok'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];
                $Phone = $_POST['Phone'];
                $email = $_POST['email'];

                try {
                    // Check if the username already exists
                    $query = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $query->execute([$username]);
                    $count1 = $query->fetchColumn();

                    if ($count1 > 0) {
                        echo "<script>alert('User Already Exists');</script>";
                    } else {
                        // Insert the new user
                        $insert_query = $pdo->prepare("INSERT INTO users (username, password, firstname, lastname, Phone, email) VALUES (?, ?, ?, ?, ?, ?)");
                        $insert_query->execute([$username, $password, $firstname, $lastname, $Phone, $email]);

                        echo "<script>
                                alert('User Data Successfully Saved');
                                window.location='user.php';
                              </script>";
                    }
                } catch (PDOException $e) {
                    echo "<script>alert('Database Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
                }
            }
            ?>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>