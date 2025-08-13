<?php
include('session.php');
include('head.php');
include('side_bar.php');
require_once 'dbcon.php';

$message = "";
$message_type = "";

if (isset($_POST['add'])) {
    $id_number = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $user_type = $_POST['user_type'];

    // SERVER-SIDE VALIDATION FOR ID NUMBER
    $min_id_length = 3;
    $max_id_length = 50;

    if (empty($id_number)) {
        echo "<script>
            alert('ID Number is required.');
            window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
        </script>";
        exit;
    } elseif (strlen($id_number) < $min_id_length || strlen($id_number) > $max_id_length) {
        echo "<script>
            alert('ID Number must be between " . $min_id_length . " and " . $max_id_length . " characters long.');
            window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
        </script>";
        exit;
    // Regex for allowed characters: letters, numbers, underscore, hyphen, dot.
    // No spaces or other special characters.
    } elseif (!preg_match("/^[a-zA-Z0-9_.-]+$/", $id_number)) {
         echo "<script>
             alert('Invalid ID Number. Only letters, numbers, underscores (_), hyphens (-), and dots (.) are allowed. No spaces.');
             window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
         </script>";
         exit;
    }

    $institution = null;
    $faculty_name = null;
    $department = null;
    $role_id = null;

    if ($user_type === 'Student') {
        $role_id = 3;
    } elseif ($user_type === 'Faculty') {
        $role_id = 5;
        $institution = isset($_POST['institution']) ? trim($_POST['institution']) : null;
        $faculty_name = isset($_POST['faculty_name']) ? trim($_POST['faculty_name']) : null;
        $department = isset($_POST['department']) ? trim($_POST['department']) : null;

        if (empty($institution) || empty($faculty_name) || empty($department)) {
            echo "<script>
                alert('Institution, Faculty Name, and Department are required when user type is Faculty.');
                window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
            </script>";
            exit;
        }
    }

    if ($role_id === null) {
        echo "<script>
            alert('Invalid user type selected. Please select Student or Faculty.');
            window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
        </script>";
        exit;
    }

    // Username can be the ID itself as it's now validated for suitable characters
    $username = $id_number;
    $date = date("Y-m-d H:i:s");

    try {
        $query = $pdo->prepare("SELECT COUNT(*) FROM ids WHERE id_number = ?");
        $query->execute([$id_number]);
        $count = $query->fetchColumn();

        if ($count > 0) {
            echo "<script>
                alert('ID Number already exists in the Database');
                window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
            </script>";
        } else {
            $insert_query = $pdo->prepare("INSERT INTO ids (id_number, firstname, lastname, username, date, role_id, institution, faculty, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_query->execute([$id_number, $firstname, $lastname, $username, $date, $role_id, $institution, $faculty_name, $department]);

            $success_msg = "Successfully Added ID: " . htmlspecialchars($id_number) . " as " . htmlspecialchars($user_type);
            if ($user_type === 'Faculty') {
                $success_msg .= " (Institution: " . htmlspecialchars($institution) .
                                ", Faculty: " . htmlspecialchars($faculty_name) .
                                ", Dept: " . htmlspecialchars($department) . ")";
            }
            echo "<script>
                alert('" . addslashes($success_msg) . "');
                window.location='current_students.php';
            </script>";
        }
    } catch (PDOException $e) {
        error_log("Database Error in add user script: " . $e->getMessage());
        echo "<script>
            alert('A database error occurred: " . htmlspecialchars(addslashes($e->getMessage())) . "');
            window.location='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
        </script>";
    }
    exit;
}
?>

<script>
function toggleFacultyInputs() {
    const userTypeSelect = document.getElementById("user_type_select");
    const facultyInputGroup = document.getElementById("faculty_specific_inputs");
    const institutionInput = document.getElementById("institution_input");
    const facultyNameInput = document.getElementById("faculty_name_input");
    const departmentInput = document.getElementById("department_input");

    if (userTypeSelect.value === "Faculty") {
        facultyInputGroup.style.display = "block";
        institutionInput.required = true;
        facultyNameInput.required = true;
        departmentInput.required = true;
    } else {
        facultyInputGroup.style.display = "none";
        institutionInput.required = false;
        facultyNameInput.required = false;
        departmentInput.required = false;
        institutionInput.value = "";
        facultyNameInput.value = "";
        departmentInput.value = "";
    }
}

function validateForm() {
    let isValid = true;

    document.getElementById("idNumberError").innerText = "";
    document.getElementById("firstnameError").innerText = "";
    document.getElementById("lastnameError").innerText = "";
    document.getElementById("userTypeError").innerText = "";
    document.getElementById("institutionError").innerText = "";
    document.getElementById("facultyNameError").innerText = "";
    document.getElementById("departmentError").innerText = "";

    const idNumber = document.getElementById("id_number_input").value.trim();
    const firstname = document.getElementById("firstname_input").value.trim();
    const lastname = document.getElementById("lastname_input").value.trim();
    const userType = document.getElementById("user_type_select").value;
    const institution = document.getElementById("institution_input").value.trim();
    const facultyName = document.getElementById("faculty_name_input").value.trim();
    const department = document.getElementById("department_input").value.trim();

    const minIdLength = 3;
    const maxIdLength = 50;
    const idPattern = /^[a-zA-Z0-9_.-]+$/; // Allowed characters

    // CLIENT-SIDE VALIDATION FOR ID NUMBER
    if (idNumber === "") {
        document.getElementById("idNumberError").innerText = "ID Number is required.";
        isValid = false;
    } else if (idNumber.length < minIdLength || idNumber.length > maxIdLength) {
        document.getElementById("idNumberError").innerText = `ID Number must be between ${minIdLength} and ${maxIdLength} characters.`;
        isValid = false;
    } else if (!idPattern.test(idNumber)) {
        document.getElementById("idNumberError").innerText = "Allowed: letters, numbers, underscore, hyphen, dot. No spaces.";
        isValid = false;
    }

    if (firstname === "" || firstname.length < 2) {
        document.getElementById("firstnameError").innerText = "First Name is required (min 2 characters).";
        isValid = false;
    }
    if (lastname === "" || lastname.length < 2) {
        document.getElementById("lastnameError").innerText = "Last Name is required (min 2 characters).";
        isValid = false;
    }

    if (userType === "") {
        document.getElementById("userTypeError").innerText = "Please select a User Type.";
        isValid = false;
    } else if (userType === "Faculty") {
        if (institution === "" || institution.length < 2) {
            document.getElementById("institutionError").innerText = "Institution is required (min 2 characters).";
            isValid = false;
        }
        if (facultyName === "" || facultyName.length < 2) {
            document.getElementById("facultyNameError").innerText = "Faculty Name is required (min 2 characters).";
            isValid = false;
        }
        if (department === "" || department.length < 2) {
            document.getElementById("departmentError").innerText = "Department is required (min 2 characters).";
            isValid = false;
        }
    }
    return isValid;
}

function liveValidate(field, errorId, minLength = 0, maxLength = Infinity, pattern = null, patternMessage = "Invalid format.") {
    const value = field.value.trim();
    const errorElement = document.getElementById(errorId);
    let errorMessage = "";
    const fieldLabel = field.labels[0] ? field.labels[0].innerText.replace('*','').trim() : "Field";


    if (value === "" && field.required) {
        errorMessage = `${fieldLabel} is required.`;
    } else if (value !== "") { // Only apply further validation if not empty (or if required and empty)
        if (value.length < minLength) {
            errorMessage = `${fieldLabel} must be at least ${minLength} characters.`;
        } else if (value.length > maxLength) {
            errorMessage = `${fieldLabel} must be no more than ${maxLength} characters.`;
        } else if (pattern && !pattern.test(value)) {
            errorMessage = patternMessage;
        }
    }
    errorElement.innerText = errorMessage;
}
</script>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Add User Information</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-8 col-sm-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">User Details</h3>
                    </div>
                    <div class="panel-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm()">

                            <div class="form-group">
                                <label for="id_number_input">ID Number <span class="text-danger">*</span></label>
                                <input id="id_number_input" class="form-control" type="text" name="id_number"
                                       placeholder="Enter unique ID (e.g., UserID123)" required
                                       oninput="liveValidate(this, 'idNumberError', 3, 50, /^[a-zA-Z0-9_.-]+$/, 'Allowed: letters, numbers, _, -, . No spaces.')">
                                <small id="idNumberError" class="text-danger"></small>
                            </div>

                            <div class="form-group">
                                <label for="firstname_input">First Name <span class="text-danger">*</span></label>
                                <input id="firstname_input" class="form-control" type="text" name="firstname" required
                                       oninput="liveValidate(this, 'firstnameError', 2, 50)">
                                <small id="firstnameError" class="text-danger"></small>
                            </div>

                            <div class="form-group">
                                <label for="lastname_input">Last Name <span class="text-danger">*</span></label>
                                <input id="lastname_input" class="form-control" type="text" name="lastname" required
                                       oninput="liveValidate(this, 'lastnameError', 2, 50)">
                                <small id="lastnameError" class="text-danger"></small>
                            </div>

                            <div class="form-group">
                                <label for="user_type_select">User Type <span class="text-danger">*</span></label>
                                <select id="user_type_select" class="form-control" name="user_type" required onchange="toggleFacultyInputs()">
                                    <option value="">-- Select User Type --</option>
                                    <option value="Student">Student (Role ID: 3)</option>
                                    <option value="Faculty">Faculty (Role ID: 5)</option>
                                </select>
                                <small id="userTypeError" class="text-danger"></small>
                            </div>

                            <div id="faculty_specific_inputs" style="display: none;">
                                <div class="form-group">
                                    <label for="institution_input">Institution <span class="text-danger">*</span></label>
                                    <input id="institution_input" class="form-control" type="text" name="institution" placeholder="e.g.,  AMIT/AWIT University"
                                           oninput="liveValidate(this, 'institutionError', 2, 100)">
                                    <small id="institutionError" class="text-danger"></small>
                                </div>
                                <div class="form-group">
                                    <label for="faculty_name_input">Faculty Name <span class="text-danger">*</span></label>
                                    <input id="faculty_name_input" class="form-control" type="text" name="faculty_name" placeholder="e.g., Faculty of SOFTWARE AND COMPUTING
                                           oninput="liveValidate(this, 'facultyNameError', 2, 100)">
                                    <small id="facultyNameError" class="text-danger"></small>
                                </div>
                                <div class="form-group">
                                    <label for="department_input">Department <span class="text-danger">*</span></label>
                                    <input id="department_input" class="form-control" type="text" name="department" placeholder="e.g., Department of Computer Science"
                                           oninput="liveValidate(this, 'departmentError', 2, 100)">
                                    <small id="departmentError" class="text-danger"></small>
                                </div>
                            </div>

                            <button name="add" type="submit" class="btn btn-primary">SAVE</button>
                        </form>
                    </div>

                    <div class="panel-footer">
                        <a href="current_students.php"><button type="button" class="btn btn-default">Back to List</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// include('footer.php');
?>