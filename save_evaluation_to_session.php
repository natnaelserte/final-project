<?php
// save_evaluation_to_session.php

// RIGOROUS SESSION START: Ensure this is at the very top, before ANY output.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Send JSON response

/ --- START INTENSE DEBUG ---
error_log("--- SAVE_EVAL_SESSION START ---");
error_log("SAVE_EVAL_SESSION: Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("SAVE_EVAL_SESSION: Received POST Data: " . print_r($_POST, true));
error_log("SAVE_EVAL_SESSION: Current SESSION at save time: " . print_r($_SESSION, true));

$evaluator_user_id_from_form_in_save = filter_input(INPUT_POST, 'evaluator_user_id', FILTER_VALIDATE_INT);
$session_evaluator_user_id_in_save = $_SESSION['user_id'] ?? 'SESSION_USER_ID_IS_NULL_AT_SAVE'; // More explicit

error_log("SAVE_EVAL_SESSION: ID from Form (POSTed 'evaluator_user_id'): " . ($evaluator_user_id_from_form_in_save ?? 'NOT_IN_POST_OR_INVALID'));
error_log("SAVE_EVAL_SESSION: ID from Current Session (SESSION['user_id']): " . $session_evaluator_user_id_in_save);
// --- END INTENSE DEBUG ---

$response = ['success' => false, 'message' => 'An unexpected error occurred.']; // Initialize response

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

// 1. Check if user is logged in and is a faculty member
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 5) {
    $response['message'] = 'Authentication error or unauthorized role. Please log in as faculty.';
    error_log("SaveEval: Auth error. UserID: " . ($_SESSION['user_id'] ?? 'N/A') . ", Role: " . ($_SESSION['user_role'] ?? 'N/A'));
    echo json_encode($response);
    exit;
}

// 2. Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method. POST request required.";
    echo json_encode($response);
    exit;
}

// 3. Retrieve and validate IDs
$candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
$position_id  = filter_input(INPUT_POST, 'position_id', FILTER_VALIDATE_INT);
$evaluator_user_id_from_form = filter_input(INPUT_POST, 'evaluator_user_id', FILTER_VALIDATE_INT); // From the hidden field in the form

// This is the crucial part: get the user_id from the CURRENT session
$session_evaluator_user_id = $_SESSION['user_id']; // No '?? null' here, we already checked for its existence above.

if (!$candidate_id || !$position_id || !$evaluator_user_id_from_form) {
    $response['message'] = "Critical Error: Missing candidate, position, or evaluator ID from the submitted form.";
    error_log("SaveEval: Missing IDs from POST. POST Data: " . print_r($_POST, true));
    echo json_encode($response);
    exit;
}

// THE MISMATCH CHECK:
if ($evaluator_user_id_from_form !== $session_evaluator_user_id) {
    $response['message'] = "Evaluator ID mismatch. Your session might have changed or expired. Please try reloading the voting page and evaluating again.";
    // Log detailed information for your debugging
    error_log("SaveEval Critical: Evaluator ID mismatch!");
    error_log("    ID from Form (hidden input, generated earlier): " . $evaluator_user_id_from_form);
    error_log("    ID from Current Session (at time of save): " . $session_evaluator_user_id);
    error_log("    Full current session: " . print_r($_SESSION, true));
    echo json_encode($response);
    exit;
}

// --- CRITERIA DEFINITIONS --- (Ensure this is identical to get_evaluation_form_structure.php)
$criteria_definitions = [
    'max_10_service' => ['max' => 10], 'performance_max_10' => ['max' => 10], 'rank_max_5' => ['max' => 5],
    'service_diff_pos_max_10' => ['max' => 10], 'publication_max_5' => ['max' => 5], 'community_max_10' => ['max' => 10],
    'committee_max_5' => ['max' => 5], 'hdp_max_5' => ['max' => 5], 'file_nearness_max_5' => ['max' => 5],
    'colleagues_eval_max_15' => ['max' => 15], 'supervisor_eval_max_20' => ['max' => 20],
];
// --- END CRITERIA DEFINITIONS ---

$scores_to_save = [];
$validation_errors = [];

foreach ($criteria_definitions as $field_name => $details) {
    $max_score = $details['max'];
    // Check if the field was submitted in the POST data
    if (isset($_POST[$field_name])) {
        $score_value_str = trim($_POST[$field_name]);

        if ($score_value_str !== '') { // Process only if not an empty string after trimming
            if (!is_numeric($score_value_str)) {
                $validation_errors[] = "Invalid score for '" . ucwords(str_replace('_', ' ', $field_name)) . "' (must be a number). You entered: '" . htmlspecialchars($score_value_str) . "'.";
            } else {
                $score_value = floatval($score_value_str);
                if ($score_value < 0 || $score_value > $max_score) {
                    $validation_errors[] = "Score for '" . ucwords(str_replace('_', ' ', $field_name)) . "' must be between 0 and " . $max_score . ". You entered: " . $score_value . ".";
                }
                $scores_to_save[$field_name] = $score_value;
            }
        } else {
            $scores_to_save[$field_name] = null; // Store null if field was submitted but empty
        }
    } else {
        // Field was not even in POST, treat as null (or handle as error if all fields are mandatory)
        $scores_to_save[$field_name] = null;
    }
}
// Sanitize comments
$scores_to_save['evaluator_comments'] = isset($_POST['evaluator_comments']) ? trim(htmlspecialchars($_POST['evaluator_comments'], ENT_QUOTES, 'UTF-8')) : null;


if (!empty($validation_errors)) {
    $response['message'] = "<strong>Please correct the following input errors:</strong><br>" . implode("<br>", $validation_errors);
} else {
    // Ensure the session structure is initialized
    if (!isset($_SESSION['evaluations'])) {
        $_SESSION['evaluations'] = [];
    }
    if (!isset($_SESSION['evaluations'][$position_id])) {
        $_SESSION['evaluations'][$position_id] = [];
    }
    
    // Store all scores and comments for the specific candidate under the specific position
    $_SESSION['evaluations'][$position_id][$candidate_id] = $scores_to_save;

    $response['success'] = true;
    $response['message'] = 'Evaluation successfully saved to your current session!';
    // For vote.php to show a general message after modal closes (optional)
    // $_SESSION['evaluation_message_vote_page'] = 'Evaluation for a candidate has been updated in your session.';
    // $_SESSION['evaluation_message_type_vote_page'] = 'info';
}

echo json_encode($response);
?>