<?php include('session.php'); ?>

<?php
if (isset($_POST['submit'])) {
    require_once 'dbcon.php';
    $date = date("Y-m-d H:i:s");
    // Check if a file was uploaded
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        try {
            // Open the uploaded CSV file
            $handle = fopen($_FILES['filename']['tmp_name'], "r");

            // Prepare the SQL statement for inserting data
            $stmt = $pdo->prepare("INSERT INTO users (id_number, date) VALUES (?, ?)");

            // Read the CSV file line by line
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Execute the prepared statement with the CSV data
                $stmt->execute([$data[0], $date]);
            }

            // Close the file handle
            fclose($handle);

            // Success message
            ?>
            <script type="text/javascript">
                alert('Successfully imported a CSV file!');
                window.location = 'current_students.php';
            </script>
            <?php
        } catch (PDOException $e) {
            // Handle database errors
            ?>
            <script type="text/javascript">
                alert('Error importing data: <?php echo htmlspecialchars($e->getMessage()); ?>');
                window.location = 'current_students.php';
            </script>
            <?php
        }
    } else {
        // Handle file upload failure
        ?>
        <script type="text/javascript">
            alert('No file uploaded or upload failed!');
            window.location = 'current_students.php';
        </script>
        <?php
    }
} else {
    // Handle form submission failure
    ?>
    <script type="text/javascript">
        alert('Upload Failed!');
        window.location = 'current_students.php';
    </script>
    <?php
}
?>