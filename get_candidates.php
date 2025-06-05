<?php
require_once 'admin/dbcon.php'; // Ensure this path is correct

// Optional: Error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if (!isset($pdo)) {
    error_log("get_candidates.php: PDO object is not available. Check dbcon.php inclusion/connection.");
    echo "<div class='alert alert-danger mt-3 col-12' role='alert'><strong>Critical Error:</strong> Database connection failed.</div>";
    exit;
}

if (isset($_POST['position'])) {
    $position_id_from_ajax = $_POST['position']; // This is the ID from position.position_id

    if (!filter_var($position_id_from_ajax, FILTER_VALIDATE_INT)) {
        echo "<div class='alert alert-warning mt-3 col-12' role='alert'>Invalid position selected.</div>";
        exit;
    }

    try {
        // MODIFIED SQL Query:
        // - Selected c.candidate_type instead of c.year_level
        // - Selected c.slogan instead of c.party (assuming 'slogan' is the new column name)
        $sql = "SELECT c.candidate_id, c.firstname, c.lastname, c.candidate_type, c.gender, c.img, c.slogan,
                       p.position_name
                FROM candidate c
                LEFT JOIN position p ON c.position = p.position_id
                WHERE c.position = :pos_id_param
                ORDER BY c.lastname ASC, c.firstname ASC";

        $candidate_query = $pdo->prepare($sql);
        $candidate_query->bindParam(':pos_id_param', $position_id_from_ajax, PDO::PARAM_INT);
        $candidate_query->execute();

        if ($candidate_query->rowCount() > 0) {
            echo '<div class="row">'; // This row will contain the candidate columns

            while ($candidate_row = $candidate_query->fetch(PDO::FETCH_ASSOC)) {
                $candidate_id = htmlspecialchars($candidate_row['candidate_id']);
                $firstname = htmlspecialchars($candidate_row['firstname']);
                $lastname = htmlspecialchars($candidate_row['lastname']);
                $name = $firstname . ' ' . $lastname;

                $role_display = !empty($candidate_row['position_name']) ? htmlspecialchars($candidate_row['position_name']) : 'Candidate';
                
                // CHANGED: year_level to candidate_type
                $candidate_type_display = !empty($candidate_row['candidate_type']) ? htmlspecialchars($candidate_row['candidate_type']) : 'N/A';
                
                // CHANGED: party to slogan
                $slogan_display = !empty($candidate_row['slogan']) ? htmlspecialchars($candidate_row['slogan']) : 'N/A';
                
                $gender = !empty($candidate_row['gender']) ? htmlspecialchars($candidate_row['gender']) : 'N/A';

                // Image path logic - assuming 'admin2' is a directory prefix and 'img' column contains 'upload/filename.ext'
                // Ensure this path is correct for your setup. If images are directly in 'upload/', remove 'admin2/'.
                $img_path = 'admin2/' . htmlspecialchars($candidate_row['img']); 
                // If images are in the root 'upload' folder relative to this script's execution context:
                // $img_path = htmlspecialchars($candidate_row['img']);


                // Bootstrap column classes for responsive grid
                echo '<div class="col-lg-4 col-md-6 col-sm-12 mb-4 candidate-card-column">';
                echo '  <div class="candidate-profile-card h-100">'; // h-100 for equal height cards
                echo '    <div class="candidate-img-container">';
                // Check if image exists, otherwise show a placeholder or hide
                if (!empty($candidate_row['img']) && file_exists($img_path)) { // Check if the file exists at the constructed path
                    echo '      <img src="' . $img_path . '?t=' . time() . '" alt="Photo of ' . $name . '">'; // Added cache buster
                } else {
                    echo '      <img src="path/to/your/placeholder-image.png" alt="Image not available">'; // Provide a placeholder
                }
                echo '      <span class="candidate-status-badge">Eligible</span>'; // This seems static, adjust if needed
                echo '    </div>';
                echo '    <div class="candidate-card-content">';
                echo '      <h5 class="candidate-name">' . $name . '</h5>';
                echo '      <p class="candidate-role">' . $role_display . '</p>';
                echo '      <div class="candidate-details-grid">';
                echo '        <div class="detail-item">';
                // CHANGED: Label and value for candidate_type
                echo '          <span class="detail-label">Type</span>';
                echo '          <span class="detail-value">' . $candidate_type_display . '</span>';
                echo '        </div>';
                echo '        <div class="detail-item">';
                // CHANGED: Label and value for slogan
                echo '          <span class="detail-label">Slogan</span>';
                echo '          <span class="detail-value">' . $slogan_display . '</span>';
                echo '        </div>';
                echo '      </div>';
                echo '      <div class="candidate-action-footer">';
                echo '        <span class="footer-text">Gender: ' . $gender . '</span>';
                // You might want to add a vote button or other actions here later
                echo '      </div>';
                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }
            echo '</div>'; // End .row
        } else {
            echo "<div class='alert alert-info mt-3 col-12' role='alert'>No candidates found for this position.</div>";
        }
    } catch (PDOException $e) {
        error_log("PDO Error in get_candidates.php: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
        // Avoid echoing $sql directly in production if it contains sensitive info, but good for debugging.
        // error_log("Failing SQL in get_candidates.php: " . $sql); 
        echo "<div class='alert alert-danger mt-3 col-12' role='alert'>An error occurred while fetching candidate data. Please contact support.</div>";
    }
} else {
    echo "<p class='text-muted mt-3 col-12'>Please select a position to view candidates.</p>";
}
?>