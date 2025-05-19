<?php include('session.php'); ?>
<?php include('head.php'); ?>
<?php include('side_bar.php'); ?>
<
<?php
require_once 'dbcon.php';

if (isset($_POST['add'])) {
    $stream = $_POST['stream'];
    $id = $_POST['id'];
    $batch = $_POST['batch'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];

    $id_number = $stream . '/' . $id . '/' . $batch;
    $username = str_replace('/', '.', $id_number);
    $date = date("Y-m-d H:i:s");

    try {
        $query = $pdo->prepare("SELECT COUNT(*) FROM ids WHERE id_number = ?");
        $query->execute([$id_number]);
        $count = $query->fetchColumn();

        if ($count > 0) {
            echo "<script>
                alert('Student ID already in Database');
                window.location='add_student_id.php';
            </script>";
        } else {
            $insert_query = $pdo->prepare("INSERT INTO ids (id_number, firstname, lastname, username, date) VALUES (?, ?, ?, ?, ?)");
            $insert_query->execute([$id_number, $firstname, $lastname, $username, $date]);

            echo "<script>
                alert('Successfully Added');
                window.location='current_students.php';
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            alert('Database Error: " . htmlspecialchars($e->getMessage()) . "');
            window.location='add_student_id.php';
        </script>";
    }
}
?>

<script>
function validateForm() {
    let isValid = true;

    // Clear messages
    document.getElementById("idError").innerText = "";
    document.getElementById("batchError").innerText = "";

    const id = document.forms[0]["id"].value.trim();
    const batch = document.forms[0]["batch"].value.trim();

    const idPattern = /^\d+$/;
    const batchPattern = /^\d{2}$/;

    if (!idPattern.test(id)) {
        document.getElementById("idError").innerText = "ID must be numeric only.";
        isValid = false;
    }

    if (!batchPattern.test(batch)) {
        document.getElementById("batchError").innerText = "Batch must be exactly 2 digits.";
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

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <center>Add Student Info</center>
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
                    <label>First Name</label>
                    <input class="form-control" type="text" name="firstname" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input class="form-control" type="text" name="lastname" required>
                </div>

                <button name="add" type="submit" class="btn btn-primary">Add to Database</button>
            </form>
        </div>

        <div class="modal-footer">
            <a href="current_students.php"><button type="button" class="btn btn-default">Back</button></a>
        </div>
    </div>
</div>
