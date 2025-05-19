<?php include('session.php'); ?>
<?php include('head.php'); ?>

<body>
    <div id="wrapper">
        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12"></div>
                <hr />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="alert alert-success">Voting Report</h4>
                    </div>
                    <br />
                    <form method="post" action="sort.php">
                        <select name="position" id="position" class="form-control pull-left" style="width:300px;margin-left:19px;">
                            <option value="" disabled selected>----Sort by Position----</option>
                            <?php
                            require 'dbcon.php';

                            $positions_query = $pdo->query("SELECT position_id, position_name FROM position ORDER BY position_name");
                            $positions = $positions_query->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($positions as $position) {
                                echo '<option value="' . htmlspecialchars($position['position_id']) . '">' . htmlspecialchars($position['position_name']) . '</option>';
                            }
                            ?>
                        </select>
                        &nbsp;&nbsp;
                        <button id="sort" class="btn btn-success">Sort</button>
                        <button type="button" onclick="window.print();" style="margin-right:14px;" id="print" class="pull-right btn btn-info">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </form>
                    <div class="panel-body">
                        <?php
                        require 'dbcon.php';
                        include("AES/aes_config.php");

                        function encryptCandidateId($candidate_id, $aes_key, $aes_iv) {
                            return openssl_encrypt($candidate_id, 'aes-256-cbc', $aes_key, 0, $aes_iv);
                        }

                        function displayCandidates($pdo, $position, $aes_key, $aes_iv) {
                            $query = $pdo->prepare("SELECT * FROM candidate WHERE position = ?");
                            $query->execute([$position['position_id']]);

                            $candidates = [];
                            $total_votes = 0;

                            while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
                                $candidate_id = $fetch['candidate_id'];
                                $encrypted_candidate_id = encryptCandidateId($candidate_id, $aes_key, $aes_iv);

                                $query1 = $pdo->prepare("SELECT COUNT(*) as total FROM votes WHERE candidate_id = ?");
                                $query1->execute([$encrypted_candidate_id]);
                                $votes = (int)$query1->fetch(PDO::FETCH_ASSOC)['total'];

                                $candidates[] = [
                                    'name' => htmlspecialchars($fetch['firstname'] . ' ' . $fetch['lastname']),
                                    'img' => htmlspecialchars($fetch['img']),
                                    'votes' => $votes,
                                    'party' => 'ABC' // Placeholder, replace with actual party if available
                                ];

                                $total_votes += $votes;
                            }

                            echo '<div style="margin-bottom: 30px;">';
                            echo '<h4 class="text-primary">Vote Result: ' . htmlspecialchars($position['position_name']) . '</h4>';
                            echo '<div class="candidate-results" style="border:1px solid #ddd; border-radius:10px; padding:15px;">';

                            // Sort by vote count descending
                            usort($candidates, fn($a, $b) => $b['votes'] <=> $a['votes']);

                            foreach ($candidates as $index => $c) {
                                $percent = $total_votes > 0 ? round(($c['votes'] / $total_votes) * 100, 2) : 0;
                                $bar_color = $index == 0 ? '#d9534f' : '#5cb85c';

                                echo '<div style="margin-bottom: 20px;">';
                                echo '<strong>' . ($index + 1) . '. ' . $c['name'] . '</strong>';
                                echo ' <span style="float:right;">' . number_format($c['votes']) . ' votes | <strong>' . $c['party'] . '</strong> ' . $percent . '%</span>';
                                echo '<div style="position:relative; height:30px; background:#e6e6e6; border-radius:50px; margin-top:5px;">';
                                echo '<div style="width:' . $percent . '%; background:' . $bar_color . '; height:100%; border-radius:50px;"></div>';
                                echo '<img src="../admin2/' . $c['img'] . '" style="position:absolute; top:-5px; left:' . ($percent > 5 ? ($percent - 5) . '%' : '0') . '; width:40px; height:40px; border-radius:50%; border:2px solid white;">';
                                echo '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '</div>';
                        }

                        foreach ($positions as $position) {
                            displayCandidates($pdo, $position, AES_KEY, AES_IV);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional styling -->
    <style>
        .candidate-results img {
            transition: transform 0.3s ease;
        }

        .candidate-results img:hover {
            transform: scale(1.1);
        }
    </style>

    <?php include('script.php'); ?>
</body>

</html>
