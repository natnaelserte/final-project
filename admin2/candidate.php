<?php
include('session.php'); // session_start() should be in here or at the very top
include('head.php');
require_once 'dbcon.php'; // Ensure this is included early for DB operations

// Initialize messages
$success_message_from_session = null;
$error_message_from_session = null;
$info_message_from_session = null; // For "no changes made"

// --- BEGIN: REVISED PHP LOGIC FOR UPDATING CANDIDATE ---
if (isset($_POST['update_candidate_submit'])) {
    $candidate_id_to_update = $_POST['candidate_id'];
    $position_id_from_form = $_POST['position'];
    $slogan_to_update = $_POST['slogan'];
    $firstname_to_update = $_POST['firstname'];
    $lastname_to_update = $_POST['lastname'];
    $year_level_to_update = $_POST['year_level'];
    $gender_to_update = $_POST['gender'];
    $current_image_path_from_form = $_POST['current_image_path'];

    $target_file_path_for_db = $current_image_path_from_form;
    $uploadOk = 1;
    $new_image_upload_attempted = false;

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["image"]["name"])) {
        $new_image_upload_attempted = true;
        $target_dir = "upload/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $_SESSION['error_message_page'] = 'Failed to create upload directory. Check permissions.';
                $uploadOk = 0;
            }
        }

        if ($uploadOk == 1) {
            $image_name = basename($_FILES["image"]["name"]);
            $sanitized_image_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_name);
            $new_target_file = $target_dir . uniqid() . "_" . $sanitized_image_name;
            $imageFileType = strtolower(pathinfo($new_target_file, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $_SESSION['error_message_page'] = 'New file is not an image.';
                $uploadOk = 0;
            }
            if ($uploadOk && $_FILES["image"]["size"] > 2000000) {
                $_SESSION['error_message_page'] = 'Sorry, your new file is too large (max 2MB).';
                $uploadOk = 0;
            }
            if ($uploadOk && !in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                $_SESSION['error_message_page'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image.';
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_target_file)) {
                    $target_file_path_for_db = $new_target_file;
                    if ($current_image_path_from_form &&
                        $current_image_path_from_form != $new_target_file &&
                        file_exists($current_image_path_from_form)) {
                        if (is_writable($current_image_path_from_form) || is_writable(dirname($current_image_path_from_form))) {
                           @unlink($current_image_path_from_form);
                        }
                    }
                } else {
                    $_SESSION['error_message_page'] = 'Sorry, there was an error saving your new file. Check permissions for upload/ directory.';
                    $uploadOk = 0;
                }
            }
        }
    }

    if ($new_image_upload_attempted && $uploadOk == 0) {
        if (!isset($_SESSION['error_message_page'])) {
            $_SESSION['error_message_page'] = 'An unspecified error occurred during image processing. Update halted.';
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($uploadOk == 1) {
        try {
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM candidate WHERE candidate_id = :candidate_id");
            $check_stmt->bindParam(':candidate_id', $candidate_id_to_update, PDO::PARAM_INT);
            $check_stmt->execute();
            if ($check_stmt->fetchColumn() == 0) {
                $_SESSION['error_message_page'] = 'Error: Candidate ID (' . htmlspecialchars($candidate_id_to_update) . ') not found for update.';
            } else {
                $sql_update = "UPDATE candidate SET 
                            position = :position, 
                            slogan = :slogan,
                            firstname = :firstname, 
                            lastname = :lastname, 
                            year_level = :year_level, 
                            gender = :gender, 
                            img = :img 
                        WHERE candidate_id = :candidate_id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':position', $position_id_from_form, PDO::PARAM_INT);
                $stmt_update->bindParam(':slogan', $slogan_to_update);
                $stmt_update->bindParam(':firstname', $firstname_to_update);
                $stmt_update->bindParam(':lastname', $lastname_to_update);
                $stmt_update->bindParam(':year_level', $year_level_to_update);
                $stmt_update->bindParam(':gender', $gender_to_update);
                $stmt_update->bindParam(':img', $target_file_path_for_db);
                $stmt_update->bindParam(':candidate_id', $candidate_id_to_update, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    if ($stmt_update->rowCount() > 0) {
                        $_SESSION['success_message_page'] = 'Candidate Updated Successfully.';
                    } else {
                        $_SESSION['info_message_page'] = 'Candidate data processed, but no changes were detected (data might be identical or ID was not found for actual update).';
                    }
                } else {
                    $_SESSION['error_message_page'] = 'Error executing candidate update. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_message_page'] = "Database Error: " . htmlspecialchars($e->getMessage());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
// --- END: REVISED PHP LOGIC FOR UPDATING CANDIDATE ---


if (isset($_POST['backup_database'])) {
    $backupResult = include('Backup/backup_db.php');
    if (is_array($backupResult) && isset($backupResult['success']) && $backupResult['success']) {
        $_SESSION['success_message_page'] = $backupResult['message'];
    } else {
        $_SESSION['error_message_page'] = (is_array($backupResult) && isset($backupResult['message'])) ? $backupResult['message'] : 'Backup failed with an unknown error.';
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$all_positions_for_dropdown = [];
try {
    $pos_query_all = $pdo->query("SELECT position_id, position_name FROM position ORDER BY position_name ASC");
    $all_positions_for_dropdown = $pos_query_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (!isset($_SESSION['error_message_page'])) {
        $_SESSION['error_message_page'] = "Error fetching positions list: " . htmlspecialchars($e->getMessage());
    }
}

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

try {
    $positionsData = $pdo->query("SELECT position, COUNT(*) as count FROM candidate GROUP BY position")->fetchAll(PDO::FETCH_ASSOC);
    $yearLevelsData = $pdo->query("
        SELECT year_level, gender, COUNT(*) as count 
        FROM candidate 
        GROUP BY year_level, gender 
        ORDER BY year_level
    ")->fetchAll(PDO::FETCH_ASSOC);
    $genderData = $pdo->query("SELECT gender, COUNT(*) as count FROM candidate GROUP BY gender")->fetchAll(PDO::FETCH_ASSOC);

    $positionNames = []; $positionCounts = [];
    foreach ($positionsData as $pos) {
        $stmt = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
        $stmt->execute([$pos['position']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $positionNames[] = $row ? $row['position_name'] : 'Unknown';
        $positionCounts[] = (int)$pos['count'];
    }

    $uniqueYearLevels = []; $maleCounts = []; $femaleCounts = [];
    foreach ($yearLevelsData as $data) {
        if (!in_array($data['year_level'], $uniqueYearLevels)) {
            $uniqueYearLevels[] = $data['year_level'];
        }
    }
    foreach ($uniqueYearLevels as $year) {
        $maleCounts[$year] = 0; $femaleCounts[$year] = 0;
    }
    foreach ($yearLevelsData as $data) {
        if (isset($maleCounts[$data['year_level']]) && $data['gender'] === 'Male') {
            $maleCounts[$data['year_level']] = (int)$data['count'];
        } elseif (isset($femaleCounts[$data['year_level']]) && $data['gender'] === 'Female') {
            $femaleCounts[$data['year_level']] = (int)$data['count'];
        }
    }
    $jsMaleCounts = array_values($maleCounts);
    $jsFemaleCounts = array_values($femaleCounts);
    $jsYearLevels = $uniqueYearLevels;

    $maleGenderCount = 0; $femaleGenderCount = 0;
    foreach ($genderData as $g) {
        if (strtolower($g['gender']) === 'male') $maleGenderCount = (int)$g['count'];
        if (strtolower($g['gender']) === 'female') $femaleGenderCount = (int)$g['count'];
    }

} catch (PDOException $e) {
    if (!isset($_SESSION['error_message_page'])) {
        $_SESSION['error_message_page'] = "Database error fetching chart data: " . htmlspecialchars($e->getMessage());
    }
    $positionNames = $positionCounts = $jsYearLevels = $jsMaleCounts = $jsFemaleCounts = [];
    $maleGenderCount = $femaleGenderCount = 0;
}
?>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Candidate List</h3>
                <?php if ($success_message_from_session): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo htmlspecialchars($success_message_from_session); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message_from_session): ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo htmlspecialchars($error_message_from_session); ?>
                    </div>
                <?php endif; ?>
                <?php if ($info_message_from_session): ?>
                    <div class="alert alert-info alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo htmlspecialchars($info_message_from_session); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 text-center" style="margin-bottom: 20px;">
                <button class="btn btn-success" data-toggle="modal" data-target="#myModal">Add Candidate</button>
                <?php include('add_candidate_modal.php'); ?>
                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal">Delete All Candidates</button>
                <?php include('delete_all_candidate_modal.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 text-right" style="margin-bottom: 10px;">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline-block;">
                    <button type="submit" class="btn btn-primary" name="backup_database">Backup Database</button>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="modal-title" id="myModalLabel PanelTitle">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Candidate List</div>
                    </div>
                </h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>Position</th>
                            <th>Slogan</th>
                            <th>Firstname</th>
                            <th>Lastname</th>
                            <th>Year Level</th>
                            <th>Gender</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt_candidates = $pdo->query("SELECT c.*, p.position_name FROM candidate c LEFT JOIN position p ON c.position = p.position_id ORDER BY c.candidate_id DESC");
                            while ($row_candidate = $stmt_candidates->fetch(PDO::FETCH_ASSOC)) {
                                $candidate_id = $row_candidate['candidate_id'];
                                $position_name_display = htmlspecialchars($row_candidate['position_name'] ?? 'N/A');
                                ?>
                                <tr>
                                    <td width="50"><img src="<?php echo htmlspecialchars($row_candidate['img']); ?>?t=<?php echo time(); ?>" width="50" height="50" class="img-rounded"></td>
                                    <td><?php echo $position_name_display; ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['slogan']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['year_level']); ?></td>
                                    <td><?php echo htmlspecialchars($row_candidate['gender']); ?></td>
                                    <td style="text-align:center">
                                        <a rel="tooltip" title="Delete" id="del_<?php echo $candidate_id; ?>"
                                           href="#delete_user<?php echo $candidate_id; ?>" data-target="#delete_user<?php echo $candidate_id ?>"
                                           data-toggle="modal" class="btn btn-danger btn-outline">
                                            <i class="fa fa-trash-o"></i> Delete
                                        </a>
                                        
                                        <a rel="tooltip" title="Edit"
                                           class="btn btn-success btn-outline edit-candidate-btn"
                                           data-toggle="modal"
                                           data-target="#universalEditCandidateModal"
                                           data-candidate_id="<?php echo htmlspecialchars($row_candidate['candidate_id']); ?>"
                                           data-position_id="<?php echo htmlspecialchars($row_candidate['position']); ?>"
                                           data-slogan="<?php echo htmlspecialchars($row_candidate['slogan']); ?>"
                                           data-firstname="<?php echo htmlspecialchars($row_candidate['firstname']); ?>"
                                           data-lastname="<?php echo htmlspecialchars($row_candidate['lastname']); ?>"
                                           data-year_level="<?php echo htmlspecialchars($row_candidate['year_level']); ?>"
                                           data-gender="<?php echo htmlspecialchars($row_candidate['gender']); ?>"
                                           data-img_path="<?php echo htmlspecialchars($row_candidate['img']); ?>">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <?php include('delete_candidate_modal.php'); // MOVED HERE ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='8'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                                <label for="edit_position_field">Position</label>
                                <select class="form-control" name="position" id="edit_position_field" required>
                                    <option value="" disabled>Select Candidate Group</option>
                                    <?php
                                    if (!empty($all_positions_for_dropdown)) {
                                        foreach ($all_positions_for_dropdown as $pos_item) {
                                            echo "<option value='" . htmlspecialchars($pos_item['position_id']) . "'>" . htmlspecialchars($pos_item['position_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No positions found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_slogan_field">Slogan</label>
                                <input class="form-control" name="slogan" id="edit_slogan_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_firstname_field">Firstname</label>
                                <input class="form-control" name="firstname" id="edit_firstname_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_lastname_field">Lastname</label>
                                <input class="form-control" name="lastname" id="edit_lastname_field" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_year_level_field">Year Level</label>
                                <select class="form-control" name="year_level" id="edit_year_level_field" required>
                                    <option value="">Select Year Level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="5th Year">5th Year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_gender_field">Gender</label>
                                <select class="form-control" name="gender" id="edit_gender_field" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Current Image</label><br>
                                <img src="" id="edit_current_image_preview" width="100" alt="Current Image" class="img-thumbnail" style="margin-bottom:10px; display:none;"><br>
                                <label for="edit_new_image_field">New Image (Optional)</label>
                                <input type="file" name="image" id="edit_new_image_field" class="form-control-file">
                                <small class="form-text text-muted">Leave blank to keep current image. Max 2MB (JPG, PNG, GIF, JPEG).</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" name="update_candidate_submit" class="btn btn-primary">Update Candidate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 40px;">
            <div class="col-lg-12"><div id="positionsBarChart" style="width: 100%; height: 400px;"></div></div>
        </div>
        <div class="row" style="margin-top: 40px;">
            <div class="col-lg-12"><div id="yearLevelLineChart" style="width: 100%; height: 400px;"></div></div>
        </div>
        <div class="row" style="margin-top: 40px; margin-bottom: 50px;">
            <div class="col-lg-12"><div id="genderPieChart" style="width: 100%; height: 400px;"></div></div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        var positionsDataTable = new google.visualization.DataTable();
        positionsDataTable.addColumn('string', 'Position');
        positionsDataTable.addColumn('number', 'Count');
        var positions = <?php echo json_encode($positionNames); ?>;
        var counts = <?php echo json_encode($positionCounts); ?>;
        if (positions && counts && positions.length === counts.length) {
            for (var i = 0; i < positions.length; i++) {
                positionsDataTable.addRow([positions[i], counts[i]]);
            }
        }
        var positionsOptions = { title: 'Number of Candidates by Position', legend: {position: 'none'}, colors: ['#4285F4'], chartArea: {width: '70%'}, vAxis: {minValue: 0} };
        var positionsChart = new google.visualization.BarChart(document.getElementById('positionsBarChart'));
        if (positionsDataTable.getNumberOfRows() > 0) positionsChart.draw(positionsDataTable, positionsOptions);
        else { document.getElementById('positionsBarChart').innerHTML = '<p style="text-align:center; padding-top:50px;">No position data for chart.</p>';}

        var yearLevelDataTable = new google.visualization.DataTable();
        yearLevelDataTable.addColumn('string', 'Year Level');
        yearLevelDataTable.addColumn('number', 'Male');
        yearLevelDataTable.addColumn('number', 'Female');
        var years = <?php echo json_encode($jsYearLevels); ?>;
        var maleDataCounts = <?php echo json_encode($jsMaleCounts); ?>;
        var femaleDataCounts = <?php echo json_encode($jsFemaleCounts); ?>;
         if (years && maleDataCounts && femaleDataCounts && years.length === maleDataCounts.length && years.length === femaleDataCounts.length) {
            for (var j = 0; j < years.length; j++) {
                yearLevelDataTable.addRow([years[j].toString(), maleDataCounts[j] || 0, femaleDataCounts[j] || 0]);
            }
        }
        var yearLevelOptions = { title: 'Candidates by Year Level and Gender', hAxis: {title: 'Year Level'}, vAxis: {title: 'Number of Candidates', minValue: 0}, colors: ['#1E90FF', '#FF69B4'], curveType: 'function', pointSize: 5, legend: {position: 'bottom'} };
        var yearLevelChart = new google.visualization.LineChart(document.getElementById('yearLevelLineChart'));
        if (yearLevelDataTable.getNumberOfRows() > 0) yearLevelChart.draw(yearLevelDataTable, yearLevelOptions);
        else { document.getElementById('yearLevelLineChart').innerHTML = '<p style="text-align:center; padding-top:50px;">No year level data for chart.</p>';}

        var genderDataTable = google.visualization.arrayToDataTable([
            ['Gender', 'Count'],
            ['Male', <?php echo $maleGenderCount; ?>],
            ['Female', <?php echo $femaleGenderCount; ?>]
        ]);
        var genderOptions = { title: 'Candidates Gender Distribution', pieHole: 0.4, colors: ['#3366CC', '#FF6699'] };
        var genderChart = new google.visualization.PieChart(document.getElementById('genderPieChart'));
        if (<?php echo $maleGenderCount + $femaleGenderCount; ?> > 0) genderChart.draw(genderDataTable, genderOptions);
        else { document.getElementById('genderPieChart').innerHTML = '<p style="text-align:center; padding-top:50px;">No gender data for chart.</p>';}
    }
    window.addEventListener('resize', drawCharts);

    $(document).ready(function() {
        $('.edit-candidate-btn').on('click', function() {
            var candidateId = $(this).data('candidate_id');
            var positionId  = $(this).data('position_id');
            var slogan      = $(this).data('slogan');
            var firstname   = $(this).data('firstname');
            var lastname    = $(this).data('lastname');
            var yearLevel   = $(this).data('year_level');
            var gender      = $(this).data('gender');
            var imgPath     = $(this).data('img_path');

            var modal = $('#universalEditCandidateModal');
            modal.find('#edit_candidate_id_field').val(candidateId);
            modal.find('#edit_position_field').val(positionId);
            modal.find('#edit_slogan_field').val(slogan);
            modal.find('#edit_firstname_field').val(firstname);
            modal.find('#edit_lastname_field').val(lastname);
            modal.find('#edit_year_level_field').val(yearLevel);
            modal.find('#edit_gender_field').val(gender);
            modal.find('#edit_current_image_path_field').val(imgPath);
            
            if(imgPath && imgPath !== "" && imgPath !== "upload/") {
                modal.find('#edit_current_image_preview').attr('src', imgPath + '?t=' + new Date().getTime()).show();
            } else {
                modal.find('#edit_current_image_preview').attr('src', '').hide();
            }
            modal.find('#edit_new_image_field').val('');
        });

        $('#universalEditCandidateModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('#edit_current_image_preview').attr('src', '').hide();
        });

        if ($.fn.dataTable && !$.fn.dataTable.isDataTable('#dataTables-example')) {
            $('#dataTables-example').DataTable({
                responsive: true,
            });
        }
    });
</script>
<?php include('script.php'); ?>
</body>
</html>