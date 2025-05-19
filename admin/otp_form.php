<?php
require_once('dbcon.php');
include('head.php');
session_start();

// Function to generate a random OTP
function generateOTP() {
    return rand(100000, 999999);
}

// Function to update OTP in the database
function updateOTPInDatabase($pdo, $user_id) {
    $otp = generateOTP();
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
    $otp_expiry = time() + 120; // 2 minutes

    $stmt = $pdo->prepare("UPDATE user SET otp_hash = ?, otp_expiry = ? WHERE user_id = ?");
    $stmt->execute([$otp_hash, $otp_expiry, $user_id]);

    return $otp;
}

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('User ID not found in session. Please login again.'); window.location = 'index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Determine if "Get OTP" or "Resend OTP" should be displayed
$showGetOTP = !isset($_SESSION['otp_requested']) || $_SESSION['otp_requested'] !== true;

// Handle Get OTP request
if (isset($_POST['get_otp'])) {
    // Generate new OTP and update in database
    $otp = updateOTPInDatabase($pdo, $user_id);

    // Simulate sending OTP (replace with actual SMS sending)
    $stmt = $pdo->prepare("SELECT phone FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $phone_number = $user['phone'];
        // Shorten the phone number (show only last 4 digits)
        $short_phone = substr($phone_number, -4);
    }

    echo "<script>
        var otp = '$otp';
        alert('Simulation OTP sent to: ...$short_phone. OTP is: ' + otp + '\\nClick OK to copy the OTP.');
        navigator.clipboard.writeText(otp);
    </script>";

    $_SESSION['otp_requested'] = true; // OTP has been requested, switch to resend
    // Fetch OTP expiry time from the database
    $stmt = $pdo->prepare("SELECT otp_expiry FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['otp_expiry'] = $user['otp_expiry'];
    }
}

// Handle resend OTP request
if (isset($_POST['resend_otp'])) {
    // Generate new OTP and update in database
    $otp = updateOTPInDatabase($pdo, $user_id);

    // Simulate sending OTP (replace with actual SMS sending)
    $stmt = $pdo->prepare("SELECT phone FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $phone_number = $user['phone'];
        // Shorten the phone number (show only last 4 digits)
        $short_phone = substr($phone_number, -4);
    }

    echo "<script>
        var otp = '$otp';
        alert('New Simulation OTP sent to: ...$short_phone. OTP is: ' + otp + '\\nClick OK to copy the OTP.');
        navigator.clipboard.writeText(otp);
    </script>";

    // Fetch OTP expiry time from the database
    $stmt = $pdo->prepare("SELECT otp_expiry FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
     if ($user) {
        $_SESSION['otp_expiry'] = $user['otp_expiry'];
    }
}

// Fetch OTP expiry time from the database
$otp_expiry = $_SESSION['otp_expiry'] ?? 0;
$otp_expired = ($otp_expiry != 0 && time() > $otp_expiry);
?>

<body>
    <?php include('index_banner.php'); ?>
    <div class="content">
        <div class="container_1">
            <div class="form-panel">
                <div class="form-heading">
                    <center>OTP Verification</center>
                </div>
                <form method="post" action="">
                    <div class="form-field">
                        <label for="otp">Enter OTP Code:</label>
                        <div style="display: flex; align-items: center;">
                            <input type="text" name="otp" id="otp">
                            <?php if ($showGetOTP): ?>
                                <button type="submit" name="get_otp" id="getOTPButton" style="margin-left: 5px;">Get OTP</button>
                            <?php else: ?>
                                <button type="submit" name="resend_otp" id="resendButton" style="margin-left: 5px;" <?php if (!$otp_expired) echo 'disabled'; ?>>
                                    Resend OTP
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="btn btn-lg btn-success btn-block" type="submit" name="login" onclick="document.getElementById('otp').required = true;">Login</button>
                    <br>
                    <center><span id="countdown"></span></center>
                </form>
            </div>
        </div>
    </div>
    <?php include('script.php'); ?>
    <?php include('../footer.php'); ?>

    <script>
        var expiry = <?php echo $otp_expiry; ?>;
        var countdownElement = document.getElementById('countdown');
        var resendButton = document.getElementById('resendButton');
        var otpInput = document.getElementById('otp');

        function updateCountdown() {
            var now = new Date().getTime();
            var distance = expiry * 1000 - now;

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = 'Time remaining: ' + minutes + 'm ' + seconds + 's ';

            if (distance < 0) {
                clearInterval(x);
                countdownElement.innerHTML = 'OTP expired!';
                if (resendButton) {
                    resendButton.disabled = false; // Enable the button
                }
            }
        }

        // Only start the countdown if OTP has been requested
        if (expiry > 0) {
            var x = setInterval(updateCountdown, 1000);
        } else {
            countdownElement.innerHTML = ''; // Clear the countdown if OTP hasn't been requested
        }

        // Disable resend button initially if OTP is not expired
        if (resendButton && expiry * 1000 > new Date().getTime()) {
            resendButton.disabled = true;
        }
    </script>
</body>

</html>

<?php
// Verify OTP
if (isset($_POST['login'])) {
    $entered_otp = $_POST['otp'];

    // Retrieve OTP hash and expiry from the database
    $stmt = $pdo->prepare("SELECT otp_hash, otp_expiry FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $otp_hash = $user['otp_hash'];
        $otp_expiry = $user['otp_expiry'];

        if (time() > $otp_expiry) {
            echo "<script>alert('OTP has expired. Please resend!');</script>";
            exit();
        }

        if (password_verify($entered_otp, $otp_hash)) {
            // OTP is valid
            // Log the login attempt
            $log_stmt = $pdo->prepare("INSERT INTO logins(username) VALUES(?)");
            $log_stmt->execute([$_SESSION['username']]);

            // Set session variable
            $_SESSION['id'] = $user_id;

            // Redirect to the candidate page
            echo "<script>alert('Welcome!'); window.location = 'voters.php';</script>";
            exit();
        } else {
            // Invalid OTP
            echo "<script>alert('Invalid OTP!');</script>";
            exit();
        }
    } else {
        // User not found (this should not happen if user_id is set correctly)
        echo "<script> alert('User not found. Please login again.');
         window.location = 'index.php'; </script>";
        exit();
    }
}
?>