<?php
session_start(); // MUST be at the VERY top
include('head.php'); // Make sure this path is correct
require_once 'dbcon.php'; // Ensure this connects to your database successfully

// Function to check if ID exists in the pre-approved list
function idExistsInAllowedList($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ids WHERE id_number = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

// Function to check if email already exists for a registered user
function emailExistsInRegistrations($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$error_message = ''; // To store server-side error messages

if (isset($_POST['next'])) {
    $id_number = filter_var(trim($_POST['id_number']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Get department values
    $department_select_value = isset($_POST['department_select']) ? filter_var($_POST['department_select'], FILTER_SANITIZE_STRING) : '';
    $department_other_text_value = isset($_POST['department_other_text']) ? filter_var(trim($_POST['department_other_text']), FILTER_SANITIZE_STRING) : '';

    // Determine the final department name
    $final_department_name = '';
    if ($department_select_value === 'OtherDept') { // Changed value for "Other" department
        $final_department_name = $department_other_text_value;
    } else {
        $final_department_name = $department_select_value;
    }

    // --- Get Club Membership values ---
    $club_select_value = isset($_POST['club_select']) ? filter_var($_POST['club_select'], FILTER_SANITIZE_STRING) : '';
    $club_other_text_value = isset($_POST['club_other_text']) ? filter_var(trim($_POST['club_other_text']), FILTER_SANITIZE_STRING) : '';

    // Determine final club name
    $final_club_name = '';
    if ($club_select_value === 'OtherClub') {
        $final_club_name = $club_other_text_value;
    } elseif ($club_select_value === 'None') {
        $final_club_name = 'None'; // Explicitly set to None if selected
    } else {
        $final_club_name = $club_select_value; // For Infoken, Charity, Minimedia
    }

    // --- Get Class Representative value ---
    $is_class_representative = isset($_POST['is_class_representative']) ? filter_var($_POST['is_class_representative'], FILTER_SANITIZE_STRING) : 'No'; // Default to 'No'

    $_SESSION['form_data'] = [
        'id_number' => $id_number,
        'gender' => $gender,
        'email' => $email,
        'department_select' => $department_select_value,
        'department_other_text' => $department_other_text_value,
        'club_select' => $club_select_value,
        'club_other_text' => $club_other_text_value,
        'is_class_representative' => $is_class_representative
    ];

    // Server-side validation
    $id_parts = explode('/', $id_number);
    if (count($id_parts) !== 3 || !ctype_alpha($id_parts[0]) || !ctype_digit($id_parts[1]) || !ctype_digit($id_parts[2])) {
        $error_message = 'Student ID format must be stream/number/batch (e.g., CSE/123/2024).';
    } elseif (!idExistsInAllowedList($pdo, $id_number)) {
        $error_message = 'Student ID not found in our records or not eligible for registration.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (emailExistsInRegistrations($pdo, $email)) {
        $error_message = 'This email address is already registered.';
    } elseif (empty($department_select_value)) {
        $error_message = 'Please select your department.';
    } elseif ($department_select_value === 'OtherDept' && empty($department_other_text_value)) {
        $error_message = 'Please specify your department if "Other" is selected.';
    } elseif ($department_select_value === 'OtherDept' && strlen($department_other_text_value) > 100) {
        $error_message = 'Specified department name is too long (max 100 characters).';
    } elseif (empty($club_select_value)) {
        $error_message = 'Please select your club membership status.';
    } elseif ($club_select_value === 'OtherClub' && empty($club_other_text_value)) {
        $error_message = 'Please specify your club if "Other" is selected.';
    } elseif ($club_select_value === 'OtherClub' && strlen($club_other_text_value) > 100) {
        $error_message = 'Specified club name is too long (max 100 characters).';
    } elseif (!in_array($is_class_representative, ['Yes', 'No'])) {
        $error_message = 'Invalid selection for class representative. Please select Yes or No.';
    } elseif (empty($password)) {
        $error_message = 'Password cannot be empty.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error_message = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error_message = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error_message = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+!-]/', $password)) {
        $error_message = 'Password must contain at least one special character.';
    } elseif ($password !== $password2) {
        $error_message = 'Passwords do not match.';
    } else {
        $_SESSION['registration_data'] = [
            'id_number' => $id_number,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'gender' => $gender,
            'email' => $email,
            'department' => $final_department_name,
            'club_membership' => $final_club_name,
            'is_class_representative' => $is_class_representative
        ];
        header("Location: otp_verify.php");
        exit();
    }

    if ($error_message) {
        echo "<script>alert('Validation Error: " . addslashes($error_message) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; padding-top: 50px; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
        .form-container { background: #fff; padding: 30px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 530px; margin: 50px auto; }
        .form-container h2 { text-align: center; margin-bottom: 10px; }
        .form-container p { text-align: center; margin-bottom: 30px; color: #777; }
        .form-container p a { color: #f97316; text-decoration: none; }
        .form-container p a:hover { text-decoration: underline; }
        .btn-custom { background-color:rgb(5, 9, 51); color: #fff; border-color:rgb(3, 5, 24); }
        .btn-custom:hover { background-color: #4754c4; border-color: #4754c4; }
        .error-message { color: #ed6565; font-size: 0.9em; display: block; margin-top: 5px; }
        .form-group { margin-bottom: 20px; }
        #department_other_container, #club_other_container { margin-top: 10px; /* Add some space for "Other" fields */ }
        .radio-group label { margin-right: 15px; font-weight: normal;}
    </style>
</head>
<body>
<?php include('view_banner.php'); ?>
<div class="form-container">
    <h2>Registration Form</h2>
    <p>Already have an account? <a href="../login.php">Sign in</a></p>

    <form method="POST" action="" id="registrationForm">
        <div class="form-group">
            <label for="id_number">Student ID:</label>
            <input type="text" name="id_number" id="id_number" class="form-control" placeholder="e.g., CSE/123/2024" value="<?php echo htmlspecialchars($formData['id_number'] ?? ''); ?>" required>
            <span class="error-message" id="id_number_error"></span>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="your.email@example.com" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
            <span class="error-message" id="email_error"></span>
        </div>

        <!-- Department Dropdown -->
        <div class="form-group">
            <label for="department_select">Department:</label>
            <select name="department_select" id="department_select" class="form-control" required>
                <option value="" disabled <?php echo empty($formData['department_select']) ? 'selected' : ''; ?>>Select Department</option>
                <option value="Mechanical" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Mechanical') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                <option value="Electrical" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Electrical') ? 'selected' : ''; ?>>Electrical Engineering</option>
                <option value="Computer" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Computer') ? 'selected' : ''; ?>>Computer Engineering/Science</option>
                <option value="Software" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Software') ? 'selected' : ''; ?>>Software Engineering</option>
                <option value="Water Supply" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Water Supply') ? 'selected' : ''; ?>>Water Supply Engineering</option>
                <option value="Hydro" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Hydro') ? 'selected' : ''; ?>>Hydropower Engineering</option>
                <option value="Irrigation" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Irrigation') ? 'selected' : ''; ?>>Irrigation Engineering</option>
                <option value="IT" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                <option value="Civil" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'Civil') ? 'selected' : ''; ?>>Civil Engineering</option>
                <option value="OtherDept" <?php echo (isset($formData['department_select']) && $formData['department_select'] === 'OtherDept') ? 'selected' : ''; ?>>Other</option>
            </select>
            <span class="error-message" id="department_select_error"></span>
        </div>

        <div class="form-group" id="department_other_container" style="display: none;">
            <label for="department_other_text">Please specify your department:</label>
            <input type="text" name="department_other_text" id="department_other_text" class="form-control" placeholder="Your Department Name" value="<?php echo htmlspecialchars($formData['department_other_text'] ?? ''); ?>">
            <span class="error-message" id="department_other_text_error"></span>
        </div>

        <!-- Club Membership Dropdown -->
        <div class="form-group">
            <label for="club_select">Club Membership:</label>
            <select name="club_select" id="club_select" class="form-control" required>
                <option value="" disabled <?php echo empty($formData['club_select']) ? 'selected' : ''; ?>>Select Club</option>
                <option value="Infoken" <?php echo (isset($formData['club_select']) && $formData['club_select'] === 'Infoken') ? 'selected' : ''; ?>>Infoken Club</option>
                <option value="Charity" <?php echo (isset($formData['club_select']) && $formData['club_select'] === 'Charity') ? 'selected' : ''; ?>>Charity Club</option>
                <option value="Minimedia" <?php echo (isset($formData['club_select']) && $formData['club_select'] === 'Minimedia') ? 'selected' : ''; ?>>Minimedia Club</option>
                <option value="None" <?php echo (isset($formData['club_select']) && $formData['club_select'] === 'None') ? 'selected' : ''; ?>>None</option>
                <option value="OtherClub" <?php echo (isset($formData['club_select']) && $formData['club_select'] === 'OtherClub') ? 'selected' : ''; ?>>Other</option>
            </select>
            <span class="error-message" id="club_select_error"></span>
        </div>

        <div class="form-group" id="club_other_container" style="display: none;">
            <label for="club_other_text">Please specify your club:</label>
            <input type="text" name="club_other_text" id="club_other_text" class="form-control" placeholder="Your Club Name" value="<?php echo htmlspecialchars($formData['club_other_text'] ?? ''); ?>">
            <span class="error-message" id="club_other_text_error"></span>
        </div>

        <!-- Class Representative Radio Buttons -->
        <div class="form-group">
            <label>Are you a Class Representative?</label>
            <div class="radio-group">
                <label for="representative_yes">
                    <input type="radio" name="is_class_representative" id="representative_yes" value="Yes" <?php echo (isset($formData['is_class_representative']) && $formData['is_class_representative'] === 'Yes') ? 'checked' : ''; ?> required> Yes
                </label>
                <label for="representative_no">
                    <input type="radio" name="is_class_representative" id="representative_no" value="No" <?php echo (isset($formData['is_class_representative']) && $formData['is_class_representative'] === 'No') ? 'checked' : (!isset($formData['is_class_representative']) ? 'checked' : ''); ?> required> No
                </label>
            </div>
            <span class="error-message" id="is_class_representative_error"></span>
        </div>


        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <span class="error-message" id="password_error"></span>
        </div>

        <div class="form-group">
            <label for="password2">Confirm Password:</label>
            <input type="password" name="password2" id="password2" class="form-control" placeholder="Confirm Password" required>
            <span class="error-message" id="password2_error"></span>
        </div>

        <div class="form-group">
            <label for="gender">Gender:</label>
            <select name="gender" id="gender" class="form-control" required>
                <option value="" disabled <?php echo empty($formData['gender']) || $formData['gender'] === '' ? 'selected' : ''; ?>>Select Gender</option>
                <option value="Male" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
            <span class="error-message" id="gender_error"></span>
        </div>

        <button type="submit" name="next" class="btn btn-custom btn-block">Next</button>
    </form>
</div>

<?php include('../footer.php'); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const idNumberInput = document.getElementById('id_number');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const password2Input = document.getElementById('password2');
    const genderSelect = document.getElementById('gender');

    const departmentSelect = document.getElementById('department_select');
    const departmentOtherContainer = document.getElementById('department_other_container');
    const departmentOtherText = document.getElementById('department_other_text');

    // --- NEW: Club elements ---
    const clubSelect = document.getElementById('club_select');
    const clubOtherContainer = document.getElementById('club_other_container');
    const clubOtherText = document.getElementById('club_other_text');

    // --- NEW: Class Representative elements ---
    const representativeRadios = document.querySelectorAll('input[name="is_class_representative"]');


    function showError(inputId, message) {
        const errorElement = document.getElementById(inputId + '_error');
        if (errorElement) { errorElement.textContent = message; }
    }

    function clearError(inputId) {
        const errorElement = document.getElementById(inputId + '_error');
        if (errorElement) { errorElement.textContent = ''; }
    }

    function toggleOtherField(selectElement, otherContainerElement, otherTextElement, otherValue) {
        if (selectElement.value === otherValue) {
            otherContainerElement.style.display = 'block';
            otherTextElement.required = true;
        } else {
            otherContainerElement.style.display = 'none';
            otherTextElement.value = '';
            otherTextElement.required = false;
            clearError(otherTextElement.id);
        }
    }

    // Initial checks for "Other" fields
    toggleOtherField(departmentSelect, departmentOtherContainer, departmentOtherText, 'OtherDept');
    toggleOtherField(clubSelect, clubOtherContainer, clubOtherText, 'OtherClub');


    departmentSelect.addEventListener('change', function() {
        toggleOtherField(departmentSelect, departmentOtherContainer, departmentOtherText, 'OtherDept');
        if (this.value !== "") clearError('department_select_error');
    });

    clubSelect.addEventListener('change', function() {
        toggleOtherField(clubSelect, clubOtherContainer, clubOtherText, 'OtherClub');
        if (this.value !== "") clearError('club_select_error');
    });

    representativeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            clearError('is_class_representative_error');
        });
    });


    form.addEventListener('submit', function(event) {
        let isValid = true;

        clearError('id_number');
        clearError('email');
        clearError('password');
        clearError('password2');
        clearError('gender');
        clearError('department_select');
        clearError('department_other_text');
        clearError('club_select'); // <-- NEW
        clearError('club_other_text'); // <-- NEW
        clearError('is_class_representative'); // <-- NEW

        // 1. Validate ID (existing)
        const idValue = idNumberInput.value.trim();
        const idRegex = /^[A-Za-z]+\/\d+\/\d+$/;
        if (!idRegex.test(idValue)) {
            showError('id_number', 'ID must be stream/number/batch (e.g., CSE/123/2024).');
            isValid = false;
        }

        // 2. Validate Email (existing)
        const emailValue = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailValue)) {
            showError('email', 'Please enter a valid email address.');
            isValid = false;
        }

        // 3. Validate Department (existing)
        const departmentSelectValue = departmentSelect.value;
        const departmentOtherTextValue = departmentOtherText.value.trim();
        if (departmentSelectValue === "") {
            showError('department_select', 'Please select your department.');
            isValid = false;
        } else if (departmentSelectValue === 'OtherDept') {
            if (departmentOtherTextValue === "") {
                showError('department_other_text', 'Please specify your department.');
                isValid = false;
            } else if (departmentOtherTextValue.length > 100) {
                showError('department_other_text', 'Department name is too long.');
                isValid = false;
            }
        }

        // --- NEW: 4. Validate Club Membership ---
        const clubSelectValue = clubSelect.value;
        const clubOtherTextValue = clubOtherText.value.trim();
        if (clubSelectValue === "") {
            showError('club_select', 'Please select your club membership.');
            isValid = false;
        } else if (clubSelectValue === 'OtherClub') {
            if (clubOtherTextValue === "") {
                showError('club_other_text', 'Please specify your club.');
                isValid = false;
            } else if (clubOtherTextValue.length > 100) {
                showError('club_other_text', 'Club name is too long.');
                isValid = false;
            }
        }

        // --- NEW: 5. Validate Class Representative ---
        let representativeSelected = false;
        representativeRadios.forEach(radio => {
            if (radio.checked) representativeSelected = true;
        });
        if (!representativeSelected) {
            showError('is_class_representative', 'Please select if you are a class representative.');
            isValid = false;
        }


        // 6. Validate Password (existing - renumbered)
        const passwordValue = passwordInput.value;
        let passwordErrorMessage = '';
        if (passwordValue.length < 8) passwordErrorMessage += 'Min 8 chars. ';
        if (!/[A-Z]/.test(passwordValue)) passwordErrorMessage += 'Needs uppercase. ';
        if (!/[a-z]/.test(passwordValue)) passwordErrorMessage += 'Needs lowercase. ';
        if (!/[0-9]/.test(passwordValue)) passwordErrorMessage += 'Needs number. ';
        if (!/[\!\@\#\$\%\^\&\*\(\)\_\+\-\=\[\]\{\}\;\:\'\"\,\<\.\>\/\?\~\`\|\\ ]/.test(passwordValue)) {
             passwordErrorMessage += 'Needs special char. ';
        }
        if (passwordErrorMessage) {
            showError('password', passwordErrorMessage.trim());
            isValid = false;
        }

        // 7. Validate Confirm Password (existing - renumbered)
        const password2Value = password2Input.value;
        if (passwordValue !== password2Value) {
            showError('password2', 'Passwords do not match.');
            isValid = false;
        } else if (password2Value === "" && passwordValue !== "") {
             showError('password2', 'Please confirm your password.');
             isValid = false;
        }

        // 8. Validate Gender (existing - renumbered)
        if (genderSelect.value === "") {
            showError('gender', 'Please select your gender.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Optional: Real-time validation (existing input event listeners)
    idNumberInput.addEventListener('input', function() { /* ... existing ... */ });
    emailInput.addEventListener('input', function() { /* ... existing ... */ });
    passwordInput.addEventListener('input', function() { /* ... existing ... */ });
    password2Input.addEventListener('input', function() { /* ... existing ... */ });

    departmentOtherText.addEventListener('input', function() {
        if (departmentSelect.value === 'OtherDept') {
            const value = this.value.trim();
            if (value === "") showError('department_other_text', 'Department cannot be empty.');
            else if (value.length > 100) showError('department_other_text', 'Department name too long.');
            else clearError('department_other_text');
        }
    });

    // --- NEW: Real-time for Club Other Text ---
    clubOtherText.addEventListener('input', function() {
        if (clubSelect.value === 'OtherClub') {
            const value = this.value.trim();
            if (value === "") showError('club_other_text', 'Club name cannot be empty.');
            else if (value.length > 100) showError('club_other_text', 'Club name too long.');
            else clearError('club_other_text');
        }
    });
});
</script>

</body>
</html>