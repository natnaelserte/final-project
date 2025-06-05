<?php
// vote.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('sess.php'); 
include('head.php');
require_once 'admin/dbcon.php';

$current_view_type = 'Student'; 
$role_id = $_SESSION['user_role'] ?? null;
$user_id_logged_in = $_SESSION['user_id'] ?? null;

if ($role_id == 3) { 
    $current_view_type = 'Student'; 
} elseif ($role_id == 5) { 
    $current_view_type = 'Faculty'; 
} elseif ($role_id === null) {
    error_log("Vote.php: user_role not found in session. Defaulting to Student view.");
} else {
    error_log("Vote.php: Unhandled user_role '{$role_id}'. Defaulting to Student view.");
}

// Determine form action based on role
$form_action_url = 'vote_review.php'; // Default action
if ($role_id == 3) {
    // Student voters might go to a combined review & submit page, or directly to final processing if no review step.
    // For this example, let's assume they go to vote_review.php which then shows their votes and a submit button.
    // If you want Role 3 to skip review and POST directly to a final processing script, change this.
    // For now, both will go to vote_review.php which will behave differently based on role.
    $form_action_url = 'vote_result.php'; 
} elseif ($role_id == 5) {
    $form_action_url = 'vote_review.php'; 
}


$all_positions_data_for_js_validation = []; 
$display_positions = [];
$displayed_candidates_for_faculty_eval = []; 
$db_error = null;

try {
    $positions_query = $pdo->query("SELECT p.position_id, p.position_name FROM `position` p ORDER BY p.position_id ASC");
    $all_raw_positions = $positions_query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_raw_positions as $position) {
        $candidate_check_query = $pdo->prepare("SELECT candidate_id FROM `candidate` WHERE `position` = ? AND `candidate_type` = ?");
        $candidate_check_query->execute([$position['position_id'], $current_view_type]);
        $candidates_for_this_pos = $candidate_check_query->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($candidates_for_this_pos)) {
            $position_name_lower = strtolower(str_replace([' ', '&', '/'], '_', $position['position_name']));
            $display_positions[] = [
                'id' => $position['position_id'],
                'name' => htmlspecialchars($position['position_name']),
                'name_lower' => $position_name_lower,
                'candidate_count' => count($candidates_for_this_pos)
            ];
            if ($role_id != 5) { 
                $all_positions_data_for_js_validation[] = [
                    'name_lower' => $position_name_lower,
                    'display_name' => htmlspecialchars($position['position_name'])
                ];
            }
            // For Role 5, we need the list of candidates they see to check against session evaluations later
            if ($role_id == 5) {
                if(!isset($_SESSION['displayed_candidates_for_eval'][$position['position_id']])) {
                     $_SESSION['displayed_candidates_for_eval'][$position['position_id']] = [];
                }
                $_SESSION['displayed_candidates_for_eval'][$position['position_id']] = $candidates_for_this_pos;
            }
        }
    }
} catch (PDOException $e) {
    $db_error = "Error fetching position data: " . htmlspecialchars($e->getMessage());
    error_log("Vote.php DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($role_id == 5) ? "Evaluate Candidates" : "Cast Your Vote"; ?> - <?php echo htmlspecialchars($current_view_type); ?> View</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; color: #333; margin: 0; line-height: 1.6; }
        #wrapper_1 { display: flex; }
        .container_vote_content { flex-grow: 1; padding: 25px 15px; max-width: 1200px; margin: 0 auto; }
        .main-voting-title { color: #2c3e50; font-weight: 700; margin-bottom: 2.5rem !important; text-align: center; font-size: 2.25rem; }
        .position-main-title { font-size: 1.8rem; font-weight: 500; color: #34495e; margin-bottom: 0.5rem; text-align: center; }
        .position-main-subtitle { font-size: 0.95rem; color: #7f8c8d; margin-bottom: 1.5rem; text-align: center; }
        .position-section-container { margin-bottom: 3rem; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .candidate-display-card { background-color: #fff; border: 1px solid #eef2f7; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-align: center; transition: all 0.3s; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
        .candidate-display-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.12); transform: translateY(-4px); }
        .candidate-card-image-wrapper { position: relative; background-color: #f7f9fc; padding: 20px 20px 0 20px; height: 200px; display: flex; align-items: center; justify-content: center; }
        .candidate-card-image-wrapper img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 6px; }
        .candidate-availability-badge { position: absolute; top: 15px; right: 15px; background-color: #2ecc71; color: white; padding: 5px 12px; font-size: 0.7rem; font-weight: 500; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .candidate-card-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; text-align: center; }
        .candidate-card-name { font-size: 1.2rem; font-weight: 700; color: #2c3e50; margin-top: 0; margin-bottom: 4px; }
        .candidate-card-position { font-size: 0.85rem; color: #7f8c8d; margin-bottom: 15px; font-style: italic; }
        .candidate-card-details-grid { display: flex; justify-content: space-around; margin-bottom: 15px; padding: 12px 0; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; }
        .candidate-detail-block .label { display: block; font-size: 0.7rem; color: #95a5a6; text-transform: uppercase; margin-bottom: 3px; }
        .candidate-detail-block .value { display: block; font-size: 0.9rem; font-weight: 500; color: #34495e; }
        .candidate-card-action-buttons { margin-top: 15px; margin-bottom: 10px; display: flex; justify-content: center; gap: 10px; }
        .btn-see-more, .btn-evaluate { padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; text-decoration: none !important; color: white !important; transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; cursor: pointer; border: 1px solid transparent; display: inline-block; text-align: center; }
        .btn-see-more { background-color: #5bc0de; border-color: #46b8da; }
        .btn-see-more:hover { background-color: #31b0d5; }
        .btn-evaluate { background-color: #f0ad4e; border-color: #eea236; }
        .btn-evaluate:hover { background-color: #ec971f; }
        .btn-evaluate.evaluated { background-color: #28a745 !important; border-color: #218838 !important; }
        .btn-evaluate.evaluated:hover { background-color: #1e7e34 !important; }
        .candidate-card-footer-action { margin-top: auto; padding-top: 15px; } /* For voters */
        .vote-checkbox-label { display: inline-block; padding: 10px 20px; background-color: #ecf0f1; color: #34495e; border: 1px solid #dce4ec; border-radius: 5px; cursor: pointer; transition: all 0.2s ease-in-out; font-weight: 500; width: 100%; box-sizing: border-box; }
        .candidate-vote-input:checked + .vote-checkbox-label { background-color: #3498db; color: white; border-color: #2980b9; }
        .candidate-vote-input:disabled + .vote-checkbox-label { background-color: #f7f9fc; color: #bdc3c7; cursor: not-allowed; border-color: #ecf0f1; }
        .candidate-vote-input { opacity: 0; position: absolute; width: 0; height: 0; }
        .vote-submit-button { background-color: #27ae60; color: white; font-size: 1.15rem; padding: 12px 35px; font-weight: 500; border: none; border-radius: 5px; transition: background-color 0.2s ease; }
        .vote-submit-button:hover { background-color: #229954; color: white; }
        .row.d-flex { display: flex; flex-wrap: wrap; }
        .col-lg-4.d-flex, .col-md-6.d-flex { display: flex; flex-direction: column; }
        @media (max-width: 768px) { .main-voting-title { font-size: 1.9rem; } .position-main-title { font-size: 1.6rem; } .candidate-card-name { font-size: 1.1rem; } .candidate-card-image-wrapper { height: 180px; } .candidate-card-details-grid { flex-direction: column; gap: 8px; } }
        @media (max-width: 576px) { .container_vote_content { padding: 15px 10px; } .main-voting-title { font-size: 1.7rem; } .position-main-title { font-size: 1.4rem; } .candidate-card-name { font-size: 1rem; } .candidate-card-image-wrapper { height: 160px; padding: 15px 15px 0 15px; } .vote-checkbox-label { padding: 8px 15px; font-size: 0.9rem; } .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; } .col-lg-4, .col-md-6 { padding-right: 15px; padding-left: 15px; } }
        @media (max-width: 400px) { .row > .col-lg-4, .row > .col-md-6 { flex: 0 0 100%; max-width: 100%; } }
    </style>
</head>
<body>
    <?php include 'side_bar.php'; ?>

    <div id="wrapper_1">
        <div class="container_vote_content">
            <h1 class="main-voting-title">
                <?php echo ($role_id == 5) ? "Evaluate Candidates" : "Cast Your Vote"; ?> 
                (<?php echo htmlspecialchars($current_view_type); ?> Candidates)
            </h1>

            <?php
            if (isset($_SESSION['form_message_vote_page'])): 
            ?>
                <div class="alert alert-<?php echo $_SESSION['form_message_type_vote_page'] ?? 'info'; ?> alert-dismissible fade show mb-4" role="alert">
                    <?php echo $_SESSION['form_message_vote_page']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
            <?php 
                unset($_SESSION['form_message_vote_page'], $_SESSION['form_message_type_vote_page']); 
            endif; 
            ?>

            <?php if ($db_error): ?>
                <div class="alert alert-danger text-center"><?php echo $db_error; ?></div>
            <?php elseif (empty($display_positions)): ?>
                <div class="alert alert-info text-center p-4">
                    No positions with eligible <?php echo strtolower(htmlspecialchars($current_view_type)); ?> candidates available at this time.
                </div>
            <?php else: ?>
                <form method="POST" action="<?php echo $form_action_url; ?>" id="votingForm">
                    <?php foreach ($display_positions as $position_item): ?>
                        <div class="position-section-container" 
                             data-position-id="<?php echo $position_item['id']; ?>"
                             data-position-name-lower="<?php echo $position_item['name_lower']; ?>">
                            <h2 class="position-main-title"><?php echo $position_item['name']; ?></h2>
                            <p class="position-main-subtitle">
                                <?php echo ($role_id == 5) ? "Please evaluate each candidate for this role." : "Select your preferred " . strtolower(htmlspecialchars($current_view_type)) . " candidate for this role."; ?>
                            </p>
                            
                            <div class="row d-flex <?php echo ($position_item['candidate_count'] < 3 && $position_item['candidate_count'] > 0) ? 'justify-content-center' : ''; ?>">
                                <?php
                                $candidate_query = $pdo->prepare(
                                    "SELECT candidate_id, firstname, lastname, candidate_type, gender, img, slogan 
                                     FROM `candidate` 
                                     WHERE `position` = ? AND `candidate_type` = ? 
                                     ORDER BY lastname ASC, firstname ASC"
                                );
                                $candidate_query->execute([$position_item['id'], $current_view_type]);
                                
                                while ($candidate = $candidate_query->fetch(PDO::FETCH_ASSOC)):
                                    $candidate_id_val = htmlspecialchars($candidate['candidate_id']);
                                    $default_image_path = "admin2/default_candidate_image.png"; 
                                    $img_path_val = "admin2/" . (!empty($candidate['img']) ? htmlspecialchars($candidate['img']) : basename($default_image_path));
                                ?>
                                    <div class="col-lg-4 col-md-6 mb-4 d-flex candidate-entry" data-candidate-id="<?php echo $candidate_id_val; ?>">
                                        <div class="candidate-display-card w-100">
                                            <div class="candidate-card-image-wrapper">
                                                <?php if (!empty($candidate['img']) && file_exists($img_path_val) && !is_dir($img_path_val)): ?>
                                                    <img src="<?php echo $img_path_val; ?>?t=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($candidate['firstname']); ?>">
                                                <?php elseif (file_exists($default_image_path)): ?>
                                                    <img src="<?php echo $default_image_path; ?>" alt="Default Candidate Image">
                                                <?php else: ?>
                                                    <div style="width:100px; height:100px; background:#eee; display:flex; align-items:center; justify-content:center; color:#aaa; border-radius:6px;">No Image</div>
                                                <?php endif; ?>
                                                <span class="candidate-availability-badge">Eligible</span>
                                            </div>
                                            <div class="candidate-card-info">
                                                <h5 class="candidate-card-name"><?php echo htmlspecialchars($candidate['firstname']) . ' ' . htmlspecialchars($candidate['lastname']); ?></h5>
                                                <p class="candidate-card-position"><?php echo $position_item['name']; ?></p>

                                                <div class="candidate-card-details-grid">
                                                    <div class="candidate-detail-block">
                                                        <span class="label">Type</span>
                                                        <span class="value"><?php echo htmlspecialchars($candidate['candidate_type']); ?></span>
                                                    </div>
                                                    <div class="candidate-detail-block">
                                                        <span class="label">Slogan</span>
                                                        <span class="value"><?php echo htmlspecialchars($candidate['slogan']); ?></span>
                                                    </div>
                                                </div>

                                                <div class="candidate-card-action-buttons">
                                                   <a href="#" class="btn btn-sm btn-see-more" data-toggle="modal"
                                                      data-target="#candidateDetailsModal" data-candidate-id="<?php echo $candidate_id_val; ?>">See More...</a>
                                                   
                                                   <?php if ($role_id == 5):
                                                        $evaluate_link = "evaluation_form_page.php?candidate_id=" . $candidate_id_val . "&position_id=" . $position_item['id'];
                                                        
                                                        $is_evaluated_class = '';
                                                        $evaluate_button_text = 'Evaluate';
                                                        if (isset($_SESSION['evaluations'][$position_item['id']][$candidate_id_val])) {
                                                            $is_evaluated_class = 'evaluated'; 
                                                            $evaluate_button_text = '<i class="fas fa-check-circle"></i> Evaluated';
                                                        }
                                                   ?>
                                                       <a href="<?php echo $evaluate_link; ?>" 
                                                          class="btn btn-sm btn-evaluate <?php echo $is_evaluated_class; ?>"
                                                          id="evalBtn_pos<?php echo $position_item['id']; ?>_cand<?php echo $candidate_id_val; ?>">
                                                           <?php echo $evaluate_button_text; ?>
                                                       </a>
                                                   <?php endif; ?>
                                                </div>

                                                <?php if ($role_id != 5): ?>
                                                <div class="candidate-card-footer-action">
                                                    <input type="checkbox"
                                                           value="<?php echo $candidate_id_val; ?>"
                                                           name="<?php echo $position_item['name_lower'] . '_id'; ?>"
                                                           id="cand_<?php echo $candidate_id_val; ?>_pos_<?php echo $position_item['id']; ?>"
                                                           class="candidate-vote-input"
                                                           data-position-group="<?php echo $position_item['name_lower']; ?>">
                                                    <label for="cand_<?php echo $candidate_id_val; ?>_pos_<?php echo $position_item['id']; ?>" class="vote-checkbox-label">
                                                        Select to Vote
                                                    </label>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-5 mb-4">
                        <button class="btn vote-submit-button" type="submit" name="submit_ballot_action"> 
                            <?php echo ($role_id == 5) ? "Proceed to Review Evaluations" : "Review Ballot"; ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Candidate Details Modal (for "See More..." - Evidence) -->
    <div class="modal fade" id="candidateDetailsModal" tabindex="-1" role="dialog" aria-labelledby="candidateDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="candidateDetailsModalLabel">Details & Evidence</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></div><div class="modal-body" style="padding: 0.5rem;"><div id="candidateDetailsContent"><div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><p class="mt-2">Loading...</p></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div>
    </div>

    <?php
    include('script.php'); 
    include('footer.php');
    ?>

    <script type="text/javascript">
    const POSITIONS_FOR_JS_VALIDATION = <?php echo json_encode($all_positions_data_for_js_validation); ?>;
    const IS_ROLE_5_USER = <?php echo json_encode($role_id == 5); ?>;
    
    $(document).ready(function() {
        if (!IS_ROLE_5_USER) {
            // Vote Checkbox Logic
            $('.candidate-vote-input').on('change', function() {
                const groupName = $(this).data('position-group');
                const $checkboxesInGroup = $(`.candidate-vote-input[data-position-group="${groupName}"]`);
                if ($(this).is(':checked')) {
                    $checkboxesInGroup.not(this).prop('checked', false).prop('disabled', true);
                    $(this).prop('disabled', false);
                } else {
                    $checkboxesInGroup.prop('disabled', false);
                }
            });
        }

        // Voting Form Submission Logic
        $('#votingForm').on('submit', function(event) {
            if (!IS_ROLE_5_USER) {
                // Validation for regular voters
                let allVotesMade = true;
                let firstMissingPosition = null;
                POSITIONS_FOR_JS_VALIDATION.forEach(function(position) {
                    const groupName = position.name_lower;
                    if ($(`.candidate-vote-input[data-position-group="${groupName}"]:checked`).length === 0) {
                        allVotesMade = false;
                        if (!firstMissingPosition) { firstMissingPosition = position.display_name; }
                    }
                });
                if (!allVotesMade) {
                    event.preventDefault();
                    alert('Please select a candidate for the "' + firstMissingPosition + '" position before submitting for review.');
                    return false;
                }
            } else {
                // For Role 5, server-side check for evaluation completeness happens in vote_review.php
                console.log("Role 5: Proceeding to review. Server will verify evaluation completeness.");
            }
            // Form will submit to the URL set in $form_action_url
        });

        // "See More" Modal AJAX
        $('#candidateDetailsModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var candidateId = button.data('candidate-id'); 
            var modal = $(this);
            modal.find('#candidateDetailsModalLabel').text('Details & Evidence');
            modal.find('#candidateDetailsContent').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><p class="mt-2">Loading...</p></div>');
            $.ajax({
                url: 'candidate_details.php', type: 'GET', data: {id: candidateId, modal: 1}, 
                success: function(response) { modal.find('#candidateDetailsContent').html(response); },
                error: function(jqXHR, textStatus, errorThrown) { 
                    console.error("AJAX Error (See More):", textStatus, errorThrown, jqXHR.responseText);
                    modal.find('#candidateDetailsContent').html('<div class="alert alert-danger m-3">Error loading details. Please try again.</div>');
                }
            });
        });
        // No AJAX for "Evaluate" modal on this page, as it's a direct link now.
    });
    </script>
</body>
</html>