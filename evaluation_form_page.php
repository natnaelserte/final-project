<?php
include('sess.php'); 
include('head.php');
require_once 'admin/dbcon.php';

// --- GET PARAMETERS FIRST ---
$candidate_id = filter_input(INPUT_GET, 'candidate_id', FILTER_VALIDATE_INT);
$position_id  = filter_input(INPUT_GET, 'position_id', FILTER_VALIDATE_INT);

// --- SESSION AND ROLE CHECKS ---
$role_id = $_SESSION['user_role'] ?? null;
$evaluator_user_id = $_SESSION['user_id'] ?? null;

if ($role_id != 5 || !$evaluator_user_id) {
    $_SESSION['form_message_vote_page'] = "Unauthorized access to evaluation page.";
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

if (!$candidate_id || !$position_id) { 
    $_SESSION['form_message_vote_page'] = "Missing candidate or position ID for evaluation.";
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

// --- DEFINE CRITERIA EARLY ---
// This is needed for the $_SESSION['evaluation_form_old_input'] loop
$criteria_definitions = [
    'max_10_service' => ['label' => 'Service in Current Role', 'max' => 10, 'required' => true],
    'performance_max_10' => ['label' => 'Performance Evaluation', 'max' => 10, 'required' => true],
    'rank_max_5' => ['label' => 'Academic Rank', 'max' => 5, 'required' => true],
    'service_diff_pos_max_10' => ['label' => 'Service in Different Positions', 'max' => 10, 'required' => false],
    'publication_max_5' => ['label' => 'Publications/Research', 'max' => 5, 'required' => false],
    'community_max_10' => ['label' => 'Community Service', 'max' => 10, 'required' => false],
    'committee_max_5' => ['label' => 'Committee Involvement', 'max' => 5, 'required' => false],
    'hdp_max_5' => ['label' => 'HDP/Professional Development', 'max' => 5, 'required' => false],
    'file_nearness_max_5' => ['label' => 'File Nearness/Completeness', 'max' => 5, 'required' => true],
    'colleagues_eval_max_15' => ['label' => 'Colleagues Evaluation', 'max' => 15, 'required' => true],
    'supervisor_eval_max_20' => ['label' => 'Supervisor Evaluation', 'max' => 20, 'required' => true],
];

// --- FETCH NAMES ---
$candidate_name = "Unknown Candidate";
$position_name = "Unknown Position";
try {
    $stmt_cand = $pdo->prepare("SELECT firstname, lastname FROM candidate WHERE candidate_id = ?");
    $stmt_cand->execute([$candidate_id]);
    $cand_data = $stmt_cand->fetch(PDO::FETCH_ASSOC);
    if ($cand_data) $candidate_name = htmlspecialchars($cand_data['firstname'] . ' ' . $cand_data['lastname']);

    $stmt_pos = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
    $stmt_pos->execute([$position_id]);
    $pos_data = $stmt_pos->fetch(PDO::FETCH_ASSOC);
    if ($pos_data) $position_name = htmlspecialchars($pos_data['position_name']);

} catch (PDOException $e) {
    error_log("EvalFormPage DB Error: " . $e->getMessage());
}

// --- PREPARE FORM DATA (using defined $candidate_id, $position_id, and $criteria_definitions) ---
$form_data_to_display = $_SESSION['evaluations'][$position_id][$candidate_id] ?? [];
if (isset($_SESSION['evaluation_form_old_input'])) {
    $old_input = $_SESSION['evaluation_form_old_input'];
    if (($old_input['candidate_id'] ?? null) == $candidate_id && ($old_input['position_id'] ?? null) == $position_id) {
        // Loop through defined criteria to safely access keys from $old_input
        foreach (array_keys($criteria_definitions) as $field_name) { // Use keys from $criteria_definitions
            if (isset($old_input[$field_name])) {
                $form_data_to_display[$field_name] = $old_input[$field_name];
            }
        }
        if (isset($old_input['evaluator_comments'])) {
            $form_data_to_display['evaluator_comments'] = $old_input['evaluator_comments'];
        }
    }
    unset($_SESSION['evaluation_form_old_input']); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate: <?php echo $candidate_name; ?> (<?php echo $position_name; ?>)</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f7f6; padding-top: 20px; padding-bottom: 20px; }
        .evaluation-container { max-width: 800px; margin: auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .evaluation-header h2 { color: #333; margin-bottom: 5px; }
        .evaluation-header p { color: #666; margin-bottom: 20px; font-size: 1.1em; }
        .form-group label { font-weight: 500; margin-bottom: .3rem; }
        .form-control-sm { height: calc(1.5em + .5rem + 2px); padding: .25rem .5rem; font-size: .875rem; }
        .btn-save-evaluation { padding: 10px 25px; font-size: 1rem; }
        .alert { margin-top: 15px; margin-bottom: 20px; } /* Added margin-bottom */
        .form-row > .col-md-6 { padding-right: 10px; padding-left: 10px; }
        .form-row { margin-right: -10px; margin-left: -10px; }
        .text-danger { color: #dc3545 !important; }
        /* Basic Bootstrap validation styles (add these if not using full Bootstrap CSS) */
        .was-validated .form-control:invalid, .form-control.is-invalid { border-color: #dc3545; }
        .was-validated .form-control:valid, .form-control.is-valid { border-color: #28a745; }
    </style>
</head>
<body>
    <div class="evaluation-container">
        <div class="evaluation-header text-center">
            <h2>Candidate Evaluation</h2>
            <p><strong><?php echo $candidate_name; ?></strong> for position: <strong><?php echo $position_name; ?></strong></p>
        </div>

        <?php if (isset($_SESSION['evaluation_form_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['evaluation_form_message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['evaluation_form_message']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <?php unset($_SESSION['evaluation_form_message'], $_SESSION['evaluation_form_message_type']); ?>
        <?php endif; ?>

        <form method="POST" action="process_evaluation_save.php" id="evaluationFormPage" novalidate> 
            <input type="hidden" name="candidate_id" value="<?php echo $candidate_id; ?>">
            <input type="hidden" name="position_id" value="<?php echo $position_id; ?>">
            <input type="hidden" name="evaluator_user_id" value="<?php echo $evaluator_user_id; ?>">
            <input type="hidden" name="return_to_vote_page" value="1">

            <div class="form-row">
                <?php foreach ($criteria_definitions as $field_name => $details): 
                    $is_field_required = $details['required'] ?? false;
                ?>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="<?php echo $field_name; ?>">
                                <?php echo htmlspecialchars($details['label']); ?> 
                                <span class="text-muted small">(Max <?php echo $details['max']; ?>)</span>
                                <?php if ($is_field_required): ?><span class="text-danger">*</span><?php endif; ?>:
                            </label>
                            <input type="number" class="form-control form-control-sm" 
                                   id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>"
                                   min="0" max="<?php echo $details['max']; ?>" step="0.1" 
                                   value="<?php echo isset($form_data_to_display[$field_name]) && is_numeric($form_data_to_display[$field_name]) ? htmlspecialchars( (string) $form_data_to_display[$field_name]) : ''; ?>"
                                   placeholder="0-<?php echo $details['max']; ?>"
                                   <?php if ($is_field_required) echo 'required'; ?> >
                            <div class="invalid-feedback"> 
                                Please provide a valid score.
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group mb-3">
                <label for="evaluator_comments_page">Overall Comments (Optional):</label>
                <textarea class="form-control" id="evaluator_comments_page" name="evaluator_comments" rows="5" placeholder="Provide your overall feedback here..."><?php echo isset($form_data_to_display['evaluator_comments']) ? htmlspecialchars($form_data_to_display['evaluator_comments']) : ''; ?></textarea>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" name="submit_evaluation_data" class="btn btn-primary btn-save-evaluation"><i class="fas fa-save"></i> Save Evaluation</button>
                <a href="vote.php" class="btn btn-outline-secondary ml-2">Cancel & Back to List</a>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.querySelectorAll('#evaluationFormPage.needs-validation, #evaluationFormPage'); // Target specific form
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated'); 
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>