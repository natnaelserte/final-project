<?php
include('session.php');
include('head.php');

// Check if the position ID is provided in the URL
if (isset($_GET['id'])) {
    $position_id = $_GET['id'];

    // Database connection
    require 'dbcon.php';

    try {
        // Fetch the position data based on the ID
        $stmt = $pdo->prepare("SELECT * FROM position WHERE position_id = :position_id");
        $stmt->bindParam(':position_id', $position_id, PDO::PARAM_INT);
        $stmt->execute();
        $position = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the position exists
        if (!$position) {
            // If the position doesn't exist, redirect to the position list page
            header("Location: add_position.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    // If no ID is provided, redirect to the position list page
    header("Location: add_position.php");
    exit();
}
?>

<body>
<div id="wrapper">
    <?php include('side_bar.php'); ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Edit Election Position</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Edit Position
                    </div>
                    <div class="panel-body">
                        <form method="post">
                            <input type="hidden" name="position_id" value="<?php echo htmlspecialchars($position['position_id']); ?>">
                            <div class="form-group">
                                <label>Position Name</label>
                                <input type="text" class="form-control" name="position_name"
                                       value="<?php echo htmlspecialchars($position['position_name']); ?>" required>
                            </div>
                            <button type="submit" name="update_position" class="btn btn-primary">Update Position</button>
                            <a href="add_position.php" class="btn btn-default">Cancel</a>
                        </form>

                        <?php
                        if (isset($_POST['update_position'])) {
                            $position_id = $_POST['position_id'];
                            $position_name = $_POST['position_name'];

                            try {
                                // Update the position using a prepared statement
                                $stmt = $conn->prepare("UPDATE position SET position_name = :position_name WHERE position_id = :position_id");
                                $stmt->bindParam(':position_name', $position_name, PDO::PARAM_STR);
                                $stmt->bindParam(':position_id', $position_id, PDO::PARAM_INT);

                                if ($stmt->execute()) {
                                    echo "<div class='alert alert-success'>Position updated successfully!</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating position.</div>";
                                }
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('script.php'); ?>
</body>
</html>