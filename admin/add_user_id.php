<?php
include('session.php');
require_once 'dbcon.php'; // For database connection ($pdo)

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    // Sanitize and retrieve form data
    $stream = trim($_POST['stream'] ?? '');
    $id_suffix = trim($_POST['id_suffix'] ?? ''); 
    $batch = trim($_POST['batch'] ?? '');
    $id_number_for_db = $stream . '/' . $id_suffix . '/' . $batch; 
    
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? ''); // Added email
    $phone = trim($_POST['phone'] ?? '');
    $role_id = trim($_POST['role_id'] ?? '');
    $password_input = $_POST['password'] ?? '';

    // --- Server-side Validation (Essential) ---
    $errors = [];
    if (empty($stream)) $errors[] = "Stream is required.";
    if (empty($id_suffix) || !ctype_digit($id_suffix)) $errors[] = "ID Suffix must be numeric.";
    if (empty($batch) || !preg_match('/^\d{2}$/', $batch)) $errors[] = "Batch must be exactly 2 digits.";
    if (empty($firstname)) $errors[] = "First Name is required.";
    if (empty($lastname)) $errors[] = "Last Name is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Phone validation
    if (empty($phone)) {
        $errors[] = "Phone is required.";
    } elseif (!preg_match('/^09\d{8}$/', $phone)) {
        $errors[] = "Phone must start with 09 and be 10 digits.";
    }
    
    if (empty($role_id) || !in_array($role_id, ['1', '2'])) $errors[] = "Invalid Role ID selected.";
    if (empty($password_input) || strlen($password_input) < 8) $errors[] = "Password is required and must be at least 8 characters.";

    // Proceed with database checks only if basic validation passes
    if (empty($errors)) {
        $username = str_replace('/', '.', $id_number_for_db);
        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        $default_status = 'Unvoted'; 
        $default_account_status = 'Active';

        try {
            // Check for uniqueness: username, id_number, email, phone
            $check_fields = [
                'username' => $username,
                'id_number' => $id_number_for_db,
                'email' => $email,
                'phone' => $phone
            ];
            $existing_field_errors = [];

            foreach ($check_fields as $field => $value) {
                $stmt_unique_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $field = :value");
                $stmt_unique_check->bindParam(':value', $value, PDO::PARAM_STR);
                $stmt_unique_check->execute();
                if ($stmt_unique_check->fetchColumn() > 0) {
                    $existing_field_errors[] = ucfirst(str_replace('_', ' ', $field)) . " ('" . htmlspecialchars($value) . "') already exists.";
                }
            }

            if (!empty($existing_field_errors)) {
                $error_message = implode("<br>", $existing_field_errors);
            } else {
                // All checks passed, proceed with insertion
                $stmt_insert = $pdo->prepare("INSERT INTO users (
                    username, id_number, firstname, lastname, gender, email, phone,
                    role_id, password, status, account, registration_date
                ) VALUES (:username, :id_number, :firstname, :lastname, :gender, :email, :phone, :role_id, :password, :status, :account, NOW())");

                $stmt_insert->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt_insert->bindParam(':id_number', $id_number_for_db, PDO::PARAM_STR);
                $stmt_insert->bindParam(':firstname', $firstname, PDO::PARAM_STR);
                $stmt_insert->bindParam(':lastname', $lastname, PDO::PARAM_STR);
                $stmt_insert->bindParam(':gender', $gender, PDO::PARAM_STR);
                $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR); // Bind email
                $stmt_insert->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt_insert->bindParam(':role_id', $role_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt_insert->bindParam(':status', $default_status, PDO::PARAM_STR);
                $stmt_insert->bindParam(':account', $default_account_status, PDO::PARAM_STR);

                if ($stmt_insert->execute()) {
                    $_SESSION['success_message'] = 'User (' . htmlspecialchars($username) . ') successfully added!';
                    header("Location: user.php"); 
                    exit;
                } else {
                    $error_message = 'Failed to add user. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Database Error in add_user.php: " . $e->getMessage());
            $error_message = "A database error occurred. Please try again later.";
        }
    } else {
        // Basic validation errors
        $error_message = implode("<br>", $errors);
    }
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

include('head.php'); 
?>
<style>
    /* Custom shadow for Bootstrap 3 panels if BS4/5 shadow utilities are not available */
    .panel-with-shadow {
        -webkit-box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }
    .form-control:focus { 
        border-color: #66afe9;
        outline: 0;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
    }
</style>
<body>
<?php include('side_bar.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3"> 
            <div class="panel panel-primary panel-with-shadow" style="margin-top: 30px; margin-bottom: 30px;">
                <div class="panel-heading">
                    <h3 class="panel-title text-center">Add New User</h3>
                </div>
                <div class="panel-body" style="padding: 25px;">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" onsubmit="return validateFormClientSide()">
                        <div class="form-group">
                            <label for="stream">Stream (Part of ID)</label>
                            <select class="form-control" id="stream" name="stream" required>
                                <option value="">-- Select Stream --</option>
                                <option value="STF" <?php echo (isset($_POST['stream']) && $_POST['stream'] == 'STF') ? 'selected' : ''; ?>>STF</option>
                                <option value="ADM" <?php echo (isset($_POST['stream']) && $_POST['stream'] == 'ADM') ? 'selected' : ''; ?>>ADM</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_suffix">ID Suffix (Middle part of ID, Numbers only)</label>
                            <input class="form-control" type="text" id="id_suffix" name="id_suffix" value="<?php echo htmlspecialchars($_POST['id_suffix'] ?? ''); ?>" required oninput="liveValidate(this, /^\d+$/, 'idSuffixError', 'ID Suffix must be numeric.')">
                            <small id="idSuffixError" class="text-danger help-block"></small>
                        </div>

                        <div class="form-group">
                            <label for="batch">Batch (e.g., 2014 becomes 14, 2-digits)</label>
                            <input class="form-control" type="text" id="batch" name="batch" value="<?php echo htmlspecialchars($_POST['batch'] ?? ''); ?>" required maxlength="2" oninput="liveValidate(this, /^\d{2}$/, 'batchError', 'Batch must be exactly 2 digits.')">
                            <small id="batchError" class="text-danger help-block"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input class="form-control" type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input class="form-control" type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required oninput="liveValidate(this, /^.+@.+\..+$/, 'emailError', 'Please enter a valid email address.')">
                            <small id="emailError" class="text-danger help-block"></small>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required maxlength="10" oninput="liveValidate(this, /^09\d{8}$/, 'phoneError', 'Phone must start with 09 and be 10 digits.')">
                            <small id="phoneError" class="text-danger help-block"></small>
                        </div>

                        <div class="form-group">
                            <label for="role_id">Role</label>
                            <select class="form-control" id="role_id" name="role_id" required>
                                <option value="">-- Select Role --</option>
                                <option value="1" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == '1') ? 'selected' : ''; ?>>System Admin</option>
                                <option value="2" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == '2') ? 'selected' : ''; ?>>Staff / Faculty Dean</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Password (min 8 characters)</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                            <small id="passwordError" class="text-danger help-block"></small> <!-- For password length error -->
                        </div>
                        
                        <button name="add_user" type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Add User</button>
                    </form>
                </div>
                <div class="panel-footer text-right">
                    <a href="user.php" class="btn btn-default">Back to User List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateFormClientSide() {
    let isValid = true;
    const errors = [];

    // Clear previous errors
    document.getElementById("idSuffixError").innerText = "";
    document.getElementById("batchError").innerText = "";
    document.getElementById("emailError").innerText = ""; // Clear email error
    document.getElementById("phoneError").innerText = "";
    document.getElementById("passwordError").innerText = ""; // Clear password error

    const idSuffix = document.getElementById("id_suffix").value.trim();
    const batch = document.getElementById("batch").value.trim();
    const email = document.getElementById("email").value.trim(); // Get email value
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value;

    const idPattern = /^\d+$/;
    const batchPattern = /^\d{2}$/;
    const emailPattern = /^.+@.+\..+$/; // Basic email pattern
    const phonePattern = /^09\d{8}$/;

    if (!idPattern.test(idSuffix)) {
        document.getElementById("idSuffixError").innerText = "ID Suffix must be numeric only.";
        errors.push("ID Suffix Error");
        isValid = false;
    }

    if (!batchPattern.test(batch)) {
        document.getElementById("batchError").innerText = "Batch must be exactly 2 digits.";
        errors.push("Batch Error");
        isValid = false;
    }

    if (!emailPattern.test(email) && email !== "") { // Check if not empty and invalid
        document.getElementById("emailError").innerText = "Please enter a valid email address.";
        errors.push("Email Error");
        isValid = false;
    }
    
    if (!phonePattern.test(phone)) {
        document.getElementById("phoneError").innerText = "Phone must start with 09 and be 10 digits.";
        errors.push("Phone Error");
        isValid = false;
    }
    
    if (password.length < 8) {
        document.getElementById("passwordError").innerText = "Password must be at least 8 characters.";
        errors.push("Password must be at least 8 characters.");
        isValid = false;
    }

    // Check other required fields
    const requiredFields = ['stream', 'id_suffix', 'batch', 'firstname', 'lastname', 'gender', 'email', 'phone', 'role_id', 'password'];
    requiredFields.forEach(function(fieldName) {
        const fieldElement = document.getElementById(fieldName);
        if (fieldElement && fieldElement.value.trim() === '') {
            if (!errors.includes(fieldName + " required")) {
                let errorDisplayId = fieldName + 'Error';
                let errorDisplayElement = document.getElementById(errorDisplayId);
                let friendlyName = fieldName.replace('_', ' ').charAt(0).toUpperCase() + fieldName.slice(1).replace('_', ' ');
                if (errorDisplayElement) {
                    errorDisplayElement.innerText = friendlyName + " is required.";
                } else {
                     // If no specific error element, maybe add to a general error list or log
                    console.warn("No error display element for " + fieldName);
                }
                errors.push(friendlyName + " is required.");
            }
            isValid = false;
        }
    });

    return isValid;
}

function liveValidate(field, pattern, errorId, message) {
    const value = field.value.trim();
    const errorElement = document.getElementById(errorId);
    if (errorElement) { 
        if (field.required && value === "") { // If required and empty, show required message or clear custom validation message
             errorElement.innerText = field.id.replace('_', ' ').charAt(0).toUpperCase() + field.id.slice(1).replace('_', ' ') + " is required.";
        } else if (!pattern.test(value) && value !== "") {
            errorElement.innerText = message;
        } else {
            errorElement.innerText = "";
        }
    }
}
</script>
</body>
</html>