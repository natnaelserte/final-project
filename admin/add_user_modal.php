<div class="modal fade" id="add_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <center>Add Staff</center>
                        </div>
                    </div>
                </h4>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" name="username" placeholder="Username" required="true">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="password" name="password" placeholder="Password" required="true">
                    </div>
                    <div class="form-group">
                        <label>Firstname</label>
                        <input class="form-control" type="text" name="firstname" placeholder="Firstname" required="true">
                    </div>
                    <div class="form-group">
                        <label>Lastname</label>
                        <input class="form-control" type="text" name="lastname" placeholder="Lastname" required="true">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input class="form-control" type="number" name="Phone" placeholder="Phone Number" required="true">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" type="email" name="email" placeholder="Email Address">
                    </div>
                    <button name="ok" type="submit" class="btn btn-primary">Save Data</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'dbcon.php'; // PDO connection
//require_once '../AES/aes_config.php'; // Include AES configuration

if (isset($_POST['ok'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['Phone'];
    $email = $_POST['email'];

    try {
        // Hash the password securely using password_hash()
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
		// AES encryption for the password
        // Check for duplicate username
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userExists = $stmt->fetchColumn();

        if ($userExists > 0) {
            echo "<script>alert('User Already Exists');</script>";
        } else {
            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, firstname, lastname, Phone, email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $firstname, $lastname, $phone, $email]);

            echo "<script>alert('User Data Successfully Saved'); window.location='user.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}
?>
