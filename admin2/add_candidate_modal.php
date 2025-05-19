<?php
require_once 'dbcon.php';

if (isset($_POST['save'])) {
    // Retrieve form data
    $position_name = $_POST['position']; // Get position name from the form
    $party = $_POST['party'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $year_level = $_POST['year_level'];
    $gender = $_POST['gender'];

    // Image Upload Handling
    $target_dir = "upload/";  // Create this directory and make it writable
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $image_name; // Unique filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File is not an image.'); window.location='candidate.php'</script>";
        $uploadOk = 0;
    }

    // Check file size (example: limit to 2MB)
    if ($_FILES["image"]["size"] > 2000000) {
        echo "<script>alert('Sorry, your file is too large.'); window.location='candidate.php'</script>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif") {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.'); window.location='candidate.php'</script>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "<script>alert('Sorry, your file was not uploaded.'); window.location='candidate.php'</script>";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            try {
                // Get the position_id based on the selected position_name using PDO
                $position_query = $pdo->prepare("SELECT position_id FROM position WHERE position_name = ?");
                $position_query->execute([$position_name]);
                $position_row = $position_query->fetch(PDO::FETCH_ASSOC);

                if ($position_row) {
                    $position_id = $position_row['position_id']; // This is the correct position_id
                } else {
                    // Handle the case where the position_name is not found
                    echo "<script>alert('Error: Position not found.'); window.location='candidate.php'</script>";
                    exit; // Stop execution
                }

                // Use prepared statements to prevent SQL injection with PDO
                $stmt = $pdo->prepare("INSERT INTO candidate(position, party, firstname, lastname, year_level, gender, img) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$position_id, $party, $firstname, $lastname, $year_level, $gender, $target_file]);  // Store the position_id

                echo "<script>alert('Candidate Added Successfully'); window.location='candidate.php'</script>";

            } catch (PDOException $e) {
                echo "Error: " . htmlspecialchars($e->getMessage()); // Display the error message
            }

        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.'); window.location='candidate.php'</script>";
        }
    }
}
?>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Add Candidate</h4>
            </div>
            <div class="modal-body">
                <form method="post" action="candidate.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Position</label>
                        <select class="form-control" name="position" required>
                            <option selected disabled>Select Candidate Group</option>
                            <?php
                            require_once 'dbcon.php';  //Make sure this path is correct
                            try {
                                $position_query = $pdo->query("SELECT * FROM position ORDER BY position_name ASC");
                                while ($position_row = $position_query->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($position_row['position_name'], ENT_QUOTES) . "'>" . htmlspecialchars($position_row['position_name'], ENT_QUOTES) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "Error: " . htmlspecialchars($e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Party</label>
                        <input class="form-control" name="party" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Firstname</label>
                        <input class="form-control" name="firstname" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Lastname</label>
                        <input class="form-control" name="lastname" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select class="form-control" name="year_level" required>
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select class="form-control" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" name="save" class="btn btn-primary">Save</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->