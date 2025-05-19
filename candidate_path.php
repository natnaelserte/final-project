<?php
include('head.php'); // Ensure jQuery is loaded here or before the Select2 JS
?>
<head>
    <!-- Add other head content from head.php -->
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Optional: Custom styling for Select2 to better fit your theme */
        .select2-container .select2-selection--single {
            height: 34px; /* Match Bootstrap's default input height */
            border-radius: 4px; /* Match Bootstrap's border-radius */
            border: 1px solid #ccc;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }
        .select2-dropdown {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #337ab7; /* Bootstrap primary color for hover/selection */
            color: white;
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
                    <h3 class="page-header">Candidates</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Select Position to View Candidates
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Select Candidate Position:</label> <!-- Changed label for clarity -->
                                <select class="form-control" id="positionSelect" style="width: 100%;"> <!-- Added style for Select2 to take full width -->
                                    <option></option> <!-- Required empty option for placeholder with Select2 -->
                                    <?php
                                    // Ensure dbcon.php is included or connection is established
                                    // For this example, assuming $pdo is available
                                    // If not, uncomment and adjust:
                                    // require_once 'admin/dbcon.php';

                                    // --- Simulated PDO connection for demonstration if dbcon.php is problematic ---
                                    // Remove this block if your dbcon.php works correctly
                                    if (!isset($pdo)) {
                                        // This is a fallback for demonstration if $pdo isn't set from dbcon.php
                                        // In a real scenario, ensure dbcon.php correctly establishes $pdo
                                        // For local testing without a DB, you might simulate data:
                                        $simulated_positions = [
                                            ['position_id' => 1, 'position_name' => 'President'],
                                            ['position_id' => 2, 'position_name' => 'Vice President'],
                                            ['position_id' => 3, 'position_name' => 'Secretary']
                                        ];
                                        foreach ($simulated_positions as $position_row) {
                                            echo "<option value='" . htmlspecialchars($position_row['position_id']) . "'>" . htmlspecialchars($position_row['position_name']) . "</option>";
                                        }
                                    } else {
                                    // --- End of simulated block ---
                                        try {
                                            $position_query = $pdo->query("SELECT * FROM position ORDER BY position_name ASC");
                                            if ($position_query) {
                                                while ($position_row = $position_query->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<option value='" . htmlspecialchars($position_row['position_id']) . "'>" . htmlspecialchars($position_row['position_name']) . "</option>";
                                                }
                                            } else {
                                                echo "<option disabled>Error fetching positions.</option>";
                                                error_log("Error fetching positions: " . print_r($pdo->errorInfo(), true));
                                            }
                                        } catch (PDOException $e) {
                                            echo "<option disabled>Error connecting to database.</option>";
                                            error_log("PDO Error: " . $e->getMessage());
                                        }
                                    } // End of else for $pdo check
                                    ?>
                                </select>
                            </div>

                            <div id="candidateList">
                                <!-- Candidate cards will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- Ensure jQuery is loaded before Select2.js -->
<!-- If jQuery is in footer.php, make sure it's before this script -->
<!-- <script src="path/to/jquery.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize Select2
        $('#positionSelect').select2({
            placeholder: "Select Position", // Text for the placeholder
            allowClear: true // Adds a small 'x' to clear the selection
        });

        // Your existing AJAX logic - should work seamlessly with Select2
        $('#positionSelect').change(function () {
            var selectedPosition = $(this).val();

            // If allowClear is true, selectedPosition can be null when cleared
            if (selectedPosition) {
                $.ajax({
                    url: 'get_candidates.php', // This file fetches and formats candidate data
                    type: 'POST',
                    data: {position: selectedPosition},
                    success: function (data) {
                        $('#candidateList').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: " + status + " - " + error);
                        $('#candidateList').html("<p>Error loading candidates.</p>");
                    }
                });
            } else {
                $('#candidateList').html(''); // Clear candidates if position is cleared
            }
        });
    });
</script>
</body>
</html>