<?php include('session.php'); ?>
<?php include('head.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->

<body>
    <div id="wrapper">
        <?php include('side_bar.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">System Usage Time Tracker</h3>
                </div>
                <hr />

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                System User Log
                            </div>
                        </div>
                    </div>

                    <div class="panel-body">
                        <!-- Login Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Time Logged In</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require 'dbcon.php';

                                    try {
                                        $query = $pdo->query("SELECT * FROM login ORDER BY login_time DESC");
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                            $username = htmlspecialchars($row['username']);
                                            $login_time = htmlspecialchars($row['login_time']);
                                    ?>
                                            <tr>
                                                <td><?php echo $username; ?></td>
                                                <td><?php echo $login_time; ?></td>
                                            </tr>
                                    <?php
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='2'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Charts Row -->
                        <div class="row d-flex align-items-start">
                            <!-- Pie Chart -->
                            <div class="col-md-6 mb-4">
                                <div class="card p-3 shadow-sm">
                                    <h4 class="text-center">Login Time Distribution (Pie)</h4>
                                    <canvas id="loginPieChart"></canvas>
                                </div>
                            </div>

                            <!-- Line Chart -->
                            <div class="col-md-6 mb-4">
                                <div class="card p-3 shadow-sm ms-md-3">
                                    <h4 class="text-center">Login Activity per Hour (Line)</h4>
                                    <canvas id="loginLineChart"></canvas>
                                </div>
                            </div>
                        </div>

                    </div> <!-- /.panel-body -->
                </div> <!-- /.panel -->
            </div> <!-- /.row -->
        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <?php include('script.php'); ?>

    <!-- PHP: Prepare chart data -->
    <?php
    require 'dbcon.php';

    // For Pie Chart
    $timeSegments = ['Morning' => 0, 'Afternoon' => 0, 'Evening' => 0, 'Night' => 0];

    // For Line Chart: logins by hour (0 to 23)
    $hourlyLogins = array_fill(0, 24, 0);

    try {
        $stmt = $pdo->query("SELECT login_time FROM login");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hour = (int)date('G', strtotime($row['login_time'])); // 0-23 hour

            $hourlyLogins[$hour]++;

            if ($hour >= 5 && $hour < 12) {
                $timeSegments['Morning']++;
            } elseif ($hour >= 12 && $hour < 17) {
                $timeSegments['Afternoon']++;
            } elseif ($hour >= 17 && $hour < 21) {
                $timeSegments['Evening']++;
            } else {
                $timeSegments['Night']++;
            }
        }
    } catch (PDOException $e) {
        echo "<script>console.error('Error fetching data.');</script>";
    }
    ?>

    <!-- JS: Render Charts -->
    <script>
        // PIE CHART
        const pieCtx = document.getElementById('loginPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Morning', 'Afternoon', 'Evening', 'Night'],
                datasets: [{
                    data: [
                        <?php echo $timeSegments['Morning']; ?>,
                        <?php echo $timeSegments['Afternoon']; ?>,
                        <?php echo $timeSegments['Evening']; ?>,
                        <?php echo $timeSegments['Night']; ?>
                    ],
                    backgroundColor: ['#36A2EB', '#FFCE56', '#4BC0C0', '#FF6384']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Login Time Distribution'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // LINE CHART
        const lineCtx = document.getElementById('loginLineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php for ($i = 0; $i < 24; $i++) echo "'$i:00',"; ?>
                ],
                datasets: [{
                    label: 'Number of Logins',
                    data: [<?php echo implode(',', $hourlyLogins); ?>],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Login Activity by Hour (24h)'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Logins'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hour of Day'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
