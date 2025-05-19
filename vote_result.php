<?php
include('head.php');
include('sess.php');
?>

<body>
    <?php include 'side_bar.php'; ?>
    <div id="wrapper_1">
        <div class="container_vote">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    if (isset($_POST['submit'])) {
                        // Initialize an array to store the selected candidate IDs
                        $votes = array();
                        $positions_with_candidates = array(); // Keep track of positions with candidates

                        try {
                            require 'admin/dbcon.php';

                            // Fetch all positions from the position table
                            $positions_query = $pdo->query("SELECT * FROM `position`");
                            while ($position = $positions_query->fetch(PDO::FETCH_ASSOC)) {
                                $position_name = strtolower(str_replace(' ', '_', $position['position_name']));
                                $position_id_name = $position_name . '_id';

                                // Check if a candidate was selected for this position
                                if (isset($_POST[$position_id_name]) && !empty($_POST[$position_id_name])) {
                                    // Store the selected candidate ID in the session
                                    $_SESSION[$position_id_name] = $_POST[$position_id_name];
                                    $votes[$position_name] = $_POST[$position_id_name]; // Store for display
                                    $positions_with_candidates[] = $position_name; // Add to the list
                                } else {
                                    // No candidate selected, so clear the session variable
                                    $_SESSION[$position_id_name] = "";
                                    $votes[$position_name] = ""; // Store empty for display
                                }
                            }
                        ?>
                            <div class="alert alert-info">
                                <h2>Review Your Votes</h2>
                                <?php
                                // Display the selected candidates for each position
                                foreach ($positions_with_candidates as $position_name) {
                                    $candidate_id = $votes[$position_name]; // Get the candidate ID from the $votes array

                                    echo '<div class="panel panel-default">';
                                    echo '<div class="panel-heading"><center>' . strtoupper(str_replace('_', ' ', $position_name)) . '</center></div>';
                                    echo '<div class="d-flex justify-content-center panel-body ">';
                                    if (!empty($candidate_id)) {
                                        // Fetch candidate details securely using a parameterized query
                                        $candidate_stmt = $pdo->prepare("SELECT * FROM `candidate` WHERE `candidate_id` = ?");
                                        $candidate_stmt->execute([$candidate_id]);
                                        $fetch = $candidate_stmt->fetch(PDO::FETCH_ASSOC);

                                        if ($fetch) {
                                            echo htmlspecialchars($fetch['firstname']) . " " . htmlspecialchars($fetch['lastname']) . " ";
                                            echo "<img src='admin2/" . htmlspecialchars($fetch['img']) . "' style='height:80px; width:80px; border-radius:500px;' />";
                                        } else {
                                            echo "Candidate not found.";
                                        }
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                 }
                                 ?>
                                 <br />
                              
                                 <div class="modal-body">
                                  <p>
                                    <center>Are you sure you want to submit your Votes?</center>
                                 </p>
                             </div>
                          </div>
                             <div class="modal-footer">
                                <center>
                                    <a href="submit_vote.php"><button type="submit" class="btn btn-success"><i class="icon-check"></i>&nbsp;Yes</button></a>
                                    <a href="vote.php"><button class="btn btn-danger" aria-hidden="true"><i class="icon-remove icon-large"></i>&nbsp;Back</button></a>
                                </center>
                            </div>
                        <?php
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                    } else {
                        echo "<p>No votes submitted yet.</p>"; // Message if the form hasn't been submitted
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

<?php
include('script.php');
include('footer.php');
?>