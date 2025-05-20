<?php
include('head.php');
include("sess.php");
require 'admin/dbcon.php';

$all_positions_data = [];
$display_positions = [];
$db_error = null;

try {
    $positions_query = $pdo->query("SELECT p.position_id, p.position_name FROM `position` p ORDER BY p.position_id ASC");
    $all_raw_positions = $positions_query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_raw_positions as $position) {
        $candidate_check_query = $pdo->prepare("SELECT COUNT(*) AS count FROM `candidate` WHERE `position` = ?");
        $candidate_check_query->execute([$position['position_id']]);
        $candidate_count = $candidate_check_query->fetchColumn();

        if ($candidate_count > 0) {
            $position_name_lower = strtolower(str_replace(' ', '_', $position['position_name']));
            $display_positions[] = [
                'id' => $position['position_id'],
                'name' => htmlspecialchars($position['position_name']),
                'name_lower' => $position_name_lower,
                'candidate_count' => $candidate_count
            ];
            $all_positions_data[] = [
                'name_lower' => $position_name_lower,
                'display_name' => htmlspecialchars($position['position_name'])
            ];
        }
    }
} catch (PDOException $e) {
    $db_error = "Error fetching position data: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
        }
        #wrapper_1 { /* Main content wrapper */
            /* display: flex; /* Uncomment if side_bar.php is a true sidebar */
        }
        .container_vote_content {
            flex-grow: 1;
            padding: 25px 15px; /* Standard Bootstrap-like container padding */
            max-width: 1200px; /* Limit content width for better readability */
            margin: 0 auto; /* Center content */
        }
        .main-voting-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2.5rem !important;
            text-align: center;
            font-size: 2.25rem;
        }
        .position-section-container { /* Wrapper for each position's title and cards */
            margin-bottom: 3rem;
        }
        .position-main-title { /* Title like "List of Doctors" / "Position Name" */
            font-size: 1.8rem;
            font-weight: 500;
            color: #34495e;
            margin-bottom: 1rem;
            text-align: center; /* Center the title above the cards */
        }
        .position-main-subtitle { /* Subtitle like "Simply browse..." */
            font-size: 0.95rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            text-align: center;
        }

        .candidate-display-card {
            background-color: #fff;
            border: 1px solid #eef2f7; /* Lighter border */
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); /* Softer, more spread shadow */
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden; /* Ensure content respects border radius */
        }
        .candidate-display-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transform: translateY(-4px);
        }
        .candidate-card-image-wrapper {
            position: relative;
            background-color: #f7f9fc; /* Very light neutral background for image area */
            padding: 20px 20px 0 20px; /* Padding top and sides, none at bottom */
            height: 200px; /* Fixed height for consistent image area */
            display: flex;
            align-items: center; /* Center image vertically */
            justify-content: center; /* Center image horizontally */
        }
        .candidate-card-image-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Show whole image, might not fill container */
            /* object-fit: cover; /* Fill container, might crop image */
            border-radius: 6px; /* Slightly rounded image */
        }
        .candidate-availability-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #2ecc71; /* Green for "Eligible" */
            color: white;
            padding: 5px 12px;
            font-size: 0.7rem;
            font-weight: 500;
            border-radius: 20px; /* Pill shape */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .candidate-card-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        .candidate-card-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 4px;
        }
        .candidate-card-position { /* Position name under candidate name */
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 15px;
            font-style: italic;
        }
        .candidate-card-details-grid { /* For Year Level, Party etc. */
            display: flex;
            justify-content: space-around; /* For 2-3 items */
            /* Or use grid for more complex layouts: display: grid; grid-template-columns: 1fr 1fr; gap: 10px; */
            margin-bottom: 15px;
            padding: 12px 0;
            border-top: 1px solid #f0f2f5;
            border-bottom: 1px solid #f0f2f5;
        }
        .candidate-detail-block {
            text-align: center;
        }
        .candidate-detail-block .label {
            display: block;
            font-size: 0.7rem;
            color: #95a5a6;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .candidate-detail-block .value {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: #34495e;
        }
        .candidate-card-footer-action { /* Checkbox area */
            margin-top: auto; /* Push to bottom */
            padding-top: 15px;
        }
        .vote-checkbox-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ecf0f1;
            color: #34495e;
            border: 1px solid #dce4ec;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            width: 100%;
            box-sizing: border-box;
        }
        .candidate-vote-input:checked + .vote-checkbox-label {
            background-color: #3498db; /* Blue when selected */
            color: white;
            border-color: #2980b9;
        }
        .candidate-vote-input:disabled + .vote-checkbox-label {
            background-color: #f7f9fc;
            color: #bdc3c7;
            cursor: not-allowed;
            border-color: #ecf0f1;
        }
        .candidate-vote-input {
            opacity: 0; position: absolute; width: 0; height: 0;
        }
        .vote-submit-button {
            background-color: #27ae60; /* Green submit */
            color: white;
            font-size: 1.15rem;
            padding: 12px 35px;
            font-weight: 500;
            border: none;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }
        .vote-submit-button:hover {
            background-color: #229954;
            color: white;
        }

        @media (max-width: 768px) {
            .main-voting-title { font-size: 1.9rem; }
            .position-main-title { font-size: 1.6rem; }
            .candidate-card-name { font-size: 1.1rem; }
            .candidate-card-image-wrapper { height: 180px; }
            .candidate-card-details-grid { flex-direction: column; gap: 8px; }
            .candidate-detail-block .label { font-size: 0.65rem; }
            .candidate-detail-block .value { font-size: 0.85rem; }
            .candidate-detail-block:not(:last-child) { margin-bottom: 8px; }
        }
        @media (max-width: 576px) {
            .container_vote_content { padding: 15px 10px; }
            .main-voting-title { font-size: 1.7rem; }
            .position-main-title { font-size: 1.4rem; }
            .candidate-card-name { font-size: 1rem; }
            .candidate-card-image-wrapper { height: 160px; padding: 15px 15px 0 15px; }
            .vote-checkbox-label { padding: 8px 15px; font-size: 0.9rem; }
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
</head>
<body>
    <?php include 'side_bar.php'; ?>

    <div id="wrapper_1">
        <div class="container_vote_content">
            <h1 class="main-voting-title">Cast Your Vote</h1>

            <?php if (isset($db_error)): ?>
                <div class="alert alert-danger"><?php echo $db_error; ?></div>
            <?php elseif (empty($display_positions)): ?>
                <div class="alert alert-info text-center p-4">
                    No positions available for voting at this time.
                </div>
            <?php else: ?>
                <form method="POST" action="vote_result.php" id="votingForm">
                    <?php foreach ($display_positions as $position_item): ?>
                        <div class="position-section-container">
                            <h2 class="position-main-title"><?php echo $position_item['name']; ?></h2>
                            <p class="position-main-subtitle">Select your preferred candidate for this role.</p>
                            <div class="row <?php echo ($position_item['candidate_count'] < 3 && $position_item['candidate_count'] > 0) ? 'justify-content-center' : ''; ?>">
                                <?php
                                $candidate_query = $pdo->prepare("SELECT * FROM `candidate` WHERE `position` = ? ORDER BY candidate_id ASC");
                                $candidate_query->execute([$position_item['id']]);
                                while ($candidate = $candidate_query->fetch(PDO::FETCH_ASSOC)):
                                    $candidate_id_val = htmlspecialchars($candidate['candidate_id']);
                                    $img_path_val = "admin2/" . htmlspecialchars($candidate['img']);
                                ?>
                                    <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
                                        <div class="candidate-display-card w-100">
                                            <div class="candidate-card-image-wrapper">
                                                <img src="<?php echo $img_path_val; ?>" alt="<?php echo htmlspecialchars($candidate['firstname']); ?>">
                                                <span class="candidate-availability-badge">Eligible</span>
                                            </div>
                                            <div class="candidate-card-info">
                                                <h5 class="candidate-card-name"><?php echo htmlspecialchars($candidate['firstname']) . ' ' . htmlspecialchars($candidate['lastname']); ?></h5>
                                                <p class="candidate-card-position"><?php echo $position_item['name']; ?></p>

                                                <div class="candidate-card-details-grid">
                                                    <div class="candidate-detail-block">
                                                        <span class="label">Year Level</span>
                                                        <span class="value"><?php echo htmlspecialchars($candidate['year_level']); ?></span>
                                                    </div>
                                                    <div class="candidate-detail-block">
                                                        <span class="label">Party</span>
                                                        <span class="value"><?php echo htmlspecialchars($candidate['party']); ?></span>
                                                    </div>
                                                    
                                                    {-- <div class="candidate-detail-block">
                                                        <span class="label">Gender</span>
                                                        <span class="value"><?php echo htmlspecialchars($candidate['gender']); ?></span>
                                                    </div> --}
                                                </div>

                                                <div class="candidate-card-footer-action">
                                                    <input type="checkbox"
                                                           value="<?php echo $candidate_id_val; ?>"
                                                           name="<?php echo $position_item['name_lower'] . '_id'; ?>"
                                                           id="cand_<?php echo $candidate_id_val; ?>"
                                                           class="candidate-vote-input"
                                                           data-position-group="<?php echo $position_item['name_lower']; ?>">
                                                    <label for="cand_<?php echo $candidate_id_val; ?>" class="vote-checkbox-label">
                                                        Select to Vote
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-5 mb-4">
                        <button class="btn vote-submit-button" type="submit" name="submit">Submit Ballot</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php
    include('script.php');
    include('footer.php');
    ?>

    <script type="text/javascript">
        const POSITIONS_FOR_VALIDATION = <?php echo json_encode($all_positions_data); ?>;
        $(document).ready(function() {
            $('.candidate-vote-input').on('change', function() {
                const groupName = $(this).data('position-group');
                const $checkboxesInGroup = $(`.candidate-vote-input[data-position-group="${groupName}"]`);
                if ($(this).is(':checked')) {
                    $checkboxesInGroup.not(this).prop('checked', false).prop('disabled', true);
                    $(this).prop('disabled', false);
                } else {
                    $checkboxesInGroup.prop('disabled', false);
                }
            });

            $('#votingForm').on('submit', function(event) {
                let allValid = true;
                let firstErrorPosition = null;
                POSITIONS_FOR_VALIDATION.forEach(function(position) {
                    const groupName = position.name_lower;
                    if ($(`.candidate-vote-input[data-position-group="${groupName}"]:checked`).length === 0) {
                        allValid = false;
                        if (!firstErrorPosition) {
                            firstErrorPosition = position.display_name;
                        }
                    }
                });
                if (!allValid) {
                    event.preventDefault();
                    alert('Please select a candidate for the "' + firstErrorPosition + '" position before submitting.');
                }
            });
        });
    </script>
</body>
</html>