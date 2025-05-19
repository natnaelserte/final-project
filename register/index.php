<?php 
session_start(); // MUST be at the VERY top
include('head.php');
require_once 'dbcon.php';


// Function to check if ID exists
function idExists($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ids WHERE id_number = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

if (isset($_POST['next'])) {
    $id_number = filter_var($_POST['id_number'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Add email field

    $_SESSION['form_data'] = [
        'id_number' => $id_number,
        'gender' => $gender,
        'email' => $email, // Store email in session
    ];

    if (!idExists($pdo, $id_number)) {
        echo "<script>alert('Student ID not found.');</script>";
    } elseif ($password !== $password2) {
        echo "<script>alert('Passwords do not match.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address.');</script>";
    } else {
        $_SESSION['registration_data'] = [
            'id_number' => $id_number,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'gender' => $gender,
            'email' => $email, // Add email to registration data
        ];
        header("Location: otp_verify.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f3f4f6;
            padding-top: 50px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 530px;
            margin: 50px auto;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        .form-container p {
            text-align: center;
            margin-bottom: 30px;
            color: #777;
        }
        .form-container p a {
            color: #f97316;
            text-decoration: none;
        }
        .form-container p a:hover {
            text-decoration: underline;
        }
        .btn-custom {
            background-color:rgb(5, 9, 51);
            color: #fff;
            border-color:rgb(3, 5, 24);
        }
        .btn-custom:hover {
            background-color: #4754c4;
            border-color: #4754c4;
        }
    </style>
</head>
<body>
<?php include('index_banner.php'); ?>
<div class="form-container">
    <h2>Registration Form</h2>
    <p>Already have an account? <a href="#">Sign in</a></p>

    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="id_number" class="form-control" placeholder="Student ID" value="<?php echo $formData['id_number'] ?? ''; ?>" required>
        </div>

        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo $formData['email'] ?? ''; ?>" required>
        </div>

        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <div class="form-group">
            <input type="password" name="password2" class="form-control" placeholder="Confirm Password" required>
        </div>

        <div class="form-group">
            <select name="gender" class="form-control" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male" <?php echo isset($formData['gender']) && $formData['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo isset($formData['gender']) && $formData['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <button type="submit" name="next" class="btn btn-custom btn-block">Next</button>
    </form>
</div>

<?php include('../footer.php'); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</body>
</html>
