<?php
// For production, error reporting should ideally be configured in php.ini
// ini_set('display_errors', 0); // Example for production
// error_reporting(E_ALL); // Still log errors, just don't display them
// ini_set('log_errors', 1);
// ini_set('error_log', '/path/to/your/php-error.log'); // Set a log file path

// process_evaluation_save.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'admin/dbcon.php'; // Make sure this path is correct

if (!$pdo) {
    // Log this critical error
    error_log("CRITICAL: PDO database connection object (\$pdo) not found in process_evaluation_save.php.");
    // Set a generic error for the user and redirect.
    $_SESSION['form_message_vote_page'] = "A critical error occurred. Please try again later or contact support.";
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

$role_id = $_SESSION['user_role'] ?? null;
$session_evaluator_user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_evaluation_data'])) {
    $_SESSION['form_message_vote_page'] = "Invalid access method to process evaluation.";
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

if ($role_id != 5 || !$session_evaluator_user_id) {
    $_SESSION['form_message_vote_page'] = 'Unauthorized action. Only faculty can save evaluations.';
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

$candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
$position_id  = filter_input(INPUT_POST, 'position_id', FILTER_VALIDATE_INT);
$evaluator_user_id_from_form = filter_input(INPUT_POST, 'evaluator_user_id', FILTER_VALIDATE_INT);

if (!$candidate_id || !$position_id || !$evaluator_user_id_from_form) {
    $_SESSION['form_message_vote_page'] = "Invalid or missing parameters (candidate, position, or evaluator ID).";
    $_SESSION['form_message_type_vote_page'] = "danger";
    // If IDs are missing, redirecting to specific form is hard, vote.php is safer.
    header("Location: vote.php");
    exit;
}

if ($evaluator_user_id_from_form !== (int)$session_evaluator_user_id) {
    $_SESSION['form_message_vote_page'] = "Evaluator ID mismatch. Cannot save evaluation.";
    $_SESSION['form_message_type_vote_page'] = "danger";
    header("Location: vote.php");
    exit;
}

$score_column_keys = [
    'max_10_service', 'performance_max_10', 'rank_max_5',
    'service_diff_pos_max_10', 'publication_max_5', 'community_max_10',
    'committee_max_5', 'hdp_max_5', 'file_nearness_max_5',
    'colleagues_eval_max_15', 'supervisor_eval_max_20',
];

$criteria_definitions_for_validation = [ // Ensure this matches evaluation_form_page.php
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

$db_values_to_save = [];
$session_scores_to_save = []; // For session update
$validation_errors = [];

foreach ($score_column_keys as $field_name) {
    $details = $criteria_definitions_for_validation[$field_name] ?? ['max' => 999, 'required' => false, 'label' => $field_name]; // Fallback
    $max_score = $details['max'];
    $is_required = $details['required'] ?? false;
    $label = $details['label'];

    $score_value_str = $_POST[$field_name] ?? '';

    if ($score_value_str !== '') {
        if (!is_numeric($score_value_str)) {
            $validation_errors[] = "Invalid score for '" . htmlspecialchars($label) . "' (must be a number).";
        } else {
            $score_value = floatval($score_value_str);
            if ($score_value < 0 || $score_value > $max_score) {
                $validation_errors[] = "Score for '" . htmlspecialchars($label) . "' must be between 0 and " . $max_score . ".";
            }
            $db_values_to_save[$field_name] = $score_value;
            $session_scores_to_save[$field_name] = $score_value;
        }
    } elseif ($is_required) {
        $validation_errors[] = "Score for '" . htmlspecialchars($label) . "' is required.";
        $db_values_to_save[$field_name] = null;
        $session_scores_to_save[$field_name] = null;
    } else {
        $db_values_to_save[$field_name] = null; // Store NULL in DB if empty and not required
        $session_scores_to_save[$field_name] = null;
    }
}
$evaluator_comments = isset($_POST['evaluator_comments']) ? trim(htmlspecialchars($_POST['evaluator_comments'], ENT_QUOTES, 'UTF-8')) : null;
$session_scores_to_save['evaluator_comments'] = $evaluator_comments;
$db_values_to_save['evaluator_comments'] = $evaluator_comments;


if (!empty($validation_errors)) {
    $_SESSION['evaluation_form_message'] = "<strong>Validation Errors:</strong><br>" . implode("<br>", array_map('htmlspecialchars', $validation_errors));
    $_SESSION['evaluation_form_message_type'] = "danger";
    $_SESSION['evaluation_form_old_input'] = $_POST; // Save POST data to repopulate
    header("Location: evaluation_form_page.php?candidate_id={$candidate_id}&position_id={$position_id}");
    exit;
} else {
    try {
        $pdo->beginTransaction();

        $check_stmt = $pdo->prepare("SELECT evaluation_id FROM candidate_evaluations WHERE candidate_id = ? AND evaluator_user_id = ?");
        $check_stmt->execute([$candidate_id, $session_evaluator_user_id]);
        $existing_evaluation_id = $check_stmt->fetchColumn();

        $params_to_bind_for_execute = $db_values_to_save; // Base parameters (scores, comments)

        if ($existing_evaluation_id) {
            $update_set_parts = [];
            foreach (array_keys($db_values_to_save) as $col) {
                $update_set_parts[] = "{$col} = :{$col}";
            }
            $sql = "UPDATE candidate_evaluations SET " . implode(", ", $update_set_parts) . ", evaluation_date = NOW()
                    WHERE evaluation_id = :evaluation_id";
            $params_to_bind_for_execute['evaluation_id'] = $existing_evaluation_id;
        } else {
            $params_to_bind_for_execute['candidate_id'] = $candidate_id;
            $params_to_bind_for_execute['evaluator_user_id'] = $session_evaluator_user_id;

            $insert_columns_array = array_merge(['candidate_id', 'evaluator_user_id'], array_keys($db_values_to_save));
            $sql_columns = implode(", ", $insert_columns_array);
            $sql_placeholders = ":" . implode(", :", $insert_columns_array);

            $sql = "INSERT INTO candidate_evaluations ({$sql_columns}, evaluation_date)
                    VALUES ({$sql_placeholders}, NOW())";
        }
        
        $stmt_save = $pdo->prepare($sql);
        
        if ($stmt_save->execute($params_to_bind_for_execute)) {
            $pdo->commit();
            $_SESSION['form_message_vote_page'] = 'Evaluation data has been successfully saved!';
            $_SESSION['form_message_type_vote_page'] = "success";

            if (!isset($_SESSION['evaluations'])) $_SESSION['evaluations'] = [];
            if (!isset($_SESSION['evaluations'][$position_id])) $_SESSION['evaluations'][$position_id] = [];
            $_SESSION['evaluations'][$position_id][$candidate_id] = $session_scores_to_save;
            unset($_SESSION['evaluation_form_old_input']);

        } else {
            $pdo->rollBack();
            $errorInfo = $stmt_save->errorInfo();
            $_SESSION['form_message_vote_page'] = "Failed to save evaluation to database. DB Error: " . htmlspecialchars($errorInfo[2]);
            $_SESSION['form_message_type_vote_page'] = "danger";
            error_log("DB Save Error (Eval): " . print_r($errorInfo, true) . " SQL: " . $sql . " Params: " . print_r($params_to_bind_for_execute, true));
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['form_message_vote_page'] = "Database error during evaluation submission: " . htmlspecialchars($e->getMessage());
        $_SESSION['form_message_type_vote_page'] = "danger";
        error_log("PDOException in process_evaluation_save: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
    }
    
    header("Location: vote.php"); // Redirect back to the main list
    exit;
}
?>