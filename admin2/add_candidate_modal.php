<?php
// Start session AT THE VERY TOP.
if (session_status() == PHP_SESSION_NONE) { // Check if session isn't already started
    session_start();
}

// Start output buffering IMMEDIATELY AFTER session_start.
ob_start();
$profile_img_upload_dir = "upload/";
$primary_evidence_storage_dir = "candidate_detail/";

if (isset($_POST['save'])) {
    // ... [YOUR EXISTING PHP CODE FROM LINE 7 to line 167 remains largely the same] ...
    // Retrieve form data
    $position_name_from_form = $_POST['position'] ?? '';
    $slogan_from_form = trim($_POST['slogan'] ?? '');
    $firstname_from_form = trim($_POST['firstname'] ?? '');
    $lastname_from_form = trim($_POST['lastname'] ?? '');
    $candidate_type_from_form = $_POST['candidate_type'] ?? '';
    $gender_from_form = $_POST['gender'] ?? '';

    $profile_image_path_for_db = null;
    $primary_evidence_path_for_db = null;

    $profile_image_selected = isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE && !empty($_FILES["image"]["name"]);
    $error_messages = [];

    if (empty($position_name_from_form)) $error_messages[] = 'Position is required.';
    if (empty($slogan_from_form)) $error_messages[] = 'Slogan is required.';
    if (empty($firstname_from_form)) $error_messages[] = 'Firstname is required.';
    if (empty($lastname_from_form)) $error_messages[] = 'Lastname is required.';
    if (empty($candidate_type_from_form)) $error_messages[] = 'Candidate Type is required.';
    if (empty($gender_from_form)) $error_messages[] = 'Gender is required.';
    if (!$profile_image_selected) $error_messages[] = 'Profile image is required.';

    if (!empty($candidate_type_from_form) && !in_array($candidate_type_from_form, ['Student', 'Faculty'])) {
        $error_messages[] = 'Invalid Candidate Type selected.';
    }

    if (!empty($error_messages)) {
        $_SESSION['form_error_message'] = implode('<br>', $error_messages);
        header("Location: candidate.php"); // This is line 161 in your original error
        exit;
    }

    $uploadOkProfile = 1;
    if (!is_dir($profile_img_upload_dir)) {
        if (!mkdir($profile_img_upload_dir, 0755, true)) {
            $_SESSION['form_error_message'] = 'Failed to create profile image upload directory.';
            $uploadOkProfile = 0;
        }
    }
    if ($uploadOkProfile && $profile_image_selected) { // Added $profile_image_selected here
        $image_name = basename($_FILES["image"]["name"]);
        $sanitized_image_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_name);
        if (empty($sanitized_image_name)) $sanitized_image_name = "default_image_name" . time(); // Add time to make it somewhat unique
        $target_file_profile = $profile_img_upload_dir . uniqid('profile_', true) . "_" . $sanitized_image_name;
        $imageFileType = strtolower(pathinfo($target_file_profile, PATHINFO_EXTENSION));

        $check = @getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['form_error_message'] = 'Profile file is not a valid image.'; $uploadOkProfile = 0;
        }
        if ($uploadOkProfile && $_FILES["image"]["size"] > 2 * 1024 * 1024) { // 2MB
            $_SESSION['form_error_message'] = 'Profile image is too large (max 2MB).'; $uploadOkProfile = 0;
        }
        $allowed_profile_extensions = ["jpg", "png", "jpeg", "gif"];
        if ($uploadOkProfile && !in_array($imageFileType, $allowed_profile_extensions)) {
            $_SESSION['form_error_message'] = 'Only JPG, JPEG, PNG & GIF files are allowed for profile image.'; $uploadOkProfile = 0;
        }

        if ($uploadOkProfile) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_profile)) {
                $profile_image_path_for_db = $target_file_profile;
            } else {
                $_SESSION['form_error_message'] = 'Sorry, there was an error moving the profile image.'; $uploadOkProfile = 0;
            }
        }
    } elseif (!$profile_image_selected && $uploadOkProfile) { // If profile was required and not selected but uploadOk was still true
        // This case is covered by the initial validation, but as a safeguard:
        $_SESSION['form_error_message'] = 'Profile image is required and was not uploaded.'; $uploadOkProfile = 0;
    }


    $uploadOkEvidence = 1;
    $evidence_file_attempted = isset($_FILES["primary_evidence_file"]) && $_FILES["primary_evidence_file"]["error"] != UPLOAD_ERR_NO_FILE && !empty($_FILES["primary_evidence_file"]["name"]);

    if ($evidence_file_attempted) {
        if (!is_dir($primary_evidence_storage_dir)) {
            if (!mkdir($primary_evidence_storage_dir, 0755, true)) {
                $_SESSION['form_error_message'] = ($_SESSION['form_error_message'] ?? '') . (!empty($_SESSION['form_error_message']) ? '<br>' : '') . 'Failed to create primary evidence directory.';
                $uploadOkEvidence = 0;
            }
        }
        if ($uploadOkEvidence) {
            $evidence_name = basename($_FILES["primary_evidence_file"]["name"]);
            $sanitized_evidence_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $evidence_name);
            if (empty($sanitized_evidence_name)) $sanitized_evidence_name = "default_evidence_name" . time(); // Add time
            $target_file_evidence = $primary_evidence_storage_dir . uniqid('evidence_', true) . "_" . $sanitized_evidence_name;
            $evidenceFileType = strtolower(pathinfo($target_file_evidence, PATHINFO_EXTENSION));
            $allowed_evidence_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

            if (!in_array($evidenceFileType, $allowed_evidence_extensions)) {
                $_SESSION['form_error_message'] = ($_SESSION['form_error_message'] ?? '') . (!empty($_SESSION['form_error_message']) ? '<br>' : '') . 'Primary Evidence: Invalid file type. Allowed: ' . implode(', ', $allowed_evidence_extensions);
                $uploadOkEvidence = 0;
            }
            if ($uploadOkEvidence && $_FILES["primary_evidence_file"]["size"] > 5 * 1024 * 1024) { // 5MB
                $_SESSION['form_error_message'] = ($_SESSION['form_error_message'] ?? '') . (!empty($_SESSION['form_error_message']) ? '<br>' : '') . 'Primary Evidence: File is too large (max 5MB).';
                $uploadOkEvidence = 0;
            }

            if ($uploadOkEvidence) {
                if (move_uploaded_file($_FILES["primary_evidence_file"]["tmp_name"], $target_file_evidence)) {
                    $primary_evidence_path_for_db = $target_file_evidence;
                } else {
                    $_SESSION['form_error_message'] = ($_SESSION['form_error_message'] ?? '') . (!empty($_SESSION['form_error_message']) ? '<br>' : '') . 'Sorry, there was an error moving the primary evidence file.';
                    $uploadOkEvidence = 0;
                }
            }
        }
    } elseif (isset($_FILES["primary_evidence_file"]) && $_FILES["primary_evidence_file"]["error"] != UPLOAD_ERR_NO_FILE && $_FILES["primary_evidence_file"]["error"] != UPLOAD_ERR_OK) {
        $_SESSION['form_error_message'] = ($_SESSION['form_error_message'] ?? '') . (!empty($_SESSION['form_error_message']) ? '<br>' : '') . 'Error uploading primary evidence: Code ' . $_FILES["primary_evidence_file"]["error"];
        $uploadOkEvidence = 0;
    }


    if ($uploadOkProfile && $uploadOkEvidence) {
        try {
            $pdo->beginTransaction();

            $position_query = $pdo->prepare("SELECT position_id FROM position WHERE position_name = ?");
            $position_query->execute([$position_name_from_form]);
            $position_row = $position_query->fetch(PDO::FETCH_ASSOC);

            if (!$position_row) {
                $pdo->rollBack();
                $_SESSION['form_error_message'] = 'Error: Selected position was not found in the database.';
                if ($profile_image_path_for_db && file_exists($profile_image_path_for_db)) @unlink($profile_image_path_for_db);
                if ($primary_evidence_path_for_db && file_exists($primary_evidence_path_for_db)) @unlink($primary_evidence_path_for_db);
                header("Location: candidate.php");
                exit;
            }
            $position_id = $position_row['position_id'];

            $stmt = $pdo->prepare("INSERT INTO candidate(position, slogan, firstname, lastname, candidate_type, gender, img, primary_evidence_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$position_id, $slogan_from_form, $firstname_from_form, $lastname_from_form, $candidate_type_from_form, $gender_from_form, $profile_image_path_for_db, $primary_evidence_path_for_db]);
            
            $pdo->commit();
            $_SESSION['form_success_message'] = 'Candidate Added Successfully';
            header("Location: candidate.php");
            exit;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Add Candidate DB Error: " . $e->getMessage());
            // Be careful about echoing $e->getMessage() directly to user in production
            $_SESSION['form_error_message'] = 'Database error occurred. Code: ' . $e->getCode(); 
            if ($profile_image_path_for_db && file_exists($profile_image_path_for_db)) @unlink($profile_image_path_for_db);
            if ($primary_evidence_path_for_db && file_exists($primary_evidence_path_for_db)) @unlink($primary_evidence_path_for_db);
            header("Location: candidate.php");
            exit;
        }
    } else {
        // If there was an error message set by file upload blocks, it will be used.
        // Otherwise, set a generic one.
        if (empty($_SESSION['form_error_message'])) {
             $_SESSION['form_error_message'] = 'File upload failed or validation error. Candidate not added.';
        }
        // Clean up any files that might have been partially uploaded if errors occurred after one upload but before another
        if (!$uploadOkProfile && $profile_image_path_for_db && file_exists($profile_image_path_for_db)) {
            @unlink($profile_image_path_for_db);
        }
        if (!$uploadOkEvidence && $primary_evidence_path_for_db && file_exists($primary_evidence_path_for_db)) {
            @unlink($primary_evidence_path_for_db);
        }
        header("Location: candidate.php");
        exit;
    }
}
// The rest of your HTML for the modal starts here
?>

<body>
<div class="container">
    <?php
    if (isset($_SESSION['form_success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade in" role="alert">'
            . htmlspecialchars($_SESSION['form_success_message'])
            . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>';
        unset($_SESSION['form_success_message']);
    }
    if (isset($_SESSION['form_error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">'
            . nl2br(htmlspecialchars($_SESSION['form_error_message']))
            . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>';
        unset($_SESSION['form_error_message']);
    }
    ?>
</div>

<!-- Modal HTML (add_candidate_modal.php or embedded in candidate.php) -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Position <span class="text-danger">*</span></label>
                        <select class="form-control" name="position" required>
                            <option value="" selected disabled>Select Candidate Group</option>
                            <?php
                            if (isset($pdo)) {
                                try {
                                    $position_query_modal = $pdo->query("SELECT position_name FROM position ORDER BY position_name ASC");
                                    while ($position_row_modal = $position_query_modal->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . htmlspecialchars($position_row_modal['position_name'], ENT_QUOTES) . "'>" . htmlspecialchars($position_row_modal['position_name'], ENT_QUOTES) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error loading positions</option>";
                                    error_log("Error loading positions in add modal: " . $e->getMessage());
                                }
                            } else {
                                echo "<option value=''>DB Connection Error</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Slogan <span class="text-danger">*</span></label>
                        <input class="form-control" name="slogan" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Firstname <span class="text-danger">*</span></label>
                        <input class="form-control" name="firstname" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Lastname <span class="text-danger">*</span></label>
                        <input class="form-control" name="lastname" type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Candidate Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="candidate_type" required>
                            <option value="" selected disabled>Select Type</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gender <span class="text-danger">*</span></label>
                        <select class="form-control" name="gender" required>
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Profile Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control-file" required>
                        <small class="form-text text-muted">Max 2MB (JPG, PNG, GIF, JPEG).</small>
                    </div>
                    <div class="form-group">
                        <label>Primary Certificate/Evidence (Optional)</label>
                        <input type="file" name="primary_evidence_file" id="add_primary_evidence_file" class="form-control-file">
                        <small class="form-text text-muted">Single file. Max 5MB (PDF, DOC, DOCX, JPG, PNG, TXT).</small>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" name="save" class="btn btn-primary">Save Candidate</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- Example button to trigger the modal if this page is viewed directly -->
<!-- <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Add Candidate (Test)</button> -->
</body>
</html>
<?php
// Flush the output buffer and send it to the browser
ob_end_flush();
?>