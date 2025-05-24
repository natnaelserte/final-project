<?php
require_once 'dbcon.php'; // Ensure this is at the very top if not already included by a parent script

if (isset($_POST['save'])) { // Assuming 'save' is the name of your submit button in add_candidate_modal.php
    // Retrieve form data
    $position_name_from_form = $_POST['position']; // Get position name from the form
    $slogan_from_form = trim($_POST['slogan']); // Get slogan from the form and trim whitespace
    $firstname_from_form = trim($_POST['firstname']); // Trim whitespace
    $lastname_from_form = trim($_POST['lastname']);   // Trim whitespace
    $year_level_from_form = $_POST['year_level'];
    $gender_from_form = $_POST['gender'];

    // Basic Validation (server-side)
    if (empty($position_name_from_form) || empty($slogan_from_form) || empty($firstname_from_form) || empty($lastname_from_form) || empty($year_level_from_form) || empty($gender_from_form) || !isset($_FILES["image"]) || $_FILES["image"]["error"] == UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('All fields, including an image, are required.'); window.location='candidate.php';</script>"; // Or your main page name
        exit;
    }


    // Image Upload Handling
    $target_dir = "upload/";  // Ensure this directory exists and is writable
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            echo "<script>alert('Failed to create upload directory. Check server permissions.'); window.location='candidate.php';</script>";
            exit;
        }
    }
    $image_name = basename($_FILES["image"]["name"]);
    $sanitized_image_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_name); // Sanitize filename
    $target_file = $target_dir . uniqid() . "_" . $sanitized_image_name; // Unique filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = @getimagesize($_FILES["image"]["tmp_name"]); // Suppress warning if not an image
    if ($check === false) {
        echo "<script>alert('File is not a valid image.'); window.location='candidate.php';</script>";
        $uploadOk = 0;
    }

    // Check file size (example: limit to 2MB)
    if ($uploadOk && $_FILES["image"]["size"] > 2000000) {
        echo "<script>alert('Sorry, your file is too large (max 2MB).'); window.location='candidate.php';</script>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_extensions = ["jpg", "png", "jpeg", "gif"];
    if ($uploadOk && !in_array($imageFileType, $allowed_extensions)) {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.'); window.location='candidate.php';</script>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        // Error message already shown by specific checks above
        // echo "<script>alert('Sorry, your file was not uploaded due to previous errors.'); window.location='candidate.php';</script>";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            try {
                // Get the position_id based on the selected position_name using PDO
                $position_query = $pdo->prepare("SELECT position_id FROM position WHERE position_name = ?");
                $position_query->execute([$position_name_from_form]);
                $position_row = $position_query->fetch(PDO::FETCH_ASSOC);

                if ($position_row) {
                    $position_id = $position_row['position_id'];
                } else {
                    // Handle the case where the position_name is not found
                    echo "<script>alert('Error: Selected position was not found in the database.'); window.location='candidate.php';</script>";
                    // Optionally delete the uploaded file if position is not found
                    if (file_exists($target_file)) { @unlink($target_file); }
                    exit;
                }

                // Use prepared statements to prevent SQL injection with PDO
                // THIS IS THE CORRECTED LINE:
                $stmt = $pdo->prepare("INSERT INTO candidate(position, slogan, firstname, lastname, year_level, gender, img) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$position_id, $slogan_from_form, $firstname_from_form, $lastname_from_form, $year_level_from_form, $gender_from_form, $target_file]);

                echo "<script>alert('Candidate Added Successfully'); window.location='candidate.php';</script>"; // Or your main page name

            } catch (PDOException $e) {
                // More user-friendly error, log the detailed one
                error_log("Add Candidate DB Error: " . $e->getMessage());
                echo "<script>alert('Database error occurred while adding candidate. Please try again.'); window.location='candidate.php';</script>";
                // Optionally delete the uploaded file if DB insert fails
                if (file_exists($target_file)) { @unlink($target_file); }
            }

        } else {
            echo "<script>alert('Sorry, there was an error moving the uploaded file. Check server permissions for the upload/ directory.'); window.location='candidate.php';</script>";
        }
    }
}
?>

<!-- Modal HTML (this seems to be from add_candidate_modal.php) -->
<!-- The HTML form part of your add_candidate_modal.php already correctly uses name="slogan" for the slogan input, which is good. -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">Add Candidate</h4>
            </div>
            <div class="modal-body">
                <!-- The action should ideally point to the script that contains the PHP processing logic.
                     If this PHP code is at the top of 'candidate.php', then action="candidate.php" is okay.
                     If this PHP code is in a separate file like 'process_add_candidate.php', change the action.
                -->
                <form method="post" action="candidate.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Position</label>
                        <select class="form-control" name="position" required>
                            <option value="" selected disabled>Select Candidate Group</option> <!-- Added value="" for disabled option -->
                            <?php
                            // Re-establishing dbcon.php if this modal is in a separate file and not included after dbcon.php is set up in parent.
                            // However, it's better if $pdo is passed or available from the parent.
                            // For this standalone example, we'll assume it's fine.
                            // require_once 'dbcon.php'; // Already required at the top of the PHP block
                            try {
                                $position_query_modal = $pdo->query("SELECT * FROM position ORDER BY position_name ASC");
                                while ($position_row_modal = $position_query_modal->fetch(PDO::FETCH_ASSOC)) {
                                    // Using position_name as value, which is then looked up.
                                    echo "<option value='" . htmlspecialchars($position_row_modal['position_name'], ENT_QUOTES) . "'>" . htmlspecialchars($position_row_modal['position_name'], ENT_QUOTES) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading positions</option>";
                                error_log("Error loading positions in add modal: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Slogan</label>
                        <input class="form-control" name="slogan" type="text" required> <!-- Correct: name="slogan" -->
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
                <button type="submit" name="save" class="btn btn-primary">Save</button> <!-- This button triggers the PHP if (isset($_POST['save'])) -->
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->