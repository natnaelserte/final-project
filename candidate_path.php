<?php
include('head.php'); // Should include jQuery
if (!isset($pdo)) { // If head.php doesn't establish $pdo
    @include_once 'admin/dbcon.php'; // Attempt to establish $pdo
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Selection - <?php echo isset($system_name) ? htmlspecialchars($system_name) : "Voting System"; ?></title>
    <style>
        /* General Page Styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
            font-size: 16px;
        }
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Page Headers */
        .page-header {
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 2rem;
            font-weight: 500;
        }
        .page-header + p {
            margin-bottom: 30px;
            font-size: 1.1rem;
            color: #6c757d;
        }
        #candidateListHeader {
            font-size: 1.75rem;
            margin-top: 35px;
            margin-bottom: 25px;
            color: #333;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        /* Panel Styling */
        .panel-default {
            border: 1px solid #e0e0e0;
            border-radius: 0.3rem;
            margin-bottom: 30px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.06);
        }
        .panel-heading {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            font-size: 1.15rem;
            color: #333;
        }
        .panel-body {
            padding: 20px;
        }

        /* Position Cards Styles */
        .position-card-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .position-card {
            background-color: #ffffff;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            flex: 1 1 100%; /* Default full width */
            min-width: 150px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .position-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.08);
            border-color: #0069d9;
        }
        .position-card.selected {
            border-color: #007bff;
            background-color: #e7f3ff;
            box-shadow: 0 3px 8px rgba(0,123,255,0.15);
            transform: translateY(-1px);
        }
        .position-card h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            color: #0056b3;
        }

        /* === RESPONSIVE CSS FOR CANDIDATE CARDS === */
        #candidateList .candidate-card-column { }

        #candidateList .candidate-profile-card {
            background-color: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
            text-align: center;
            padding-bottom: 15px;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        #candidateList .candidate-profile-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
        }
        #candidateList .candidate-img-container {
            position: relative;
            background-color: #f0f5ff;
            padding: 10px;
            border-top-left-radius: 7px;
            border-top-right-radius: 7px;
            margin-bottom: 10px;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #candidateList .candidate-img-container img {
            max-width: 100%;
            max-height: 100%;
            height: auto;
            width: auto;
            border-radius: 4px;
            object-fit: cover;
        }
        #candidateList .candidate-status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 3px 8px;
            font-size: 0.65rem;
            font-weight: bold;
            border-radius: 10px;
            text-transform: uppercase;
        }
        #candidateList .candidate-card-content {
            padding: 0 10px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        #candidateList .candidate-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: #333;
            margin-top: 0;
            margin-bottom: 3px;
        }
        #candidateList .candidate-role {
            font-size: 0.8rem;
            color: #667;
            margin-bottom: 10px;
        }
        #candidateList .candidate-details-grid {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
            padding: 8px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }
        #candidateList .detail-item {
            text-align: center;
            flex: 1;
            padding: 0 3px;
        }
        #candidateList .detail-item:first-child {
             border-right: 1px solid #f0f0f0;
        }
        #candidateList .detail-label {
            display: block;
            font-size: 0.65rem;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        #candidateList .detail-value {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #444;
            word-break: break-word;
        }
        #candidateList .candidate-action-footer {
            margin-top: auto;
            padding-top: 8px;
        }
        #candidateList .footer-text {
            font-size: 0.75rem;
            color: #777;
            font-style: italic;
        }
        /* === END OF RESPONSIVE CSS FOR CANDIDATE CARDS === */

        /* Utility Classes */
        .text-muted { color: #6c757d !important; }
        .text-info { color: #17a2b8 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-warning { color: #ffc107 !important; }

        /* === GENERAL RESPONSIVE ADJUSTMENTS === */
        @media (min-width: 576px) { /* Small devices */
            .page-header { font-size: 2.25rem; }
            #candidateListHeader { font-size: 1.9rem; }
            .position-card { flex-basis: calc(50% - 10px); }
            .panel-heading { font-size: 1.2rem; }
            #candidateList .candidate-img-container { height: 170px; padding: 12px; margin-bottom: 12px; }
            #candidateList .candidate-card-content { padding: 0 12px; }
            #candidateList .candidate-name { font-size: 1.1rem; }
            #candidateList .candidate-role { font-size: 0.85rem; }
            #candidateList .detail-value { font-size: 0.85rem; }
            #candidateList .footer-text { font-size: 0.8rem; }
            .row{
                display: block !important;
            }
        }
        @media (min-width: 400px) {
            .row{
                display: block !important;
            } 
        }


        @media (min-width: 768px) { /* Medium devices */
            .page-header { font-size: 2.5rem; }
            #candidateListHeader { font-size: 2rem; }
            .position-card { flex-basis: calc(33.333% - 14px); }
            #candidateList .candidate-img-container { height: 180px; padding: 15px; margin-bottom: 15px; }
            #candidateList .candidate-card-content { padding: 0 15px; }
            #candidateList .candidate-name { font-size: 1.15rem; }
            #candidateList .candidate-role { font-size: 0.9rem; }
            #candidateList .detail-label { font-size: 0.7rem; }
            #candidateList .detail-value { font-size: 0.9rem; }
            .row{
                display: block !important;
            }
        }
        @media (min-width: 992px) { /* Large devices */
            .position-card { flex-basis: calc(25% - 15px); }
            #candidateList .candidate-img-container { height: 200px; }
            #candidateList .candidate-name { font-size: 1.2rem; }
        }
        @media (min-width: 1200px) { /* Extra large devices */
            .position-card { flex-basis: calc(20% - 16px); }
            .panel-body { padding: 25px; }
        }
        @media (max-width: 420px) { /* Very small screens */
            .page-header { font-size: 1.75rem; }
            .page-header + p { font-size: 1rem; }
            #candidateListHeader { font-size: 1.5rem; }
            .position-card h4 { font-size: 0.9rem; }
            #candidateList .candidate-details-grid { flex-direction: column; align-items: center; }
            #candidateList .detail-item { flex-basis: auto; width: 80%; margin-bottom: 8px; padding: 5px 0; }
            #candidateList .detail-item:first-child { border-right: none; border-bottom: 1px solid #f0f0f0; }
            #candidateList .detail-item:last-child { margin-bottom: 0; }
            #candidateList .candidate-name { font-size: 1rem; }
            #candidateList .candidate-role { font-size: 0.75rem; }
            #candidateList .detail-value { font-size: 0.75rem; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('view_banner.php'); ?>
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Find a Candidate by Position</h3>
                    <p>Explore candidates across various positions and easily view their profiles.</p>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Select a Position</div>
                <div class="panel-body">
                    <div class="position-card-grid">
                        <?php
                        if (!isset($pdo)) {
                            echo "<p class='text-danger col-12'>Database connection error.</p>";
                        } else {
                            try {
                                $position_query = $pdo->query("SELECT position_id, position_name FROM position ORDER BY position_name ASC");
                                if ($position_query && $position_query->rowCount() > 0) {
                                    while ($position_row = $position_query->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<div class='position-card' data-position-id='" . htmlspecialchars($position_row['position_id']) . "'>";
                                        echo "<h4>" . htmlspecialchars($position_row['position_name']) . "</h4>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='text-info col-12'>No positions available.</p>";
                                }
                            } catch (PDOException $e) {
                                echo "<p class='text-danger col-12'>Error fetching positions.</p>";
                                error_log("PDO Error fetching positions: " . $e->getMessage());
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header" id="candidateListHeader" style="display:none;">List of Candidates</h3>
                    <div id="candidateList">
                        <p class="text-muted col-12" id="selectPositionPrompt">Please select a position above to see candidates.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
$(document).ready(function () {
    $('.position-card').click(function () {
        var $thisCard = $(this);
        var selectedPositionId = $thisCard.data('position-id');
        var selectedPositionName = $thisCard.find('h4').text();

        $('.position-card').removeClass('selected');
        $thisCard.addClass('selected');
        
        $('#selectPositionPrompt').hide();
        $('#candidateListHeader').text('Candidates for ' + selectedPositionName).show();
        $('#candidateList').html('<div class="col-12 text-center"><p class="text-info" style="padding:20px;">Loading candidates...</p></div>');

        if (selectedPositionId) {
            $.ajax({
                url: 'get_candidates.php',
                type: 'POST',
                data: { position: selectedPositionId },
                success: function (data) {
                    $('#candidateList').html(data);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    var errorMsg = "<div class='col-12 text-center'><p class='text-danger' style='padding:20px;'>"; // Corrected style attribute
                    errorMsg += "<strong>An error occurred while retrieving candidate data. Please contact support if the issue persists.</strong><br>";
                    if (xhr.responseText && xhr.responseText.includes("42S22")) {
                        errorMsg += "<small>Error (42S22): A database column was not found. Please verify system configuration.</small>";
                    }
                    errorMsg += "</p></div>";
                    $('#candidateList').html(errorMsg);
                }
            });
        } else {
            $('#candidateList').html('<p class="text-muted col-12">Please select a valid position.</p>');
            $('#candidateListHeader').hide();
        }
    });
});
</script>
</body>
</html>