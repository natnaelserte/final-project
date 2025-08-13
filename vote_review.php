<?php
// vote_review.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('sess.php'); // Should set $_SESSION['user_id'], $_SESSION['user_role']
include('head.php');
require_once 'admin/dbcon.php';

$role_id = $_SESSION['user_role'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null; // Needed for submit_vote.php if it uses it from session

$form_action_target = "submit_vote.php"; // Default final submission page

// --- VOTER LOGIC (Role != 5) ---
// Store submitted votes from vote.php into session if they are not faculty
if ($role_id != 5 && isset($_POST['submit_ballot_review'])) {
    $_SESSION['submitted_votes'] = []; // Initialize/clear previous
    $positions_q = $pdo->query("SELECT position_id, position_name FROM position");
    $db_positions = $positions_q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($db_positions as $db_pos) {
        // Construct field name based on position name from vote.php
        $post_field_name = 'candidate_for_position_' . $db_pos['position_id']; // Assuming this matches name in vote.php
        if (isset($_POST[$post_field_name]) && !empty($_POST[$post_field_name])) {
            $_SESSION['submitted_votes'][$db_pos['position_id']] = filter_var($_POST[$post_field_name], FILTER_VALIDATE_INT);
        } else {
            $_SESSION['submitted_votes'][$db_pos['position_id']] = null; // No vote for this position
        }
    }
}
// --- END VOTER LOGIC ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Selections</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ... (Your existing styles for vote_review.php) ... */
        body { background-color: #f4f7f6; font-family: 'Roboto', sans-serif; }
        .container_vote_review { max-width: 900px; margin: 2rem auto; }
        .review-page-title { color: #2c3e50; font-weight: 700; margin-bottom: 2.5rem !important; }
        .review-summary-container { background-color: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .review-section-title { font-size: 1.5rem; color: #34495e; margin-bottom: 1.5rem; text-align: center; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .reviewed-vote-card { background-color: #fdfdfd; border: 1px solid #e0e7ee; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; height: 100%; }
        .reviewed-position-title { font-size: 1.2rem; font-weight: 500; color: #2980b9; margin-top: 0; margin-bottom: 15px; text-align: center; }
        .reviewed-candidate-info { display: flex; align-items: center; justify-content: center; min-height: 70px; flex-grow: 1; }
        .reviewed-candidate-img { height: 60px; width: 60px; border-radius: 50%; object-fit: cover; margin-right: 15px; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .default-img-placeholder-review { height: 60px; width: 60px; border-radius: 50%; background-color: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem; margin-right: 15px;}
        .reviewed-candidate-name { font-size: 1.1rem; font-weight: 500; color: #34495e; }
        .no-selection-text { font-size: 1rem; color: #7f8c8d; width: 100%; text-align: center; }
        .review-confirmation-section { border-top: 1px solid #e0e0e0; margin-top: 20px; padding-top: 20px;}
        .confirmation-text { font-size: 1.2rem; color: #333; margin-bottom: 1.5rem; font-weight: 500; }
        .confirmation-actions .btn { font-size: 1rem; padding: 10px 25px; margin: 0 10px; font-weight: 500; border-radius: 5px; }
        .btn-review-action i { margin-right: 7px; }
        .evaluation-details-list { list-style-type: none; padding-left: 0; }
        .evaluation-details-list li { padding: 8px 0; border-bottom: 1px dashed #eee; }
        .evaluation-details-list li:last-child { border-bottom: none; }
        .score-badge { font-size: 0.9rem; padding: .25em .5em; }
        @media (max-width: 767px) { .review-page-title { font-size: 1.8rem; } .review-section-title { font-size: 1.3rem; } .reviewed-position-title { font-size: 1.1rem; } .reviewed-candidate-info { flex-direction: column; text-align: center; } .reviewed-candidate-img, .default-img-placeholder-review { margin-right: 0; margin-bottom: 10px; } .reviewed-candidate-name { font-size: 1rem; } .confirmation-actions { display: flex; flex-direction: column; } .confirmation-actions .btn { width: 100%; margin: 5px 0; } }

    </style>
</head><body>
    <?php include 'side_bar.php'; ?>
    <div id="wrapper_1">
        <div class="container container_vote_review py-4">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-9">
                    <h1 class="text-center mb-4 review-page-title">Review Your Selections</h1>

                    <?php
                    // Check if accessed directly without submitting the form from vote.php (for non-faculty)
                    // Faculty might access this page directly after evaluations if they are stored in session.
                    if ($role_id != 5 && !isset($_POST['submit_ballot_review']) && !isset($_SESSION['submitted_votes'])) {
                        echo "<div class='alert alert-warning text-center p-4'>Please make your selections on the <a href='vote.php'>voting page</a> first.</div>";
                    } else {
                    ?>
                        <div class="review-summary-container">
                            <?php
                            // --- FACULTY EVALUATION REVIEW & HIGHEST SCORE LOGIC (Role == 5) ---
                            if ($role_id == 5) {
                                echo '<h2 class="review-section-title">Faculty Candidate Selections (Based on Highest Evaluation Score)</h2>';
                                $faculty_evaluations = $_SESSION['evaluations'] ?? [];
                                $all_displayed_candidates_evaluated = true; // Assume true initially
                                $unevaluated_list_html = "";
                                $highest_scoring_candidates_per_position = []; // To store highest scorer for each position
                                $_SESSION['submitted_votes'] = []; // Clear/initialize for faculty

                                // Fetch all candidates faculty was supposed to evaluate
                                // This query should match the one that displays candidates for evaluation on vote.php
                                $stmt_faculty_cand_list = $pdo->prepare(
                                    "SELECT c.candidate_id, c.firstname, c.lastname, c.img, p.position_id, p.position_name 
                                     FROM candidate c JOIN position p ON c.position = p.position_id 
                                     WHERE c.candidate_type = 'Faculty' ORDER BY p.position_id, c.lastname, c.firstname"
                                );
                                // If you have specific criteria for which candidates a faculty evaluates (e.g., based on department),
                                // you'll need to add those WHERE clauses here too, matching vote.php.
                                $stmt_faculty_cand_list->execute();
                                $candidates_to_evaluate_list = $stmt_faculty_cand_list->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($candidates_to_evaluate_list)) {
                                    echo "<p class='text-center text-muted'>No candidates were found for you to evaluate in the 'Faculty' category.</p>";
                                    // In this case, they can't "vote" via highest score either.
                                    // $all_displayed_candidates_evaluated remains true but effectively means no action.
                                } else {
                                    // First pass: Check evaluations and calculate scores
                                    foreach ($candidates_to_evaluate_list as $cand_to_eval) {
                                        $pos_id = $cand_to_eval['position_id'];
                                        $cand_id = $cand_to_eval['candidate_id'];

                                        if (isset($faculty_evaluations[$pos_id][$cand_id])) {
                                            $eval_data = $faculty_evaluations[$pos_id][$cand_id];
                                            $total_score = 0;
                                            foreach ($eval_data as $key => $val) {
                                                if ($key !== 'evaluator_comments' && is_numeric($val)) {
                                                    $total_score += floatval($val);
                                                }
                                            }

                                            // Check if this candidate is the highest scorer for this position so far
                                            if (!isset($highest_scoring_candidates_per_position[$pos_id]) || $total_score > $highest_scoring_candidates_per_position[$pos_id]['score']) {
                                                $highest_scoring_candidates_per_position[$pos_id] = [
                                                    'candidate_id' => $cand_id,
                                                    'score' => $total_score,
                                                    'name' => htmlspecialchars($cand_to_eval['firstname'] . ' ' . $cand_to_eval['lastname']),
                                                    'position_name' => htmlspecialchars($cand_to_eval['position_name']),
                                                    'img' => $cand_to_eval['img'] ?? null, // Store img path
                                                    'raw_firstname' => $cand_to_eval['firstname'], // For detailed display if needed
                                                    'raw_lastname' => $cand_to_eval['lastname']
                                                ];
                                            }
                                        } else {
                                            // This candidate was supposed to be evaluated but wasn't
                                            $all_displayed_candidates_evaluated = false;
                                            $unevaluated_list_html .= "<li>" . htmlspecialchars($cand_to_eval['firstname'] . ' ' . $cand_to_eval['lastname']) . " (<em>" . htmlspecialchars($cand_to_eval['position_name']) . "</em>)</li>";
                                        }
                                    } // end foreach $candidates_to_evaluate_list

                                    // If all evaluations are done, display highest scorers and prepare for submission
                                    if ($all_displayed_candidates_evaluated) {
                                        if (!empty($highest_scoring_candidates_per_position)) {
                                            echo '<div class="row">';
                                            foreach ($highest_scoring_candidates_per_position as $pos_id => $leader_data) {
                                                // This leader's candidate_id becomes the "vote" for this position
                                                $_SESSION['submitted_votes'][$pos_id] = $leader_data['candidate_id'];
                                                // For submit_vote.php compatibility, also set the specific session var it expects
                                                $position_name_key = strtolower(str_replace([' ', '&', '/'], '_', $leader_data['position_name']));
                                                $_SESSION[$position_name_key . '_id'] = $leader_data['candidate_id'];

                                                $img_path_rev = "admin2/" . (!empty($leader_data['img']) ? htmlspecialchars($leader_data['img']) : 'default_candidate_image.png');
                                                $default_img_rev = "admin2/default_candidate_image.png";
                                                ?>
                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="reviewed-vote-card">
                                                        <h4 class="reviewed-position-title"><?php echo $leader_data['position_name']; ?></h4>
                                                        <div class="reviewed-candidate-info">
                                                            <img src="<?php echo file_exists($img_path_rev) && !is_dir($img_path_rev) ? $img_path_rev : $default_img_rev; ?>?t=<?php echo time();?>" alt="Candidate" class="reviewed-candidate-img">
                                                            <span class="reviewed-candidate-name">
                                                                <?php echo $leader_data['name']; ?>
                                                                <br><span class="badge bg-primary score-badge">Score: <?php echo number_format($leader_data['score'], 1); ?></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            } // end foreach highest_scoring_candidates_per_position
                                            echo '</div>'; // end row
                                        } else {
                                             echo "<p class='text-center text-muted'>Evaluations have been made, but no clear highest scores could be determined or no candidates were evaluated.</p>";
                                             // This case might also mean no evaluations actually happened if $_SESSION['evaluations'] was empty
                                             // but $candidates_to_evaluate_list was also empty.
                                             // Or if all scores were 0 or null.
                                             $all_displayed_candidates_evaluated = false; // Prevent submission if no leaders found
                                        }
                                    } else {
                                        // $all_displayed_candidates_evaluated is false, show warning
                                        echo "<div class='alert alert-warning mt-3'><strong>Attention:</strong> You have not yet evaluated all displayed candidates. Please evaluate the following before selections can be finalized:<ul>" . $unevaluated_list_html . "</ul></div>";
                                        $form_action_target = "vote.php"; // Force back to vote.php
                                    }
                                } // end else (if $candidates_to_evaluate_list not empty)

                            } // --- END FACULTY LOGIC ---
                            // --- VOTER SELECTION REVIEW (Role != 5) ---
                            elseif ($role_id != 5 && isset($_SESSION['submitted_votes'])) {
                                echo '<h2 class="review-section-title">Your Selected Candidates:</h2>';
                                if (!empty($_SESSION['submitted_votes'])) {
                                    echo '<div class="row">';
                                    foreach ($_SESSION['submitted_votes'] as $pos_id => $candidate_id) {
                                        $pos_name_stmt = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
                                        $pos_name_stmt->execute([$pos_id]);
                                        $pos_name_fetch = $pos_name_stmt->fetch(PDO::FETCH_ASSOC);
                                        $position_display_name = $pos_name_fetch ? htmlspecialchars($pos_name_fetch['position_name']) : "Position ID {$pos_id}";
                                    ?>
                                        <div class="col-lg-6 col-md-12 mb-4">
                                            <div class="reviewed-vote-card">
                                                <h4 class="reviewed-position-title"><?php echo $position_display_name; ?></h4>
                                                <div class="reviewed-candidate-info">
                                                <?php
                                                if ($candidate_id) {
                                                    $cand_stmt = $pdo->prepare("SELECT firstname, lastname, img FROM `candidate` WHERE `candidate_id` = ?");
                                                    $cand_stmt->execute([$candidate_id]);
                                                    $cand_fetch = $cand_stmt->fetch(PDO::FETCH_ASSOC);
                                                    if ($cand_fetch) {
                                                        $img_path_rev = "admin2/" . (!empty($cand_fetch['img']) ? htmlspecialchars($cand_fetch['img']) : 'default_candidate_image.png');
                                                        $default_img_rev = "admin2/default_candidate_image.png";
                                                ?>
                                                        <img src="<?php echo file_exists($img_path_rev) && !is_dir($img_path_rev) ? $img_path_rev : $default_img_rev; ?>?t=<?php echo time();?>" alt="Candidate" class="reviewed-candidate-img">
                                                        <span class="reviewed-candidate-name"><?php echo htmlspecialchars($cand_fetch['firstname'] . " " . $cand_fetch['lastname']); ?></span>
                                                <?php
                                                    } else { echo '<span class="no-selection-text text-danger">Candidate details not found.</span>'; }
                                                } else { echo '<span class="no-selection-text"><i>No candidate selected for this position.</i></span>'; }
                                                ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    } // end foreach submitted_votes
                                    echo '</div>'; // end row
                                } else {
                                    echo '<div class="col-12"><p class="text-muted text-center">You have not made any selections.</p></div>';
                                }
                            } // --- END VOTER SELECTION REVIEW ---
                            ?>

                            <div class="review-confirmation-section text-center mt-4 pt-3">
                                <p class="confirmation-text">
                                    <?php
                                    if ($role_id == 5) {
                                        echo $all_displayed_candidates_evaluated ? "Are you sure you want to submit these selections based on your evaluations?" : "You must complete all evaluations on the previous page to proceed.";
                                    } else {
                                        echo "Are you sure you want to submit your votes?";
                                    }
                                    ?>
                                </p>
                                <div class="confirmation-actions">
                                    <a href="vote.php" class="btn btn-outline-secondary btn-review-action"><i class="fas fa-arrow-left"></i> Go Back</a>
                                    <?php
                                    // Allow submission if:
                                    // 1. Not faculty (role_id != 5)
                                    // 2. Is faculty (role_id == 5) AND all candidates have been evaluated AND there are highest scorers to submit
                                    $can_submit_faculty = ($role_id == 5 && $all_displayed_candidates_evaluated && !empty($highest_scoring_candidates_per_position));
                                    $can_submit_voter = ($role_id != 5 && (isset($_SESSION['submitted_votes']) && !empty(array_filter($_SESSION['submitted_votes'])))); // Voter has made at least one selection

                                    if ($can_submit_faculty || $can_submit_voter) : ?>
                                        <a href="<?php echo $form_action_target; // Should be submit_vote.php ?>" class="btn btn-success btn-lg btn-review-action">
                                            <i class="fas fa-check-circle"></i> Yes, Submit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    } // end if form submitted (or faculty with session data)
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php include('script.php'); include('footer.php'); ?>
</body></html>