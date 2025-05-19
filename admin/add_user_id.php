<?php include('session.php'); ?>
<?php include('head.php'); ?>

<?php
require_once 'dbcon.php';

if (isset($_POST['add'])) {
    $stream = $_POST['stream'];
    $id = $_POST['id'];
    $batch = $_POST['batch'];
    $id_number = $_POST['id_number']; // user input
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $role_id = $_POST['role_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = $_POST['status'];
    $account = $_POST['account'];

    $username = str_replace('/', '.', "$stream/$id/$batch");

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo "<script>
                alert('Username already exists in user table!');
                window.location='user.php';
            </script>";
        } else {
            $insert_user = $pdo->prepare("INSERT INTO users (
                username, id_number, firstname, lastname, gender, phone,
                role_id, password, status, account, registration_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $insert_user->execute([
                $username, $id_number, $firstname, $lastname, $gender,
                $phone, $role_id, $password, $status, $account
            ]);

            echo "<script>
                alert('User successfully added!');
                window.location='user.php';
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            alert('Database Error: " . htmlspecialchars($e->getMessage()) . "');
            window.location='add_user.php';
        </script>";
    }
}
?>

<script>
function validateForm() {
    let isValid = true;

    document.getElementById("idError").innerText = "";
    document.getElementById("batchError").innerText = "";
    document.getElementById("phoneError").innerText = "";

    const id = document.forms[0]["id"].value.trim();
    const batch = document.forms[0]["batch"].value.trim();
    const phone = document.forms[0]["phone"].value.trim();

    const idPattern = /^\d+$/;
    const batchPattern = /^\d{2}$/;
    const phonePattern = /^09\d{8}$/;

    if (!idPattern.test(id)) {
        document.getElementById("idError").innerText = "ID must be numeric only.";
        isValid = false;
    }

    if (!batchPattern.test(batch)) {
        document.getElementById("batchError").innerText = "Batch must be exactly 2 digits.";
        isValid = false;
    }

    if (!phonePattern.test(phone)) {
        document.getElementById("phoneError").innerText = "Phone must start with 09 and be 10 digits.";
        isValid = false;
    }

    return isValid;
}

function liveValidate(field, pattern, errorId, message) {
    const value = field.value.trim();
    const error = document.getElementById(errorId);
    if (!pattern.test(value)) {
        error.innerText = message;
    } else {
        error.innerText = "";
    }
}
</script> 
<body>
<?php
include('side_bar.php')
?> 

<div class="modal-dialog container" >

    <div class="modal-content " style="width: 500px;">
        <div class="modal-header">
            <h4 class="modal-title">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <center>Add User Info</center>
                    </div>
                </div>
            </h4>
        </div>

        <div class="modal-body">
            <form action="" method="post" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Stream</label>
                    <select class="form-control" name="stream" required>
                        <option value="">-- Select Stream --</option>
                        <option value="NSR">NSR</option>
                        <option value="PGR">PGR</option>
                        <option value="SSR">SSR</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>ID (Numbers only)</label>
                    <input class="form-control" type="text" name="id" required oninput="liveValidate(this, /^\d+$/, 'idError', 'ID must be numeric only.')">
                    <small id="idError" style="color:red;"></small>
                </div>

                <div class="form-group">
                    <label>Batch (2-digit only)</label>
                    <input class="form-control" type="text" name="batch" required oninput="liveValidate(this, /^\d{2}$/, 'batchError', 'Batch must be exactly 2 digits.')">
                    <small id="batchError" style="color:red;"></small>
                </div>

                <div class="form-group">
                    <label>Full ID Number (e.g., NSR/123/22)</label>
                    <input class="form-control" type="text" name="id_number" required>
                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input class="form-control" type="text" name="firstname" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input class="form-control" type="text" name="lastname" required>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select class="form-control" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input class="form-control" type="text" name="phone" required oninput="liveValidate(this, /^09\d{8}$/, 'phoneError', 'Phone must start with 09 and be 10 digits.')">
                    <small id="phoneError" style="color:red;"></small>
                </div>

                <div class="form-group">
                    <label>Role ID</label>
                    <input class="form-control" type="number" name="role_id" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Account</label>
                    <select class="form-control" name="account" required>
                        <option value="">-- Select Account Status --</option>
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>

                <button name="add" type="submit" class="btn btn-primary">Add to Database</button>
            </form>
        </div>

        <div class="modal-footer">
            <a href="user.php"><button type="button" class="btn btn-default">Back</button></a>
        </div>
    </div>
</div>
</body>