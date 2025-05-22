<?php
session_start();

// --- INCLUDE YOUR DATABASE CONNECTION FILE HERE ---
// This line you already have. Make sure $pdo_conn (or your PDO variable) is defined by it.
include('admin/dbcon.php');
// If your dbcon.php uses a different variable name for the PDO object (e.g., $conn, $db),
// you MUST replace $pdo_conn with that name in the database section below.


// PHPMailer Use Statements
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Manually include PHPMailer files
if (file_exists('PHPMailer-6.10.0/src/Exception.php')) require 'PHPMailer-6.10.0/src/Exception.php'; else die('Error: PHPMailer Exception.php not found.');
if (file_exists('PHPMailer-6.10.0/src/PHPMailer.php')) require 'PHPMailer-6.10.0/src/PHPMailer.php'; else die('Error: PHPMailer PHPMailer.php not found.');
if (file_exists('PHPMailer-6.10.0/src/SMTP.php')) require 'PHPMailer-6.10.0/src/SMTP.php'; else die('Error: PHPMailer SMTP.php not found.');

// Include head.php safely
if (file_exists('head.php')) {
    include('head.php');
} else {
    echo "<!-- head.php not found -->";
}

// Constants
define('OTP_EXPIRY', 120); // 2 minutes

// --- MODIFIED OTP GENERATION AND EMAIL SENDING LOGIC ---
if (isset($_GET['resend']) && $_GET['resend'] === "true") {

    // CRITICAL: Check if the PDO database connection variable is set and valid.
    // *** REPLACE '$pdo_conn' WITH THE ACTUAL VARIABLE NAME FROM YOUR 'admin/dbcon.php' IF DIFFERENT ***
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $_SESSION['otp_message'] = "Error: Database connection is not available. Cannot fetch email.";
        $_SESSION['otp_message_type'] = 'danger';
        error_log("OTP Resend Error: PDO Database connection object (\$pdo_conn or your variable) not available or not a PDO instance.");
        header("Location: otp_form.php");
        exit();
    }

    // Ensure username is in session (should be set during initial login)
    if (!isset($_SESSION['username'])) {
        $_SESSION['otp_message'] = "Error: User session not found. Please login again.";
        $_SESSION['otp_message_type'] = 'danger';
        $log_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'N/A';
        error_log("OTP Resend Error: username not found in session. User ID: " . $log_user_id);
        header("Location: login.php");
        exit();
    }

    $session_username = $_SESSION['username'];
    $user_email_to_send_otp = null;

    try {
        // Fetch email from database based on username using PDO
        // *** REPLACE '$pdo_conn' WITH THE ACTUAL VARIABLE NAME FROM YOUR 'admin/dbcon.php' IF DIFFERENT ***
        $sql = "SELECT email FROM users WHERE username = :username LIMIT 1"; // Adjust table/column names if needed
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $session_username, PDO::PARAM_STR);
        $stmt->execute();
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_row && isset($user_row['email'])) {
            $user_email_to_send_otp = $user_row['email'];
        } else {
            $_SESSION['otp_message'] = "Error: Could not find an email address associated with the username '" . htmlspecialchars($session_username) . "'.";
            $_SESSION['otp_message_type'] = 'danger';
            error_log("OTP Resend Error: Email not found in DB for username: " . $session_username);
        }
    } catch (PDOException $e) {
        $_SESSION['otp_message'] = "Database error while trying to fetch your email. Please try again later.";
        $_SESSION['otp_message_type'] = 'danger';
        error_log("OTP Resend PDO DB Error for username: " . $session_username . " - " . $e->getMessage());
    }


    if ($user_email_to_send_otp) {
        $_SESSION['otp'] = rand(100000, 999999);
        $_SESSION['otp_expire'] = time() + OTP_EXPIRY;
        $otp_code = $_SESSION['otp'];

        $mail = new PHPMailer(true);
        try {
            // --- SERVER SETTINGS FOR MAILTRAP ---
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Or you can use the number 4 for very verbose output // Change to SMTP::DEBUG_SERVER for detailed logs if needed
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '2c6302b1d58448';                 // YOUR MAILTRAP USERNAME
            $mail->Password   = '2a4edeb25107e1'; // <<<< REPLACE WITH YOUR FULL MAILTRAP PASSWORD (e.g., ********0485)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Use STARTTLS
            $mail->Port       = 2525;                              // Port for STARTTLS

            // Optional: Disable SSL verification for Mailtrap sandbox if you encounter SSL issues
            // This is generally safe for Mailtrap's sandbox environment during testing.
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // --- END MAILTRAP SERVER SETTINGS ---

            $mail->setFrom('otp-service@your-app.test', 'Your Voting App (Test)');
            $mail->addAddress($user_email_to_send_otp, 'Test User'); // OTP is sent "to" this user, caught by Mailtrap

            $mail->isHTML(true);
            $mail->Subject = '[TEST OTP] Your Verification Code - Voting App';
            $session_username_display = isset($session_username) ? htmlspecialchars($session_username) : 'User';
            $mail->Body    = "Dear {$session_username_display},<br><br>Your One-Time Password (OTP) for testing is: <b>{$otp_code}</b><br><br>This OTP is valid for " . (OTP_EXPIRY / 60) . " minutes.<br><br>This is a test email captured by Mailtrap.<br><br>Thanks,<br>Your Application Team";
            $mail->AltBody = "Dear {$session_username_display},\n\nYour One-Time Password (OTP) for testing is: {$otp_code}\n\nThis OTP is valid for " . (OTP_EXPIRY / 60) . " minutes.\n\nThis is a test email captured by Mailtrap.\n\nThanks,\nYour Application Team";

            $mail->send();
            $_SESSION['otp_message'] = 'A test OTP has been sent and should be captured by Mailtrap (intended for: ' . htmlspecialchars($user_email_to_send_otp) . ').';
            $_SESSION['otp_message_type'] = 'success';

        } catch (Exception $e) {
            $_SESSION['otp_message'] = "Mailtrap Test Email Error: {$mail->ErrorInfo}.";
            $_SESSION['otp_message_type'] = 'danger';
            $log_user_id_catch = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'N/A';
            $log_username_catch = isset($session_username) ? $session_username : 'N/A';
            error_log("PHPMailer (Mailtrap) Error for {$user_email_to_send_otp} (User ID: " . $log_user_id_catch . ", Username: " . $log_username_catch . "): {$mail->ErrorInfo}");
        }
    }
    header("Location: otp_form.php");
    exit();
}
?>

<body>
<?php
if (file_exists('view_banner.php')) include('view_banner.php'); else echo "<!-- view_banner.php not found -->";
?>
<div class="content">
    <div class="container_1">
        <div class="form-panel">
            <div class="form-heading">Email OTP Verification (Mailtrap Test)</div>

            <?php
            if (isset($_SESSION['otp_message'])) {
                $message_type = isset($_SESSION['otp_message_type']) ? $_SESSION['otp_message_type'] : 'info';
                $alert_class = ($message_type === 'danger') ? 'alert-danger' : 'alert-success';
                echo '<div style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;" class="' . htmlspecialchars($alert_class) . '">' . htmlspecialchars($_SESSION['otp_message']) . '</div>';
                unset($_SESSION['otp_message'], $_SESSION['otp_message_type']);
            }
            ?>

            <form role="form" method="post" class="index-form" action="otp_form.php">
                <div class="form-field">
                    <label for="otp_input_id">Enter OTP (from Mailtrap):</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="otp_input_id" name="otp_input" class="form-control" placeholder="Enter 6-digit OTP" required pattern="\d{6}" title="OTP must be 6 digits" style="flex: 1;">
                        <button type="button" id="getCodeBtn" class="btn btn-warning">
                            <?php
                                if (isset($_SESSION['otp']) && isset($_SESSION['otp_expire']) && time() < $_SESSION['otp_expire']) {
                                    echo 'Resend OTP to Mailtrap';
                                } else {
                                    echo 'Get OTP via Mailtrap';
                                }
                            ?>
                        </button>
                    </div>
                </div>
                <div id="timer" style="text-align:center; margin-top:10px; font-weight: bold;"></div>
                <center>
                    <button type="submit" class="btn btn-lg btn-success btn-block" name="login">Verify & Login</button>
                </center>
            </form>

            <?php
            // OTP Verification Logic (Backend - Unchanged as requested)
            $otp_submit_button_name = 'login';
            if (isset($_POST[$otp_submit_button_name])) {
                if (!isset($_SESSION['user_id'])) {
                    error_log("OTP Verify Attempt: Critical - user_id not found in session.");
                    echo "<script type='text/javascript'>alert('Your session is invalid. Please login again.'); window.location='login.php';</script>";
                    exit();
                }
                $user_otp = isset($_POST['otp_input']) ? trim($_POST['otp_input']) : '';
                if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expire'])) {
                    $_SESSION['otp_message'] = 'No OTP found in session. Please request a new one.';
                    $_SESSION['otp_message_type'] = 'danger';
                    echo "<script type='text/javascript'>window.location.href = 'otp_form.php';</script>";
                    exit();
                } elseif (time() > $_SESSION['otp_expire']) {
                    $_SESSION['otp_message'] = 'OTP has expired. Please request a new one.';
                    $_SESSION['otp_message_type'] = 'danger';
                    unset($_SESSION['otp'], $_SESSION['otp_expire']);
                    echo "<script type='text/javascript'>window.location.href = 'otp_form.php';</script>";
                    exit();
                } elseif ($user_otp == $_SESSION['otp']) {
                    $_SESSION['otp_verified'] = true;
                    unset($_SESSION['otp'], $_SESSION['otp_expire']);
                    session_write_close();
                    if (isset($_SESSION['user_role'])) {
                        $role_id = $_SESSION['user_role'];
                        $redirect_url = '';
                        if ($role_id == 1) $redirect_url = 'admin/index.php';
                        elseif ($role_id == 2) $redirect_url = 'admin2/index.php';
                        elseif ($role_id == 3) $redirect_url = 'voter_home.php';
                        else {
                            error_log("OTP Verified: Unknown role_id '{$role_id}' for user_id '{$_SESSION['user_id']}'.");
                            if (session_status() == PHP_SESSION_NONE) { session_start(); }
                            $_SESSION['login_message'] = 'Login successful, but your user role is unrecognized. Please contact support.';
                            echo "<script type='text/javascript'>window.location='login.php';</script>";
                            exit();
                        }
                        echo "<script type='text/javascript'>window.location='" . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') . "';</script>";
                        exit();
                    } else {
                        error_log("OTP Verified: Critical - role_id NOT found in session for user_id '{$_SESSION['user_id']}'.");
                        if (session_status() == PHP_SESSION_NONE) { session_start(); }
                        $_SESSION['login_message'] = 'Critical error: User role information is missing. Please login again.';
                        echo "<script type='text/javascript'>window.location='login.php';</script>";
                        exit();
                    }
                } else {
                    $_SESSION['otp_message'] = 'Incorrect OTP. Please try again.';
                    $_SESSION['otp_message_type'] = 'danger';
                    echo "<script type='text/javascript'>window.location.href = 'otp_form.php';</script>";
                    exit();
                }
            }
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    const getCodeBtn = document.getElementById("getCodeBtn");
    const timerElement = document.getElementById("timer");
    let intervalId;
    function startTimer(duration) {
        let countdown = duration;
        if (getCodeBtn) { getCodeBtn.disabled = true; /* getCodeBtn.innerText = "Resend OTP to Mailtrap"; */ } // Text already set by PHP
        function updateTimerDisplay() {
            if (countdown <= 0) {
                clearInterval(intervalId);
                if (timerElement) timerElement.innerText = "OTP expired. Please request a new one.";
                if (getCodeBtn) { getCodeBtn.innerText = "Get OTP via Mailtrap"; getCodeBtn.disabled = false; }
            } else {
                const mins = Math.floor(countdown / 60);
                const secs = countdown % 60;
                if (timerElement) timerElement.innerText = `OTP expires in: ${mins}:${secs.toString().padStart(2, '0')}`;
                countdown--;
            }
        }
        updateTimerDisplay();
        intervalId = setInterval(updateTimerDisplay, 1000);
    }
    <?php
    if (isset($_SESSION['otp']) && isset($_SESSION['otp_expire']) && time() < $_SESSION['otp_expire']) {
        $remainingTime = $_SESSION['otp_expire'] - time();
        echo "startTimer(" . $remainingTime . ");\n";
    } else {
        echo 'if (timerElement) timerElement.innerText = "Click button to get OTP (via Mailtrap).";';
        // getCodeBtn text is set by PHP based on session state
    }
    ?>
    if (getCodeBtn) {
        getCodeBtn.addEventListener("click", () => {
            getCodeBtn.disabled = true; getCodeBtn.innerText = "Sending...";
            window.location.href = "?resend=true";
        });
    }
</script>

<?php
if (file_exists('footer.php')) include('footer.php'); else echo "<!-- footer.php not found -->";
if (file_exists('script.php')) include('script.php'); else echo "<!-- script.php not found -->";
?>
</body>
</html>