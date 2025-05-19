<?php
include('head.php');
include("sess.php");

require 'admin/dbcon.php';

$all_positions_data = [];
$display_positions = [];

try {
    $positions_query = $pdo->query("SELECT position_id, position_name FROM `position` ORDER BY position_id ASC");
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
    <link rel="stylesheet" href="path/to/your/main-stylesheet.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'side_bar.php'; ?>

    <div id="wrapper_1">
        <div class="container_vote_content py-4">
            <h1 class="text-center mb-5 main-voting-title">Cast Your Vote</h1>

            <?php if (isset($db_error)): ?>
                <div class="alert alert-danger"><?php echo $db_error; ?></div>
            <?php elseif (empty($display_positions)): ?>
                <div class="alert alert-info text-center p-4 notify-voting">
                    No positions available for voting at this time.
                </div>
            <?php else: ?>
                <form method="POST" action="vote_result.php" id="votingForm" class="voting-form-content">
                    <?php foreach ($display_positions as $position): ?>
                        <?php
                            $row_center_class = ($position['candidate_count'] < 3 && $position['candidate_count'] > 0) ? 'justify-content-center' : '';
                        ?>
                        <div class="position-container mb-4">
                            <h2 class="position-title"><?php echo $position['name']; ?></h2>
                            <div class="row <?php echo $row_center_class; ?>">
                                <?php
                                $candidate_query = $pdo->prepare("SELECT * FROM `candidate` WHERE `position` = ? ORDER BY candidate_id ASC");
                                $candidate_query->execute([$position['id']]);
                                while ($candidate = $candidate_query->fetch(PDO::FETCH_ASSOC)):
                                    $candidate_id = htmlspecialchars($candidate['candidate_id']);
                                    $img_path = "admin2/" . htmlspecialchars($candidate['img']);
                                ?>
                                    <div class="col-lg-4 col-md-6 mb-4 d-flex">
                                        <div class="candidate-card w-100">
                                            <img class="card-img-top" src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($candidate['firstname']); ?> Image">
                                            <div class="card-body text-center">
                                                <div>
                                                    <h5 class="card-title"><?php echo htmlspecialchars($candidate['firstname']) . ' ' . htmlspecialchars($candidate['lastname']); ?></h5>
                                                    <p class="card-text">
                                                        <strong>Party:</strong> <?php echo htmlspecialchars($candidate['party']); ?><br />
                                                        <strong>Level:</strong> <?php echo htmlspecialchars($candidate['year_level']); ?><br />
                                                        <strong>Gender:</strong> <?php echo htmlspecialchars($candidate['gender']); ?>
                                                    </p>
                                                </div>
                                                <div class="mt-auto pt-2">
                                                    <input type="checkbox"
                                                           value="<?php echo $candidate_id; ?>"
                                                           name="<?php echo $position['name_lower'] . '_id'; ?>"
                                                           id="cand_<?php echo $candidate_id; ?>"
                                                           class="form-check-input candidate-checkbox-input"
                                                           data-position-group="<?php echo $position['name_lower']; ?>">
                                                    <label for="cand_<?php echo $candidate_id; ?>" class="custom-vote-checkbox-label">
                                                        Select Candidate
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-5">
                        <button class="btn vote-btn" type="submit" name="submit">Submit Ballot</button>
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
            $('.candidate-checkbox-input').on('change', function() {
                const groupName = $(this).data('position-group');
                const $checkboxesInGroup = $(`.candidate-checkbox-input[data-position-group="${groupName}"]`);
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
                    if ($(`.candidate-checkbox-input[data-position-group="${groupName}"]:checked`).length === 0) {
                        allValid = false;
                        if (!firstErrorPosition) {
                            firstErrorPosition = position.display_name;
                        }
                    }
                });
                if (!allValid) {
                    event.preventDefault();
                    alert('Please select a candidate for the "' + firstErrorPosition + '" position.');
                }
            });
        });
    </script>
</body>
</html>