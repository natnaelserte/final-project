<?php 
include('session.php'); 
include('head.php'); 
?>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>

    <div id="page-wrapper">
        <div class="row"><div class="col-lg-12"></div></div><hr />
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="alert alert-success">Voting Report</h4>
            </div><br />

            <a href="report.php" class="btn btn-warning" style="margin-left:19px;">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button onclick="window.print();" class="btn btn-info pull-right" style="margin-right:19px;">
                <i class="fa fa-print"></i> Print
            </button>
            <div style="clear:both;"></div>

            <div class="panel-body">
                <?php
                require 'dbcon.php';
                include("AES/aes_config.php");

                function encryptCandidateId($candidate_id, $aes_key, $aes_iv) {
                    $encrypted = openssl_encrypt($candidate_id, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $aes_iv);
                    return base64_encode($encrypted);
                }

                if (!isset($_POST['position']) || empty($_POST['position'])) {
                    echo '<p>Please select a position first in the <a href="report.php">report page</a>.</p>';
                    exit;
                }

                $position_id = $_POST['position'];

                $stmtPos = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
                $stmtPos->execute([$position_id]);
                $posData = $stmtPos->fetch(PDO::FETCH_ASSOC);

                if (!$posData) {
                    echo '<p>Invalid position selected.</p>';
                    exit;
                }

                echo '<div class="panel-heading text-center" style="font-size: 20px; font-weight: bold;">' . htmlspecialchars($posData['position_name']) . '</div>';

                $stmtCandidates = $pdo->prepare("SELECT * FROM candidate WHERE position = ?");
                $stmtCandidates->execute([$position_id]);
                $candidates = $stmtCandidates->fetchAll(PDO::FETCH_ASSOC);

                if (!$candidates) {
                    echo '<p>No candidates found for this position.</p>';
                    exit;
                }

                // Arrays for chart
                $candidateNames = [];
                $voteCounts = [];
                $candidateImages = [];

                echo '<table class="table table-striped table-bordered table-hover">';
                echo '<thead>';
                echo '<th class="alert alert-success">Candidate</th>';
                echo '<th class="alert alert-success">Image</th>';
                echo '<th class="alert alert-success">Total Votes</th>';
                echo '</thead><tbody>';

                foreach ($candidates as $candidate) {
                    $encrypted_id = encryptCandidateId($candidate['candidate_id'], AES_KEY, AES_IV);

                    $stmtVotes = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE candidate_id = ?");
                    $stmtVotes->execute([$encrypted_id]);
                    $votesCount = $stmtVotes->fetchColumn();

                    $fullName = htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']);
                    $imagePath = !empty($candidate['img']) && file_exists("../admin2/" . $candidate['img']) 
                                 ? "../admin2/" . htmlspecialchars($candidate['img']) 
                                 : "default.png"; // Fallback image

                    $candidateNames[] = $fullName;
                    $voteCounts[] = (int)$votesCount;
                    $candidateImages[] = $imagePath;

                    echo '<tr>';
                    echo '<td>' . $fullName . '</td>';
                    echo '<td><img src="' . $imagePath . '" style="width:40px; height:40px; border-radius:50%;"></td>';
                    echo '<td class="text-center"><button class="btn btn-primary" disabled>' . (int)$votesCount . '</button></td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
                ?>

                <!-- Custom Horizontal Bar Chart -->
                <style>
                .bar-container {
                    max-width: 800px;
                    margin: 40px auto;
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .bar-row {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                }
                .bar-row img {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    margin-right: 10px;
                }
                .bar-label {
                    flex: 1;
                    font-weight: bold;
                }
                .bar {
                    flex: 3;
                    background: #e0e0e0;
                    border-radius: 30px;
                    overflow: hidden;
                    margin-right: 10px;
                }
                .bar-fill {
                    height: 20px;
                    text-align: right;
                    padding-right: 8px;
                    line-height: 20px;
                    color: #fff;
                    font-size: 12px;
                }
                </style>

                <div class="bar-container">
                    <?php
                    $maxVotes = max($voteCounts);
                    foreach ($candidateNames as $index => $name) {
                        $votes = $voteCounts[$index];
                        $image = $candidateImages[$index];
                        $percent = $maxVotes > 0 ? round(($votes / $maxVotes) * 100, 2) : 0;
                        $color = $index == 0 ? '#d32f2f' : '#388e3c'; // red/green alternation
                        echo '<div class="bar-row">';
                        echo '<img src="' . $image . '" alt="Candidate">';
                        echo '<div class="bar-label">' . $name . '</div>';
                        echo '<div class="bar">';
                        echo '<div class="bar-fill" style="width:' . $percent . '%; background:' . $color . ';">' . $percent . '%</div>';
                        echo '</div>';
                        echo '<div>' . number_format($votes) . ' votes</div>';
                        echo '</div>';
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include('script.php'); ?>
</body>
</html>
