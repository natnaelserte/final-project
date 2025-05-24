<?php
session_start();

include('admin/dbcon.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists('PHPMailer-6.10.0/src/Exception.php')) require 'PHPMailer-6.10.0/src/Exception.php'; else die('Error: PHPMailer Exception.php not found.');
if (file_exists('PHPMailer-6.10.0/src/PHPMailer.php')) require 'PHPMailer-6.10.0/src/PHPMailer.php'; else die('Error: PHPMailer PHPMailer.php not found.');
if (file_exists('PHPMailer-6.10.0/src/SMTP.php')) require 'PHPMailer-6.10.0/src/SMTP.php'; else die('Error: PHPMailer SMTP.php not found.');

if (file_exists('head.php')) {
    include('head.php');
}

define('OTP_EXPIRY', 120);

if (isset($_GET['resend']) && $_GET['resend'] === "true") {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $_SESSION['otp_message'] = "Error: Database connection is not available. Cannot fetch email.";
        $_SESSION['otp_message_type'] = 'danger';
        error_log("OTP Resend Error: PDO Database connection object (\$pdo_conn or your variable) not available or not a PDO instance.");
        header("Location: otp_form.php");
        exit();
    }

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
        $sql = "SELECT email FROM users WHERE username = :username LIMIT 1";
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
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '41ad075c726835';
            $mail->Password   = '38c7d95eb94a24';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525;
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->setFrom('otp-service@your-app.test', 'Your Voting App (Test)');
            $mail->addAddress($user_email_to_send_otp, 'Test User');

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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
    body {
        background-color: #f7f7f7; 
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between; 
        font-family: Arial, sans-serif; 
    }
    .otp-container-custom { 
        margin-top: 30px;   
        margin-bottom: 50px;
        max-width: 530px; 
        padding-left: 15px;
        padding-right: 15px;
        margin-left: auto;
        margin-right: auto;
    }
    .otp-form-wrapper { 
        background: #f7f7f7; 
        padding: 30px 15px; 
        border-radius: 8px;
        text-align: center; 
    }
    .otp-box { 
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        padding: 30px;
        margin-top: 20px; 
    }
    .otp-box h4 {
        text-align: center;
        margin-bottom: 15px; 
        font-size: 22px; 
        font-weight: 600;
    }
    .form-group { 
        margin-bottom: 20px;
    }
    .form-control { 
        height: 45px; 
        border-radius: 4px;
    }
    .btn-primary-custom { 
        background-color: #337ab7; 
        border-color: #2e6da4;
        color: white;
        padding: 10px 15px;
        font-size: 16px;
        line-height: 1.5;
        border-radius: 4px;
        width: 100%; 
        font-weight: bold;
    }
    .btn-primary-custom:hover {
        background-color: #286090;
        border-color: #204d74;
    }
    .btn-warning-custom { 
        background-color: #f0ad4e;
        border-color: #eea236;
        color: white;
    }
    .btn-warning-custom:hover {
        background-color: #ec971f;
        border-color: #d58512;
    }
    .alert-custom-otp {
        margin-top: 10px;
        margin-bottom: 20px;
        padding: 10px 15px;
        border-radius: 4px;
    }
    .input-group-otp { 
        display: flex;
        gap: 10px;
        align-items: center; 
    }
    .input-group-otp .form-control {
        flex: 1; 
    }
    .text-muted-otp {
        color: #777;
        margin-bottom: 25px;
        font-size: 16px;
    }
     footer { 
        margin-top: auto;
        padding: 20px;
        background-color: #f8f9fa; 
        width: 100%;
        text-align: center;
    }
</style>

<body>
<?php
if (file_exists('view_banner.php')) include('view_banner.php');
?>

<div class="otp-container-custom">
    <div class="otp-form-wrapper">
        <h2 class="text-center">Two-Factor Authentication</h2> 
        <p class="text-center text-muted-otp">Please verify your account by entering the OTP sent to your email (via Mailtrap).</p>

        <div class="otp-box">
            <h4>Verify your Account</h4>
            
            <?php
            if (isset($_SESSION['otp_message'])) {
                $message_type = isset($_SESSION['otp_message_type']) ? $_SESSION['otp_message_type'] : 'info';
                $alert_class = ($message_type === 'danger') ? 'alert-danger' : (($message_type === 'success') ? 'alert-success' : 'alert-info');
                echo '<div class="alert ' . htmlspecialchars($alert_class) . ' alert-custom-otp" role="alert">' . htmlspecialchars($_SESSION['otp_message']) . '</div>';
                unset($_SESSION['otp_message'], $_SESSION['otp_message_type']);
            }
            ?>

            <form role="form" method="post" class="index-form" action="otp_form.php">
                <div class="form-group">
                    <div class="input-group-otp"> 
                        <input type="text" id="otp_input_id" name="otp_input" class="form-control" placeholder="Enter OTP" required pattern="\d{6}" title="OTP must be 6 digits">
                        <button type="button" id="getCodeBtn" class="btn btn-warning-custom"> 
                            <?php
                                if (isset($_SESSION['otp']) && isset($_SESSION['otp_expire']) && time() < $_SESSION['otp_expire']) {
                                    echo 'Resend OTP';
                                } else {
                                    echo 'Get OTP';
                                }
                            ?>
                        </button>
                    </div>
                </div>

                <div id="timer" style="text-align:center; margin-bottom:15px; margin-top: -10px; font-weight: bold; color: #555;"></div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary-custom" name="login">Verify</button> 
                </div>
            </form>

            <?php
            $otp_submit_button_name = 'login';
            if (isset($_POST[$otp_submit_button_name])) {
                if (!isset($_SESSION['user_id'])) {
                    error_log("OTP Verify Attempt: Critical - user_id not found in session.");
                    echo "<script type='text/javascript'>alert('Your session is invalid. Please login again.'); window.location='login.php';</script>";
                    exit();
                }
                $user_otp = isset($_POST['otp_input']) ? trim($_POST['otp_input']) : '';
                if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expire'])) {
                    $_SESSION['otp_message'] = 'incorrect otp. Please request a new one.';
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
                        if ($role_id == 1 || $role_id == 4) { // Check for role_id 1 OR 4
                            $redirect_url = 'admin/index.php';
                        } elseif ($role_id == 2) {
                            $redirect_url = 'admin2/index.php';
                        } elseif ($role_id == 3) {
                            $redirect_url = 'voter_home.php';
                        } else {
                            // This 'else' block will now only be reached if role_id is NOT 1, 4, 2, or 3.
                            error_log("OTP Verified: Unknown role_id '{$role_id}' for user_id '{$_SESSION['user_id']}'.");
                            // It's generally better to have session_start() at the very beginning of your script.
                            // However, keeping your original structure for this specific change:
                            if (session_status() == PHP_SESSION_NONE) {
                                session_start();
                            }
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
        if (getCodeBtn) { getCodeBtn.disabled = true; } 
        function updateTimerDisplay() {
            if (countdown <= 0) {
                clearInterval(intervalId);
                if (timerElement) timerElement.innerText = "OTP expired. Please request a new one.";
                if (getCodeBtn) { getCodeBtn.innerText = "Get OTP"; getCodeBtn.disabled = false; }
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
        echo 'if (timerElement) timerElement.innerText = "Click button to get OTP.";';
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
if (file_exists('footer.php')) include('footer.php');
if (file_exists('script.php')) include('script.php');
?>
</body>
</html>