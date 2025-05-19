<style>
    /* Custom CSS for Candidate Cards - NatGeo Style */
    .candidate-card {
        background-color: #fff; /* White card background */
        border: 1px solid #e0e0e0; /* Subtle border, similar to target */
        border-radius: 0; /* Target has sharp corners */
        margin-bottom: 30px; /* Space between cards */
        transition: box-shadow 0.3s ease-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); /* Softer, more subtle shadow */
        display: flex; /* Use flexbox for a flexible card structure */
        flex-direction: column; /* Stack items vertically */
        height: 100%; /* Make cards in a row equal height if using Bootstrap row */
    }

    .candidate-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12); /* Slightly more pronounced shadow on hover */
    }

    .candidate-card .card-img-top {
        width: 100%;
        /* --- MODIFIED IMAGE HEIGHT --- */
        height: 180px; /* Reduced from 220px. Adjust as needed. */
        /* --- END OF MODIFICATION --- */
        object-fit: cover; /* Ensures the image covers the area, cropping if necessary */
        border-radius: 0; /* No rounded corners for the image */
        border-bottom: 1px solid #e0e0e0; /* Separator line like in some card designs */
    }

    .candidate-card .card-body {
        padding: 18px; /* Consistent padding */
        text-align: left;
        display: flex;
        flex-direction: column;
        flex-grow: 1; /* Allows card body to expand and push footer down if any */
    }

    /* For the small info line above the title (e.g., "19 DAYS FROM $5,849") */
    .candidate-card .card-info-line {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; /* Clean sans-serif font */
        font-size: 0.75rem; /* Small font size */
        color: #666; /* Grey color */
        margin-bottom: 8px;
        text-transform: uppercase; /* As seen in the target */
        letter-spacing: 0.5px;
        display: block;
    }

    .candidate-card .card-title {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 1.15rem; /* Prominent title */
        font-weight: 700; /* Bold */
        margin-top: 0; /* Reset margin if .card-info-line is present */
        margin-bottom: 12px;
        color: #222; /* Dark color for title */
        line-height: 1.3;
    }

    .candidate-card .card-text {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 0.875rem; /* Standard text size */
        color: #444;
        margin-bottom: 8px; /* Space between text lines */
        line-height: 1.5;
    }
    .candidate-card .card-text strong {
        color: #222; /* Key text slightly darker */
        font-weight: 600;
    }
</style>

<?php
// ... (rest of your PHP code remains the same) ...
require_once 'admin/dbcon.php';

if (isset($_POST['position'])) {
    $position_id = $_POST['position'];

    try {
        $sql = "SELECT * FROM candidate WHERE position = :position_id ORDER BY lastname ASC, firstname ASC";
        $candidate_query = $pdo->prepare($sql);
        $candidate_query->bindParam(':position_id', $position_id, PDO::PARAM_INT);
        $candidate_query->execute();

        if ($candidate_query->rowCount() > 0) {
            echo '<div class="row">'; 

            while ($candidate_row = $candidate_query->fetch(PDO::FETCH_ASSOC)) {
                $firstname = htmlspecialchars($candidate_row['firstname']);
                $lastname = htmlspecialchars($candidate_row['lastname']);
                $year_level = htmlspecialchars($candidate_row['year_level']);
                $img_path = 'admin2/' . htmlspecialchars($candidate_row['img']); 

                echo '<div class="col-lg-4 col-md-6 col-sm-12 mb-4">'; 
                echo '  <div class="card candidate-card h-100">'; 
                echo '    <img class="card-img-top" src="' . $img_path . '" alt="Photo of ' . $firstname . ' ' . $lastname . '">';
                echo '    <div class="card-body">';
                echo '      <span class="card-info-line">YEAR LEVEL: ' . strtoupper($year_level) . '</span>';
                echo '      <h5 class="card-title">' . $firstname . ' ' . $lastname . '</h5>';
                echo '    </div>'; 
                echo '  </div>'; 
                echo '</div>'; 
            }

            echo '</div>'; 
        } else {
            echo "<div class='alert alert-info mt-3' role='alert'>No candidates are currently registered for this position.</div>";
        }
    } catch (PDOException $e) {
        error_log("PDO Error fetching candidates: " . $e->getMessage()); 
        echo "<div class='alert alert-danger mt-3' role='alert'>An error occurred while fetching candidate data. Please try again later.</div>";
    }
} else {
    echo "<p class='text-muted mt-3'>Please select a position to view candidates.</p>";
}
?>