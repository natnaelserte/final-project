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
        // SQL Query confirmed against your provided table structures
        $sql = "SELECT c.candidate_id, c.firstname, c.lastname, c.year_level, c.gender, c.img, c.party,
                       p.position_name
                FROM candidate c
                LEFT JOIN position p ON c.position = p.position_id /* Candidate.position links to Position.position_id */
                WHERE c.position = :pos_id_param                  /* Filter by candidate.position */
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
                $year_level = !empty($candidate_row['year_level']) ? htmlspecialchars($candidate_row['year_level']) : 'N/A';
                $party = !empty($candidate_row['party']) ? htmlspecialchars($candidate_row['party']) : 'N/A';
                $gender = !empty($candidate_row['gender']) ? htmlspecialchars($candidate_row['gender']) : 'N/A';

                // Image path logic - assuming 'admin2' is a directory prefix and 'img' column contains 'upload/filename.ext'
                $img_path = 'admin2/' . htmlspecialchars($candidate_row['img']);


                // Bootstrap column classes for responsive grid
                echo '<div class="col-lg-4 col-md-6 col-sm-12 mb-4 candidate-card-column">';
                echo '  <div class="candidate-profile-card h-100">'; // h-100 for equal height cards
                echo '    <div class="candidate-img-container">';
                echo '      <img src="' . $img_path . '" alt="Photo of ' . $name . '">';
                echo '      <span class="candidate-status-badge">Eligible</span>';
                echo '    </div>';
                echo '    <div class="candidate-card-content">';
                echo '      <h5 class="candidate-name">' . $name . '</h5>';
                echo '      <p class="candidate-role">' . $role_display . '</p>';
                echo '      <div class="candidate-details-grid">';
                echo '        <div class="detail-item">';
                echo '          <span class="detail-label">Year Level</span>';
                echo '          <span class="detail-value">' . $year_level . '</span>';
                echo '        </div>';
                echo '        <div class="detail-item">';
                echo '          <span class="detail-label">Party</span>';
                echo '          <span class="detail-value">' . $party . '</span>';
                echo '        </div>';
                echo '      </div>';
                echo '      <div class="candidate-action-footer">';
                echo '        <span class="footer-text">Gender: ' . $gender . '</span>';
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
        error_log("Failing SQL in get_candidates.php: " . $sql); // Log the SQL
        echo "<div class='alert alert-danger mt-3 col-12' role='alert'>An error occurred while fetching candidate data. Please contact support. (Error: " . htmlspecialchars($e->getCode()) . ")</div>";
    }
} else {
    echo "<p class='text-muted mt-3 col-12'>Please select a position to view candidates.</p>";
}
?>