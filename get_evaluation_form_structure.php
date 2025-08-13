<?php
header('Content-Type: application/json'); // This script returns JSON

$response = ['success' => false, 'form_html' => '', 'message' => 'An error occurred.', 'candidate_name' => 'Candidate'];

// Role and User ID checks from session (set by login and sess.php on vote.php)
$role_id = $_SESSION['user_role'] ?? null;
$evaluator_user_id = $_SESSION['user_id'] ?? null;

if ($role_id != 5 || !$evaluator_user_id) {
    $response['message'] = 'Unauthorized access for evaluation.';
    echo json_encode($response);
    exit;
}

// Fetch parameters sent by the AJAX call from vote.php
$candidate_id = filter_input(INPUT_GET, 'candidate_id', FILTER_VALIDATE_INT);
$position_id  = filter_input(INPUT_GET, 'position_id', FILTER_VALIDATE_INT);

// Validate parameters received from AJAX
if (!$candidate_id) {
    $response['message'] = 'AJAX Error: Missing or invalid Candidate ID parameter.';
    error_log("GetEvalForm AJAX: Missing candidate_id. GET: " . print_r($_GET, true));
    echo json_encode($response);
    exit;
}
if (!$position_id) {
    $response['message'] = 'AJAX Error: Missing or invalid Position ID parameter.';
    error_log("GetEvalForm AJAX: Missing position_id. GET: " . print_r($_GET, true));
    echo json_encode($response);
    exit;
}

// Fetch candidate's name for the form title/header
try {
    $stmt_cand = $pdo->prepare("SELECT firstname, lastname FROM candidate WHERE candidate_id = ?");
    $stmt_cand->execute([$candidate_id]);
    $cand_data = $stmt_cand->fetch(PDO::FETCH_ASSOC);
    if ($cand_data) {
        $response['candidate_name'] = htmlspecialchars($cand_data['firstname'] . ' ' . $cand_data['lastname']);
    } else {
        $response['message'] = 'Candidate not found (ID: ' . $candidate_id . ').';
        echo json_encode($response);
        exit;
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error fetching candidate name.';
    error_log("GetEvalForm Error (CandName Fetch AJAX): " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// Retrieve previously saved scores for this candidate & position from session
$saved_evaluation_data = $_SESSION['evaluations'][$position_id][$candidate_id] ?? [];

// Define criteria (ensure this list is comprehensive for your needs)
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

ob_start(); // Start output buffering to capture the form HTML
?>
<form id="saveEvaluationToSessionForm" class="p-1"> 
    <input type="hidden" name="candidate_id" value="<?php echo $candidate_id; ?>">
    <input type="hidden" name="position_id" value="<?php echo $position_id; ?>">
    <input type="hidden" name="evaluator_user_id" value="<?php echo $evaluator_user_id; ?>"> 

    <p class="text-muted small">Please provide scores for the following criteria. Fields can be left blank if not applicable unless marked with <span class="text-danger">*</span>.</p>

    <div class="row">
        <?php 
        foreach ($criteria_definitions as $field_name => $details): 
            $is_field_required_html = $details['required'] ?? false;
        ?>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="<?php echo $field_name; ?>_modal" class="form-label" style="font-weight: 500;">
                        <?php echo htmlspecialchars($details['label']); ?> 
                        <span class="text-muted">(Max <?php echo $details['max']; ?>)</span>
                        <?php if ($is_field_required_html): ?><span class="text-danger">*</span><?php endif; ?>:
                    </label>
                    <input type="number" class="form-control form-control-sm" 
                           id="<?php echo $field_name; ?>_modal" name="<?php echo $field_name; ?>"
                           min="0" max="<?php echo $details['max']; ?>" step="0.1" 
                           value="<?php echo isset($saved_evaluation_data[$field_name]) && is_numeric($saved_evaluation_data[$field_name]) ? htmlspecialchars($saved_evaluation_data[$field_name]) : ''; ?>"
                           placeholder="Score (0-<?php echo $details['max']; ?>)"
                           <?php /* HTML5 required is handled by JS/server for better UX for now, but can be added */ ?>>
                </div>
            </div>
        <?php 
        endforeach; 
        ?>
    </div>

    <div class="form-group mb-3">
        <label for="evaluator_comments_modal_<?php echo $candidate_id; ?>" class="form-label" style="font-weight: 500;">Overall Comments (Optional):</label>
        <textarea class="form-control form-control-sm" id="evaluator_comments_modal_<?php echo $candidate_id; ?>" name="evaluator_comments" rows="4" placeholder="Your comments..."><?php echo isset($saved_evaluation_data['evaluator_comments']) ? htmlspecialchars($saved_evaluation_data['evaluator_comments']) : ''; ?></textarea>
    </div>

    <div class="text-right mt-4">
        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Save Evaluation</button>
    </div>
</form>
<?php
$response['form_html'] = ob_get_clean();
$response['success'] = true;
// $response['message'] = 'Form loaded.'; // This is usually not needed if success is true and form_html is populated

echo json_encode($response);
?>