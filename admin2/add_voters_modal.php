<div class="modal fade" id="add_voters" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <center>Add Candidate</center>
                        </div>
                    </div>
                </h4>
            </div>

            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>ID Number</label>
                        <input class="form-control" type="text" name="id_number" placeholder="ID number" required="true">
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
                        <label>Year Level</label>
                        <select class="form-control" name="year_level" required>
                            <option value="">Select Year Level</option>
                            <option>1st Year</option>
                            <option>2nd Year</option>
                            <option>3rd Year</option>
                            <option>4th Year</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select class="form-control" name="gender" required>
                            <option value="">Select Gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" required>
                    </div>

                    <button name="save" type="submit" class="btn btn-primary">Save Data</button>
                    <button name="save" type="reset" class="btn btn-success">Cancel All</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

            <?php
            include('dbcon.php'); // Ensure $pdo is initialized in this file

            if (isset($_POST['save'])) {
                $id_number = $_POST['id_number'];
                $password = $_POST['password'];
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];
                $year_level = $_POST['year_level'];
                $gender = $_POST['gender'];

                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_tmp = $_FILES['image']['tmp_name'];
                    $image_name = $_FILES['image']['name'];
                    $image_size = $_FILES['image']['size'];
                    $image_type = mime_content_type($image_tmp);

                    // Validate image type
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($image_type, $allowed_types)) {
                        echo "<script>alert('Invalid image type. Only JPG, PNG, and GIF are allowed.');</script>";
                        exit;
                    }

                    // Move uploaded file
                    $upload_dir = "upload/";
                    $location = $upload_dir . basename($image_name);
                    if (!move_uploaded_file($image_tmp, $location)) {
                        echo "<script>alert('Failed to upload image.');</script>";
                        exit;
                    }

                    try {
                        // Insert data into the database
                        $query = $pdo->prepare("INSERT INTO candidate (id_number, password, firstname, lastname, year_level, gender, img) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $query->execute([$id_number, $password, $firstname, $lastname, $year_level, $gender, $location]);

                        echo "<script>
                                alert('Candidate successfully added.');
                                window.location='candidates.php';
                              </script>";
                    } catch (PDOException $e) {
                        echo "<script>alert('Database Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
                    }
                } else {
                    echo "<script>alert('Please upload a valid image.');</script>";
                }
            }
            ?>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>