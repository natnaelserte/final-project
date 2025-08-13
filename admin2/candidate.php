<?php
session_start();
ob_start();
include('dbcon.php');

// --- BEGIN: REVISED PHP LOGIC FOR UPDATING CANDIDATE ---
if (isset($_POST['update_candidate_submit'])) {
    $candidate_id_to_update = $_POST['candidate_id'] ?? null; // Use null coalescing
    $position_id_from_form = $_POST['position'] ?? null;      // Use null coalescing
    $slogan_to_update = trim($_POST['slogan'] ?? '');
    $firstname_to_update = trim($_POST['firstname'] ?? '');
    $lastname_to_update = trim($_POST['lastname'] ?? '');
    $candidate_type_to_update = $_POST['candidate_type'] ?? '';
    $gender_to_update = $_POST['gender'] ?? '';
    $current_image_path_from_form = $_POST['current_image_path'] ?? '';

    $target_file_path_for_db = $current_image_path_from_form;
    $uploadOk = 1;
    $new_image_upload_attempted = false;

    // Basic validation
    $update_errors = [];
    if (empty($candidate_id_to_update)) $update_errors[] = 'Candidate ID is missing for update.';
    if (empty($position_id_from_form)) $update_errors[] = 'Position is required for update.';
    if (empty($slogan_to_update)) $update_errors[] = 'Slogan is required for update.';
    if (empty($firstname_to_update)) $update_errors[] = 'Firstname is required for update.';
    if (empty($lastname_to_update)) $update_errors[] = 'Lastname is required for update.';
    if (empty($candidate_type_to_update) || !in_array($candidate_type_to_update, ['Student', 'Faculty'])) {
        $update_errors[] = 'Invalid Candidate Type selected for update.';
    }
    if (empty($gender_to_update)) $update_errors[] = 'Gender is required for update.';

    if (!empty($update_errors)) {
        $_SESSION['error_message_page'] = implode('<br>', $update_errors);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }


    // Image Upload Handling (if a new image is provided)
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["image"]["name"])) {
        $new_image_upload_attempted = true;
        $target_dir = "upload/"; // Make sure this directory exists and is writable

        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $_SESSION['error_message_page'] = 'Failed to create upload directory. Check permissions for admin2/upload/.';
                $uploadOk = 0;
            }
        }

        if ($uploadOk == 1) {
            $image_name = basename($_FILES["image"]["name"]);
            $sanitized_image_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_name);
            if (empty($sanitized_image_name)) $sanitized_image_name = "image_" . time(); // Fallback name
            $new_target_file = $target_dir . uniqid('img_', true) . "_" . $sanitized_image_name;
            $imageFileType = strtolower(pathinfo($new_target_file, PATHINFO_EXTENSION));

            $check = @getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $_SESSION['error_message_page'] = 'New file is not a valid image.';
                $uploadOk = 0;
            }
            if ($uploadOk && $_FILES["image"]["size"] > 2 * 1024 * 1024) { // 2MB limit
                $_SESSION['error_message_page'] = 'Sorry, your new file is too large (max 2MB).';
                $uploadOk = 0;
            }
            $allowed_extensions = ["jpg", "png", "jpeg", "gif"];
            if ($uploadOk && !in_array($imageFileType, $allowed_extensions)) {
                $_SESSION['error_message_page'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image.';
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_target_file)) {
                    $target_file_path_for_db = $new_target_file;
                    // Delete old image if it's different and exists
                    if ($current_image_path_from_form &&
                        $current_image_path_from_form != $new_target_file &&
                        file_exists($current_image_path_from_form) &&
                        is_writable(dirname($current_image_path_from_form))) { // Check dir writability
                       @unlink($current_image_path_from_form);
                    }
                } else {
                    $_SESSION['error_message_page'] = 'Sorry, there was an error saving your new file. Check permissions for admin2/upload/ directory.';
                    $uploadOk = 0;
                }
            }
        }
    }

    // If new image upload was attempted but failed, stop and show error
    if ($new_image_upload_attempted && $uploadOk == 0) {
        if (!isset($_SESSION['error_message_page'])) { // Fallback error if not set by specific checks
            $_SESSION['error_message_page'] = 'An unspecified error occurred during image processing. Update halted.';
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Proceed with database update if no image upload errors (or no new image was uploaded)
    if ($uploadOk == 1) {
        try {
            $pdo->beginTransaction();

            // Check if candidate ID exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM candidate WHERE candidate_id = :candidate_id");
            $check_stmt->bindParam(':candidate_id', $candidate_id_to_update, PDO::PARAM_INT);
            $check_stmt->execute();

            if ($check_stmt->fetchColumn() == 0) {
                $_SESSION['error_message_page'] = 'Error: Candidate ID (' . htmlspecialchars($candidate_id_to_update) . ') not found for update.';
                $pdo->rollBack();
            } else {
                // Assuming 'position' column in 'candidate' table stores the position_id
                $sql_update = "UPDATE candidate SET
                            position = :position,
                            slogan = :slogan,
                            firstname = :firstname,
                            lastname = :lastname,
                            candidate_type = :candidate_type,
                            gender = :gender,
                            img = :img
                        WHERE candidate_id = :candidate_id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':position', $position_id_from_form, PDO::PARAM_INT);
                $stmt_update->bindParam(':slogan', $slogan_to_update);
                $stmt_update->bindParam(':firstname', $firstname_to_update);
                $stmt_update->bindParam(':lastname', $lastname_to_update);
                $stmt_update->bindParam(':candidate_type', $candidate_type_to_update);
                $stmt_update->bindParam(':gender', $gender_to_update);
                $stmt_update->bindParam(':img', $target_file_path_for_db);
                $stmt_update->bindParam(':candidate_id', $candidate_id_to_update, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    if ($stmt_update->rowCount() > 0) {
                        $_SESSION['success_message_page'] = 'Candidate Updated Successfully.';
                    } else {
                        $_SESSION['info_message_page'] = 'Candidate data processed, but no changes were detected (data might be identical).';
                    }
                    $pdo->commit();
                } else {
                    $pdo->rollBack();
                    $_SESSION['error_message_page'] = 'Error executing candidate update. DB Error: ' . implode(":", $stmt_update->errorInfo());
                }
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message_page'] = "Database Error during update: " . htmlspecialchars($e->getMessage());
             // Optionally log $e->getTraceAsString() for detailed debugging for yourself
            // error_log("Candidate Update Error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect after processing
    exit;
}
// --- END: REVISED PHP LOGIC FOR UPDATING CANDIDATE ---

$all_positions_for_dropdown = [];
try {
    $pos_query_all = $pdo->query("SELECT position_id, position_name FROM position ORDER BY position_name ASC");
    $all_positions_for_dropdown = $pos_query_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Don't overwrite existing error messages unless this is the only one
    if (!isset($_SESSION['error_message_page'])) {
        $_SESSION['error_message_page'] = "Error fetching positions list for edit form: " . htmlspecialchars($e->getMessage());
    }
}

// Retrieve messages from session to display them
if (isset($_SESSION['success_message_page'])) {
    $success_message_from_session = $_SESSION['success_message_page'];
    unset($_SESSION['success_message_page']);
}
if (isset($_SESSION['error_message_page'])) {
    $error_message_from_session = $_SESSION['error_message_page'];
    unset($_SESSION['error_message_page']);
}
if (isset($_SESSION['info_message_page'])) {
    $info_message_from_session = $_SESSION['info_message_page'];
    unset($_SESSION['info_message_page']);
}

// Initialize chart data arrays
$positionNames = []; $positionCounts = [];
$jsCandidateTypes = []; $jsMaleCountsByType = []; $jsFemaleCountsByType = [];
$maleGenderCount = 0; $femaleGenderCount = 0;

try {
    // Data for Candidates by Position Chart
    $positionsData = $pdo->query("SELECT c.position, p.position_name, COUNT(*) as count
                                    FROM candidate c
                                    JOIN position p ON c.position = p.position_id
                                    GROUP BY c.position, p.position_name
                                    ORDER BY p.position_name")
                                    ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($positionsData as $pos) {
        $positionNames[] = $pos['position_name'];
        $positionCounts[] = (int)$pos['count'];
    }

    // Data for Candidates by Type and Gender Chart
    $candidateTypeGenderData = $pdo->query("
        SELECT candidate_type, gender, COUNT(*) as count
        FROM candidate
        GROUP BY candidate_type, gender
        ORDER BY candidate_type, gender")->fetchAll(PDO::FETCH_ASSOC);

    $tempCountsByType = [];
    foreach ($candidateTypeGenderData as $data) {
        if (!in_array($data['candidate_type'], $jsCandidateTypes)) {
            $jsCandidateTypes[] = $data['candidate_type'];
        }
        $tempCountsByType[$data['candidate_type']][$data['gender']] = (int)$data['count'];
    }
    sort($jsCandidateTypes); // Optional: Ensure a consistent order e.g., Faculty, Student

    foreach ($jsCandidateTypes as $cType) {
        $jsMaleCountsByType[] = $tempCountsByType[$cType]['Male'] ?? 0;
        $jsFemaleCountsByType[] = $tempCountsByType[$cType]['Female'] ?? 0;
    }

    // Data for Gender Distribution Pie Chart
    $genderData = $pdo->query("SELECT gender, COUNT(*) as count FROM candidate GROUP BY gender")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($genderData as $g) {
        if (strtolower($g['gender']) === 'male') $maleGenderCount = (int)$g['count'];
        if (strtolower($g['gender']) === 'female') $femaleGenderCount = (int)$g['count'];
    }

} catch (PDOException $e) {
    if (!isset($_SESSION['error_message_page'])) { // Avoid overwriting specific update errors
        $_SESSION['error_message_page'] = "Database error fetching chart data: " . htmlspecialchars($e->getMessage());
    }
    // Reset chart data on error to prevent JS issues
    $positionNames = $positionCounts = $jsCandidateTypes = $jsMaleCountsByType = $jsFemaleCountsByType = [];
    $maleGenderCount = $femaleGenderCount = 0;
}
include('head.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Candidate Management - Admin Panel</title>
    <!-- Modern Admin Theme CSS -->
    <link rel="stylesheet" href="../admin/css/modern-admin.css">
</head>

<style>
    /* Custom Button Base Styles with #90D1CA as primary */
    :root {
        --primary-color: #90D1CA;
        --primary-dark: #75B5AE;  /* Darker shade for hover/active states */
        --primary-light: #A8DCD6; /* Lighter shade for backgrounds */
        --primary-very-light: #E5F4F2; /* Very light shade for subtle backgrounds */
        --text-on-primary: #333333; /* Dark text on primary color */
        --text-light: #ffffff;
        --text-dark: #333333;
        --accent-color: #FF9E80; /* Complementary accent if needed */
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;

        /* Button variables */
        --btn-border-radius: 6px;
        --btn-padding-y: 0.55rem;
        --btn-padding-x: 1.1rem;
        --btn-font-size: 0.9rem;
        --btn-font-weight: 500;
        --btn-icon-margin-right: 8px;
        --btn-shadow: 0 2px 4px rgba(0, 0, 0, 0.075);
        --btn-hover-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        --btn-active-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-custom-solid, .btn-custom-outline {
        border-radius: var(--btn-border-radius);
        padding: var(--btn-padding-y) var(--btn-padding-x);
        font-size: var(--btn-font-size);
        font-weight: var(--btn-font-weight);
        transition: all 0.2s ease-in-out;
        box-shadow: var(--btn-shadow);
        text-transform: none;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-custom-solid:hover, .btn-custom-outline:hover {
        transform: translateY(-1px);
        box-shadow: var(--btn-hover-shadow);
    }

    .btn-custom-solid:active, .btn-custom-outline:active {
        transform: translateY(0);
        box-shadow: var(--btn-active-shadow);
    }

    .btn-custom-solid i, .btn-custom-outline i {
        margin-right: var(--btn-icon-margin-right);
    }

    /* Primary Button */
    .btn-custom-solid.btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--text-dark);
    }

    .btn-custom-solid.btn-primary-custom:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    /* Success Button */
    .btn-custom-solid.btn-success-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--text-dark);
    }

    .btn-custom-solid.btn-success-custom:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    /* Danger Button */
    .btn-custom-solid.btn-danger-custom {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }

    .btn-custom-solid.btn-danger-custom:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    /* Info Button */
    .btn-custom-solid.btn-info-custom {
        background-color: var(--info-color);
        border-color: var(--info-color);
        color: white;
    }

    .btn-custom-solid.btn-info-custom:hover {
        background-color: #138496;
        border-color: #117a8b;
    }

    /* Secondary Outline Button */
    .btn-custom-outline.btn-secondary-custom {
        background-color: white;
        border: 1px solid var(--primary-color);
        color: var(--primary-dark);
    }

    .btn-custom-outline.btn-secondary-custom:hover {
        background-color: var(--primary-color);
        color: var(--text-dark);
    }

    /* Button sizes */
    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.82rem;
    }

    .btn-lg {
        padding: 0.7rem 1.5rem;
        font-size: 1.1rem;
    }

    /* Table styling improvements */
    #dataTables-example {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    #dataTables-example thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    #dataTables-example tbody tr:hover {
        background-color: rgba(0,123,255,0.03);
    }

    .img-circle {
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Panel styling */
    .panel-primary {
        border-color: var(--primary-color);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .panel-primary > .panel-heading {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--text-dark);
        padding: 12px 15px;
    }

    .panel-title {
        font-size: 1.2rem;
        font-weight: 600;
    }

    /* Modal styling */
    .modal-content {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .modal-title {
        font-weight: 600;
        color: #495057;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        background-color: #f8f9fa;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    /* Form styling */
    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: var(--primary-color, rgba(30, 110, 157, 0.9));
        box-shadow: 0 0 0 0.2rem rgba(30, 110, 157, 0.25);
    }

    label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
</style>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>
    <div id="page-wrapper">
        <!-- ALERTS SECTION -->
        <div class="row">
            <div class="col-lg-12">
                <?php if (isset($success_message_from_session)): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo nl2br(htmlspecialchars($success_message_from_session)); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error_message_from_session)): ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo nl2br(htmlspecialchars($error_message_from_session)); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($info_message_from_session)): ?>
                    <div class="alert alert-info alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo nl2br(htmlspecialchars($info_message_from_session)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PAGE HEADER -->
        <div class="row">
            <div class="col-lg-12">
                <div class="modern-page-header">
                    <h1>
                        <i class="fa fa-users"></i> Candidate Management
                    </h1>
                </div>
                <ol class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="active">Candidates</li>
                </ol>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-buttons-container">
            <h4><i class="fa fa-cogs"></i> Quick Actions</h4>
            <div class="text-center">
                <button class="btn btn-success btn-lg" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-plus"></i> Add Candidate
                </button>
                <?php include('add_candidate_modal.php'); ?>
<!--
                <button class="btn btn-danger btn-lg" data-toggle="modal" data-target="#deleteAllModal">
                    <i class="fa fa-trash"></i> Delete All Candidates
                </button> -->
                <?php include('delete_all_candidate_modal.php'); ?>
            </div>
        </div>

        <!-- CANDIDATES TABLE -->
        <div class="panel modern-table-panel">
            <div class="panel-heading">
                <i class="fa fa-table"></i> Candidate Directory
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover modern-table" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>Position</th>
                            <th>Slogan</th>
                            <th>Firstname</th>
                            <th>Lastname</th>
                            <th>Candidate Type</th>
                            <th>Gender</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt_candidates = $pdo->query(
                                "SELECT c.*, p.position_name
                                 FROM candidate c
                                 LEFT JOIN position p ON c.position = p.position_id
                                 ORDER BY c.candidate_id DESC"
                            );
                            while ($row_candidate = $stmt_candidates->fetch(PDO::FETCH_ASSOC)) {
                                $candidate_id = $row_candidate['candidate_id'];
                                $position_name_display = htmlspecialchars($row_candidate['position_name'] ?? 'N/A');
                                $img_src = !empty($row_candidate['img']) && file_exists($row_candidate['img'])
                                           ? htmlspecialchars($row_candidate['img']) . '?t=' . time()
                                           : 'assets/img/default-profile.png'; // Provide a default image path

                                // Check if primary evidence exists
                                $has_evidence = !empty($row_candidate['primary_evidence_path']) &&
                                               file_exists($row_candidate['primary_evidence_path']);
                                ?>
                                <tr>
                                    <td width="60" class="text-center">
                                        <img src="<?php echo $img_src; ?>" width="50" height="50" class="img-circle" alt="Candidate Image">
                                    </td>
                                    <td><?php echo $position_name_display; ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['slogan']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['candidate_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['gender']); ?></td>
                                    <td style="text-align:center; white-space: nowrap;">
                                        <!-- View Evidence Button (if available) -->
                                        <?php if ($has_evidence): ?>
                                        <a rel="tooltip" title="View Evidence"
                                           href="#view_evidence<?php echo $candidate_id; ?>"
                                           data-toggle="modal"
                                           class="btn btn-info btn-xs">
                                            <i class="fa fa-file-text"></i>
                                        </a>
                                        <?php endif; ?>

                                        <!-- Edit Button -->
                                        <a rel="tooltip" title="Edit"
                                           class="btn btn-success btn-xs edit-candidate-btn"
                                           data-toggle="modal"
                                           data-target="#universalEditCandidateModal"
                                           data-candidate_id="<?php echo htmlspecialchars($row_candidate['candidate_id']); ?>"
                                           data-position_id="<?php echo htmlspecialchars($row_candidate['position']); ?>"
                                           data-slogan="<?php echo htmlspecialchars($row_candidate['slogan']); ?>"
                                           data-firstname="<?php echo htmlspecialchars($row_candidate['firstname']); ?>"
                                           data-lastname="<?php echo htmlspecialchars($row_candidate['lastname']); ?>"
                                           data-candidate_type="<?php echo htmlspecialchars($row_candidate['candidate_type']); ?>"
                                           data-gender="<?php echo htmlspecialchars($row_candidate['gender']); ?>"
                                           data-img_path="<?php echo htmlspecialchars($row_candidate['img']); ?>">
                                            <i class="fa fa-pencil"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <!-- <a rel="tooltip" title="Delete" id="del_<?php echo $candidate_id; ?>"
                                           href="#delete_user<?php echo $candidate_id; ?>"
                                           data-target="#delete_user<?php echo $candidate_id ?>"
                                           data-toggle="modal"
                                           class="btn btn-custom-solid btn-danger-custom btn-sm">
                                            <i class="fa fa-trash-o"></i> Delete
                                        </a> -->

                                        <?php include('delete_candidate_modal.php'); ?>

                                        <!-- Evidence Modal -->
                                        <?php if ($has_evidence): ?>
                                        <div class="modal fade" id="view_evidence<?php echo $candidate_id; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="modal-title">Evidence for <?php echo htmlspecialchars($row_candidate['firstname'] . ' ' . $row_candidate['lastname']); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        $evidence_path = $row_candidate['primary_evidence_path'];
                                                        $file_ext = strtolower(pathinfo($evidence_path, PATHINFO_EXTENSION));
                                                        $filename = basename($evidence_path);

                                                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                            // Display image directly
                                                            echo '<div class="text-center">';
                                                            echo '<img src="' . htmlspecialchars($evidence_path) . '" class="img-responsive" alt="Candidate Evidence" style="max-width: 100%; height: auto;">';
                                                            echo '</div>';
                                                        } elseif (in_array($file_ext, ['pdf'])) {
                                                            // For PDF files, provide a safe viewer
                                                            echo '<div class="text-center">';
                                                            echo '<p><i class="fa fa-file-pdf-o fa-3x text-danger"></i></p>';
                                                            echo '<p><strong>PDF Document:</strong> ' . htmlspecialchars($filename) . '</p>';
                                                            echo '<p>Click the download button below to view the PDF file.</p>';
                                                            echo '</div>';
                                                        } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                                            // For Word documents
                                                            echo '<div class="text-center">';
                                                            echo '<p><i class="fa fa-file-word-o fa-3x text-primary"></i></p>';
                                                            echo '<p><strong>Word Document:</strong> ' . htmlspecialchars($filename) . '</p>';
                                                            echo '<p>Click the download button below to view the document.</p>';
                                                            echo '</div>';
                                                        } elseif (in_array($file_ext, ['txt'])) {
                                                            // For text files, try to display content safely
                                                            echo '<div class="well">';
                                                            echo '<h5>Text File Content:</h5>';
                                                            if (file_exists($evidence_path) && filesize($evidence_path) < 50000) { // Limit to 50KB
                                                                $content = file_get_contents($evidence_path);
                                                                echo '<pre>' . htmlspecialchars($content) . '</pre>';
                                                            } else {
                                                                echo '<p>File too large to display. Use download button below.</p>';
                                                            }
                                                            echo '</div>';
                                                        } else {
                                                            // For other file types, show file info
                                                            echo '<div class="text-center">';
                                                            echo '<p><i class="fa fa-file-o fa-3x text-info"></i></p>';
                                                            echo '<p><strong>File:</strong> ' . htmlspecialchars($filename) . '</p>';
                                                            echo '<p><strong>Type:</strong> ' . strtoupper($file_ext) . ' file</p>';
                                                            echo '<p>Click the download button below to view the file.</p>';
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <?php if (in_array($file_ext, ['pdf'])): ?>
                                                        <a href="<?php echo htmlspecialchars($evidence_path); ?>"
                                                           target="_blank"
                                                           class="btn btn-info"
                                                           rel="noopener noreferrer">
                                                            <i class="fa fa-eye"></i> View PDF
                                                        </a>
                                                        <?php endif; ?>
                                                        <a href="<?php echo htmlspecialchars($evidence_path); ?>"
                                                           class="btn btn-success"
                                                           download="<?php echo htmlspecialchars($filename); ?>">
                                                            <i class="fa fa-download"></i> Download File
                                                        </a>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='8' class='text-danger'>Database Error loading candidates: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- UNIVERSAL EDIT CANDIDATE MODAL -->
        <div class="modal fade" id="universalEditCandidateModal" tabindex="-1" role="dialog" aria-labelledby="editCandidateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="editCandidateModalLabel">Edit Candidate</h4>
                    </div>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="candidate_id" id="edit_candidate_id_field">
                            <input type="hidden" name="current_image_path" id="edit_current_image_path_field">
                            <div class="form-group">
                                <label for="edit_position_field">Position <span class="text-danger">*</span></label>
                                <select class="form-control" name="position" id="edit_position_field" required>
                                    <option value="" disabled>Select Candidate Group</option>
                                    <?php
                                    if (!empty($all_positions_for_dropdown)) {
                                        foreach ($all_positions_for_dropdown as $pos_item) {
                                            echo "<option value='" . htmlspecialchars($pos_item['position_id']) . "'>" . htmlspecialchars($pos_item['position_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No positions loaded</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_slogan_field">Slogan <span class="text-danger">*</span></label>
                                <input class="form-control" name="slogan" id="edit_slogan_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_firstname_field">Firstname <span class="text-danger">*</span></label>
                                <input class="form-control" name="firstname" id="edit_firstname_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_lastname_field">Lastname <span class="text-danger">*</span></label>
                                <input class="form-control" name="lastname" id="edit_lastname_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_candidate_type_field">Candidate Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="candidate_type" id="edit_candidate_type_field" required>
                                    <option value="" disabled>Select Type</option>
                                    <option value="Student">Student</option>
                                    <option value="Faculty">Faculty</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_gender_field">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" name="gender" id="edit_gender_field" required>
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Current Image</label><br>
                                <img src="" id="edit_current_image_preview" width="100" height="100" alt="Current Image" class="img-thumbnail" style="margin-bottom:10px; display:none; object-fit: cover;"><br>
                                <label for="edit_new_image_field">New Image (Optional)</label>
                                <input type="file" name="image" id="edit_new_image_field" class="form-control-file">
                                <small class="form-text text-muted">Leave blank to keep current. Max 2MB (JPG, PNG, GIF, JPEG).</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" name="update_candidate_submit" class="btn btn-success">Update Candidate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- End Edit Modal -->

        <!-- CHARTS SECTION -->
        <div class="row" style="margin-top: 40px;">
            <div class="col-lg-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-bar-chart"></i> Candidates by Position</h3>
                    </div>
                    <div class="panel-body">
                        <div id="positionsBarChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-users"></i> Candidates by Type and Gender</h3>
                    </div>
                    <div class="panel-body">
                        <div id="candidateTypeGenderChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 50px;">
            <div class="col-lg-12">
                <div class="panel modern-chart-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-pie-chart"></i> Gender Distribution</h3>
                    </div>
                    <div class="panel-body">
                        <div id="genderPieChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- /#page-wrapper -->
</div> <!-- /#wrapper -->

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<?php include('script.php'); ?>
<script>
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        // Positions Bar Chart
        var positionsDataTable = new google.visualization.DataTable();
        positionsDataTable.addColumn('string', 'Position');
        positionsDataTable.addColumn('number', 'Count');
        var positions = <?php echo json_encode($positionNames); ?>;
        var counts = <?php echo json_encode($positionCounts); ?>;
        if (positions && counts && positions.length > 0 && positions.length === counts.length) {
            for (var i = 0; i < positions.length; i++) {
                positionsDataTable.addRow([positions[i], counts[i]]);
            }
        }
        var positionsOptions = { title: 'Number of Candidates by Position', legend: {position: 'none'}, colors: ['#90D1CA'], chartArea: {width: '70%'}, vAxis: {minValue: 0, format: '0'} };
        var positionsChart = new google.visualization.BarChart(document.getElementById('positionsBarChart'));
        if (positionsDataTable.getNumberOfRows() > 0) {
            positionsChart.draw(positionsDataTable, positionsOptions);
        } else {
            document.getElementById('positionsBarChart').innerHTML = '<p class="text-center" style="padding-top:50px;">No position data available for chart.</p>';
        }

        // Candidate Type and Gender Column Chart
        var candidateTypeGenderDataTable = new google.visualization.DataTable();
        candidateTypeGenderDataTable.addColumn('string', 'Candidate Type');
        candidateTypeGenderDataTable.addColumn('number', 'Male');
        candidateTypeGenderDataTable.addColumn('number', 'Female');
        var cTypes = <?php echo json_encode($jsCandidateTypes); ?>;
        var maleDataCountsByType = <?php echo json_encode($jsMaleCountsByType); ?>;
        var femaleDataCountsByType = <?php echo json_encode($jsFemaleCountsByType); ?>;
         if (cTypes && maleDataCountsByType && femaleDataCountsByType && cTypes.length > 0 && cTypes.length === maleDataCountsByType.length && cTypes.length === femaleDataCountsByType.length) {
            for (var j = 0; j < cTypes.length; j++) {
                candidateTypeGenderDataTable.addRow([cTypes[j].toString(), maleDataCountsByType[j] || 0, femaleDataCountsByType[j] || 0]);
            }
        }
        var candidateTypeGenderOptions = {
            title: 'Candidates by Type and Gender',
            hAxis: {title: 'Candidate Type'},
            vAxis: {title: 'Number of Candidates', minValue: 0, format: '0'},
            colors: ['#90D1CA', '#75B5AE'],
            legend: {position: 'top', maxLines: 3},
            isStacked: false
        };
        var candidateTypeGenderChart = new google.visualization.ColumnChart(document.getElementById('candidateTypeGenderChart'));
        if (candidateTypeGenderDataTable.getNumberOfRows() > 0) {
            candidateTypeGenderChart.draw(candidateTypeGenderDataTable, candidateTypeGenderOptions);
        } else {
            document.getElementById('candidateTypeGenderChart').innerHTML = '<p class="text-center" style="padding-top:50px;">No candidate type data available for chart.</p>';
        }

        // Gender Pie Chart
        var genderDataTable = google.visualization.arrayToDataTable([
            ['Gender', 'Count'],
            ['Male', <?php echo $maleGenderCount; ?>],
            ['Female', <?php echo $femaleGenderCount; ?>]
        ]);
        var genderOptions = { title: 'Candidates Gender Distribution', pieHole: 0.4, colors: ['#90D1CA', '#A8DCD6'] };
        var genderChart = new google.visualization.PieChart(document.getElementById('genderPieChart'));
        if (<?php echo $maleGenderCount + $femaleGenderCount; ?> > 0) {
            genderChart.draw(genderDataTable, genderOptions);
        } else {
            document.getElementById('genderPieChart').innerHTML = '<p class="text-center" style="padding-top:50px;">No gender data available for chart.</p>';
        }
    }
    window.addEventListener('resize', drawCharts); // Redraw charts on window resize

    $(document).ready(function() {
        // DataTable Initialization
        if ($.fn.dataTable && !$.fn.dataTable.isDataTable('#dataTables-example')) {
            $('#dataTables-example').DataTable({
                responsive: true,
                order: [] // Disable initial sorting or set your preferred default
            });
        }

        // Edit Candidate Modal Population
        $('.edit-candidate-btn').on('click', function() {
            var candidateId   = $(this).data('candidate_id');
            var positionId    = $(this).data('position_id');
            var slogan        = $(this).data('slogan');
            var firstname     = $(this).data('firstname');
            var lastname      = $(this).data('lastname');
            var candidateType = $(this).data('candidate_type');
            var gender        = $(this).data('gender');
            var imgPath       = $(this).data('img_path');

            var modal = $('#universalEditCandidateModal');
            modal.find('#edit_candidate_id_field').val(candidateId);
            modal.find('#edit_position_field').val(positionId);
            modal.find('#edit_slogan_field').val(slogan);
            modal.find('#edit_firstname_field').val(firstname);
            modal.find('#edit_lastname_field').val(lastname);
            modal.find('#edit_candidate_type_field').val(candidateType);
            modal.find('#edit_gender_field').val(gender);
            modal.find('#edit_current_image_path_field').val(imgPath);

            var previewImage = modal.find('#edit_current_image_preview');
            if(imgPath && imgPath.trim() !== "" && imgPath.trim() !== "upload/") { // Check if imgPath is meaningful
                // Add a cache-busting parameter to the image URL
                previewImage.attr('src', imgPath + '?t=' + new Date().getTime()).show();
            } else {
                previewImage.attr('src', 'path/to/default/image.png').show(); // Or hide: previewImage.hide();
            }
            modal.find('#edit_new_image_field').val(''); // Clear the file input
        });

        // Reset modal form on close
        $('#universalEditCandidateModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('#edit_current_image_preview').attr('src', '').hide();
            $(this).find('.is-invalid').removeClass('is-invalid'); // For Bootstrap 4/5 validation styling
            $(this).find('.invalid-feedback').remove(); // For Bootstrap 4/5 validation styling
        });

    });
</script>
<?php
include('script.php');
ob_end_flush();
?>
</body>
</html>
