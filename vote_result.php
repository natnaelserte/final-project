<?php
include('head.php'); // Should include Bootstrap CSS
include('sess.php');
?>

<body>
    <?php include 'side_bar.php'; ?>
    <div id="wrapper_1">
        <div class="container container_vote_review py-4"> 
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-center mb-4 review-page-title">Review Your Ballot</h1> 

                    <?php
                    if (isset($_POST['submit_ballot_action'])) {
                        $votes = array();
                        $positions_with_candidates = array();
                        $all_fetched_positions_for_review = []; // To store position names for cleaner iteration

                        try {
                            require 'admin/dbcon.php';

                            $positions_query = $pdo->query("SELECT position_id, position_name FROM `position` ORDER BY position_id ASC"); // Fetch name and ID
                            while ($position = $positions_query->fetch(PDO::FETCH_ASSOC)) {
                                $position_key_name = strtolower(str_replace(' ', '_', $position['position_name']));
                                $post_field_name = $position_key_name . '_id';

                                // Store all positions encountered in the form submission for ordered review
                                $all_fetched_positions_for_review[$position['position_id']] = [
                                    'display_name' => htmlspecialchars($position['position_name']),
                                    'key_name' => $position_key_name // Store the key name for accessing $_POST
                                ];


                                if (isset($_POST[$post_field_name]) && !empty($_POST[$post_field_name])) {
                                    $_SESSION[$post_field_name] = $_POST[$post_field_name];
                                    $votes[$position_key_name] = $_POST[$post_field_name];
                                    if (!in_array($position_key_name, $positions_with_candidates)) {
                                        $positions_with_candidates[] = $position_key_name;
                                    }
                                } else {
                                    $_SESSION[$post_field_name] = "";
                                    $votes[$position_key_name] = "";
                                }
                            }
                        ?>
                            <div class="review-summary-container"> 
                                <h2 class="review-section-title">Your Selections:</h2>
                                <div class="row justify-content-center"> 
                                <?php
                                // Variable to track if any candidates were displayed
                                $any_candidates_displayed = false;

                                // Iterate through all positions to maintain order and show ONLY positions with selected candidates
                                foreach ($all_fetched_positions_for_review as $pos_id => $pos_data) {
                                    $position_key_name_for_vote = $pos_data['key_name'];
                                    $candidate_id = isset($votes[$position_key_name_for_vote]) ? $votes[$position_key_name_for_vote] : "";

                                    // Only display positions where a candidate was selected
                                    if (!empty($candidate_id)) {
                                        $candidate_stmt = $pdo->prepare("SELECT firstname, lastname, img FROM `candidate` WHERE `candidate_id` = ?");
                                        $candidate_stmt->execute([$candidate_id]);
                                        $fetch = $candidate_stmt->fetch(PDO::FETCH_ASSOC);

                                        if ($fetch) {
                                            $any_candidates_displayed = true;
                                            echo '<div class="col-lg-6 col-md-8 col-sm-12 mb-4">'; // Control width of each review card
                                            echo '  <div class="reviewed-vote-card">';
                                            echo '    <h4 class="reviewed-position-title">' . $pos_data['display_name'] . '</h4>';
                                            echo '    <div class="reviewed-candidate-info">';
                                            $img_path = "admin2/" . htmlspecialchars($fetch['img']);
                                            echo '<img src="' . $img_path . '" alt="Candidate Image" class="reviewed-candidate-img">';
                                            echo '<span class="reviewed-candidate-name">' . htmlspecialchars($fetch['firstname']) . " " . htmlspecialchars($fetch['lastname']) . '</span>';
                                            echo '    </div>'; // end .reviewed-candidate-info
                                            echo '  </div>';   // end .reviewed-vote-card
                                            echo '</div>';   // end .col-
                                        }
                                    }
                                }

                                // Display a message if no candidates were selected
                                if (!$any_candidates_displayed) {
                                    echo '<div class="col-12 text-center">';
                                    echo '<p class="alert alert-warning">You have not selected any candidates. Please go back to make your selections.</p>';
                                    echo '</div>';
                                }
                                ?>
                                </div> 

                                 <div class="review-confirmation-section text-center mt-4 pt-4">
                                     <p class="confirmation-text">Are you sure you want to submit your ballot?</p>
                                     <div class="confirmation-actions">
                                         <a href="vote.php" class="btn btn-outline-secondary btn-review-action"><i class="fas fa-arrow-left"></i> Go Back & Edit</a>
                                         <a href="submit_vote.php" class="btn btn-success btn-lg btn-review-action"><i class="fas fa-check-circle"></i> Yes, Submit My Votes</a>
                                     </div>
                                 </div>
                            </div> 
                        <?php
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                    } else {
                        // This message is shown if someone navigates directly to vote_result.php without POSTing
                        echo "<div class='alert alert-warning text-center p-4'>It looks like you haven't submitted your votes yet. Please go to the <a href='vote.php'>voting page</a> to cast your ballot.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    include('script.php'); // For Bootstrap JS, Font Awesome if used
    include('footer.php');
    ?>

    <style>
        body {
            background-color: white; /* Softer background */
            font-family: 'Roboto', sans-serif; /* Assuming Roboto is loaded via head.php or CDN */
        }
        .container_vote_review {
            max-width: 900px; /* Control overall width for review page */
            margin: 0 auto;
        }
        .review-page-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2.5rem !important;
        }
        .review-summary-container {
            background-color: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .review-section-title {
            font-size: 1.5rem;
            color: #34495e;
            margin-bottom: 1.5rem;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .reviewed-vote-card {
            background-color: #fdfdfd;
            border: 1px solid #e0e7ee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px; /* Space between stacked cards on mobile */
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .reviewed-vote-card:hover {
            border-color: #c0cddc;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .reviewed-position-title {
            font-size: 1.2rem;
            font-weight: 500;
            color: #2980b9; /* Position title color */
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
        }
        .reviewed-candidate-info {
            display: flex;
            align-items: center;
            justify-content: center; /* Center content if only one item (like "No selection") */
            min-height: 80px; /* Ensure a minimum height even if no candidate */
        }
        .reviewed-candidate-img {
            height: 70px; /* Adjusted size */
            width: 70px;
            border-radius: 50%; /* Circular image */
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .reviewed-candidate-name {
            font-size: 1.1rem;
            font-weight: 500;
            color: #34495e;
        }
        .no-selection-text {
            font-size: 1rem;
            color: #7f8c8d;
            width: 100%; /* Take full width for centering */
            text-align: center;
        }

        .review-confirmation-section {
            border-top: 1px solid #e0e0e0;
        }
        .confirmation-text {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .confirmation-actions .btn {
            font-size: 1rem;
            padding: 10px 25px;
            margin: 0 10px;
            font-weight: 500;
            border-radius: 5px;
        }
        .btn-review-action i { /* For Font Awesome icons */
            margin-right: 7px;
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            .review-page-title { font-size: 1.8rem; }
            .review-section-title { font-size: 1.3rem; }
            .reviewed-position-title { font-size: 1.1rem; }
            .reviewed-candidate-info {
                flex-direction: column; /* Stack image and name on small screens */
                text-align: center;
            }
            .reviewed-candidate-img {
                margin-right: 0;
                margin-bottom: 10px; /* Space below image when stacked */
            }
            .reviewed-candidate-name { font-size: 1rem; }
            .confirmation-actions {
                display: flex;
                flex-direction: column; /* Stack buttons on small screens */
            }
            .confirmation-actions .btn {
                width: 100%; /* Full width buttons */
                margin-bottom: 10px;
            }
            .confirmation-actions .btn:last-child {
                margin-bottom: 0;
            }
        }
        @media (max-width: 576px) {
            .container_vote_review { padding: 15px; }
            .review-summary-container { padding: 20px 15px; }
            .reviewed-vote-card { padding: 15px; }
            .row {
    display: block !important;
    
}
        }
        @media (max-width: 400px) {
        .row {
    display: block !important;
    
}
        }
    </style>
</body>
</html>
