<?php
require_once 'dbcon.php';

if (isset($_POST['change'])) {
    $user_id = $_GET['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $Phone = $_POST['Phone'];
    $email = $_POST['email'];

    try {
        // Use a parameterized query to update user details securely
        $stmt = $pdo->prepare("UPDATE users SET 
            username = ?, 
            password = ?, 
            firstname = ?, 
            lastname = ?, 
            Phone = ?, 
            email = ? 
            WHERE user_id = ?");
        $stmt->execute([$username, $password, $firstname, $lastname, $Phone, $email, $user_id]);

        // Redirect with a success message
        ?>
        <script type="text/javascript">
            alert('User updated successfully');
            window.location = 'user.php';
        </script>
        <?php
    } catch (PDOException $e) {
        // Handle database errors
        ?>
        <script type="text/javascript">
            alert('Error updating user: <?php echo htmlspecialchars($e->getMessage()); ?>');
            window.location = 'user.php';
        </script>
        <?php
    }
}
?>