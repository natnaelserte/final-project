<?php
include('session.php');
include('head.php');

// Initialize messages
$success_message = null;
$error_message = null;

// Check if the backup button was clicked
if (isset($_POST['backup_database'])) {
    // Include the backup script
    $backupResult = include('Backup/backup_db.php');

    // Set session messages based on the backup result
    if ($backupResult && $backupResult['success']) {
        $success_message = $backupResult['message'];
    } else {
        $error_message = $backupResult['message'];
    }
}

// Fetch candidate data for charts
require 'dbcon.php';

try {
    // Positions count for bar chart
    $positionsData = $pdo->query("SELECT position, COUNT(*) as count FROM candidate GROUP BY position")->fetchAll(PDO::FETCH_ASSOC);

    // Year levels with count for multiline chart
    $yearLevelsData = $pdo->query("
        SELECT year_level, gender, COUNT(*) as count 
        FROM candidate 
        GROUP BY year_level, gender 
        ORDER BY year_level
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Gender count for pie chart
    $genderData = $pdo->query("SELECT gender, COUNT(*) as count FROM candidate GROUP BY gender")->fetchAll(PDO::FETCH_ASSOC);

    // Get position names for positions chart
    $positionNames = [];
    $positionCounts = [];
    foreach ($positionsData as $pos) {
        $stmt = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
        $stmt->execute([$pos['position']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $positionNames[] = $row ? $row['position_name'] : 'Unknown';
        $positionCounts[] = (int)$pos['count'];
    }

    // Process year level data into arrays for males and females
    $yearLevels = [];
    $maleCounts = [];
    $femaleCounts = [];
    foreach ($yearLevelsData as $data) {
        $year = $data['year_level'];
        if (!in_array($year, $yearLevels)) {
            $yearLevels[] = $year;
        }
    }
    sort($yearLevels);

    // Initialize male and female counts for each year level
    foreach ($yearLevels as $year) {
        $maleCounts[$year] = 0;
        $femaleCounts[$year] = 0;
    }

    // Fill counts
    foreach ($yearLevelsData as $data) {
        if ($data['gender'] === 'Male') {
            $maleCounts[$data['year_level']] = (int)$data['count'];
        } elseif ($data['gender'] === 'Female') {
            $femaleCounts[$data['year_level']] = (int)$data['count'];
        }
    }

    // Gender pie chart data
    $maleGenderCount = 0;
    $femaleGenderCount = 0;
    foreach ($genderData as $g) {
        if (strtolower($g['gender']) === 'male') $maleGenderCount = (int)$g['count'];
        if (strtolower($g['gender']) === 'female') $femaleGenderCount = (int)$g['count'];
    }

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>

<body>
<div id="wrapper">

    <!-- Navigation -->
    <?php include('side_bar.php'); ?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Candidate List</h3>
            </div>
            <!-- /.col-lg-12 -->
        </div>

        <!-- Centered Buttons -->
        <div class="row">
            <div class="col-lg-12 text-center" style="margin-bottom: 20px;">
                <button class="btn btn-success" data-toggle="modal" data-target="#myModal">Add Candidate</button>
                <?php include('add_candidate_modal.php'); ?>
                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal">Delete All Candidates</button>
                <?php include('delete_all_candidate_modal.php'); ?>
            </div>
        </div>

        <!-- Backup Button -->
        <div class="row">
            <div class="col-lg-12 text-right" style="margin-bottom: 10px;">
                <form method="post" style="display: inline-block;">
                    <button type="submit" class="btn btn-primary" name="backup_database">Backup Database</button>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="modal-title" id="myModalLabel">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            Candidate List
                        </div>
                    </div>
                </h4>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>Position</th>
                            <th>Party</th>
                            <th>Firstname</th>
                            <th>Lastname</th>
                            <th>Year Level</th>
                            <th>Gender</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM candidate ORDER BY candidate_id DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $candidate_id = $row['candidate_id'];
                                $position_id = $row['position']; // position id

                                $stmt1 = $pdo->prepare("SELECT position_name FROM position WHERE position_id = ?");
                                $stmt1->execute([$position_id]);
                                $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

                                $position_name = ($row1 && isset($row1['position_name'])) ? htmlspecialchars($row1['position_name']) : 'Unknown Position';
                                ?>
                                <tr>
                                    <td width="50"><img src="<?php echo htmlspecialchars($row['img']); ?>" width="50" height="50" class="img-rounded"></td>
                                    <td><?php echo $position_name; ?></td>
                                    <td><?php echo htmlspecialchars($row['party']); ?></td>
                                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td style="text-align:center">
                                        <a rel="tooltip" title="Delete" id="<?php echo $candidate_id; ?>"
                                           href="#delete_user<?php echo $candidate_id; ?>" data-target="#delete_user<?php echo $candidate_id ?>"
                                           data-toggle="modal" class="btn btn-danger btn-outline">
                                            <i class="fa fa-trash-o"></i> Delete
                                        </a>
                                        <?php include('delete_candidate_modal.php'); ?>
                                        <a rel="tooltip" title="Edit" id="<?php echo htmlspecialchars($row['candidate_id']) ?>"
                                           href="#edit_candidate<?php echo htmlspecialchars($row['candidate_id']) ?>" data-toggle="modal"
                                           class="btn btn-success btn-outline">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <?php require 'edit_candidate_modal.php'; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='8'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->

        <!-- Charts below the table -->
        <div class="row" style="margin-top: 40px;">
            <div class="col-lg-12">
                <div id="positionsBarChart" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
        <div class="row" style="margin-top: 40px;">
            <div class="col-lg-12">
                <div id="yearLevelLineChart" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
        <div class="row" style="margin-top: 40px; margin-bottom: 50px;">
            <div class="col-lg-12">
                <div id="genderPieChart" style="width: 100%; height: 400px;"></div>
            </div>
        </div>

    </div>
    <!-- /#page-wrapper -->

</div>
<!-- /#wrapper -->

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        // Positions Bar Chart
        var positionsData = new google.visualization.DataTable();
        positionsData.addColumn('string', 'Position');
        positionsData.addColumn('number', 'Count');

        var positions = <?php echo json_encode($positionNames); ?>;
        var counts = <?php echo json_encode($positionCounts); ?>;

        for (var i = 0; i < positions.length; i++) {
            positionsData.addRow([positions[i], counts[i]]);
        }

        var positionsOptions = {
            title: 'Number of Candidates by Position',
            legend: {position: 'none'},
            colors: ['#4285F4'],
            chartArea: {width: '70%'}
        };

        var positionsChart = new google.visualization.BarChart(document.getElementById('positionsBarChart'));
        positionsChart.draw(positionsData, positionsOptions);

        // Year Level Multi-line Chart
        var yearLevelData = new google.visualization.DataTable();
        yearLevelData.addColumn('string', 'Year Level');
        yearLevelData.addColumn('number', 'Male');
        yearLevelData.addColumn('number', 'Female');

        var years = <?php echo json_encode($yearLevels); ?>;
        var maleCounts = <?php echo json_encode(array_values($maleCounts)); ?>;
        var femaleCounts = <?php echo json_encode(array_values($femaleCounts)); ?>;

        for (var j = 0; j < years.length; j++) {
            yearLevelData.addRow([years[j].toString(), maleCounts[j], femaleCounts[j]]);
        }

        var yearLevelOptions = {
            title: 'Candidates by Year Level and Gender',
            hAxis: {title: 'Year Level'},
            vAxis: {title: 'Number of Candidates'},
            colors: ['#1E90FF', '#FF69B4'],
            curveType: 'function',
            pointSize: 5,
            legend: {position: 'bottom'}
        };

        var yearLevelChart = new google.visualization.LineChart(document.getElementById('yearLevelLineChart'));
        yearLevelChart.draw(yearLevelData, yearLevelOptions);

        // Gender Pie Chart
        var genderData = google.visualization.arrayToDataTable([
            ['Gender', 'Count'],
            ['Male', <?php echo $maleGenderCount; ?>],
            ['Female', <?php echo $femaleGenderCount; ?>]
        ]);

        var genderOptions = {
            title: 'Candidates Gender Distribution',
            pieHole: 0.4,
            colors: ['#3366CC', '#FF6699']
        };

        var genderChart = new google.visualization.PieChart(document.getElementById('genderPieChart'));
        genderChart.draw(genderData, genderOptions);
    }

    window.addEventListener('resize', drawCharts);
</script>

<?php include('script.php'); ?>

</body>
</html>
