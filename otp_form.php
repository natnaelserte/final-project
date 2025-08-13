<?php
// Ensure session_start() is at the very beginning, before any output or includes that might output.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('admin/dbcon.php'); // Assuming this doesn't output anything

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check and require PHPMailer files
if (file_exists('PHPMailer-6.10.0/src/Exception.php')) require 'PHPMailer-6.10.0/src/Exception.php'; else die('Error: PHPMailer Exception.php not found.');
if (file_exists('PHPMailer-6.10.0/src/PHPMailer.php')) require 'PHPMailer-6.10.0/src/PHPMailer.php'; else die('Error: PHPMailer PHPMailer.php not found.');
if (file_exists('PHPMailer-6.10.0/src/SMTP.php')) require 'PHPMailer-6.10.0/src/SMTP.php'; else die('Error: PHPMailer SMTP.php not found.');

// Include head.php *after* session start and any potential redirects or die() statements.
// If head.php outputs HTML, it must come after logic that might use header().
// For now, assuming this structure is intentional.
if (file_exists('head.php')) {
    // include('head.php'); // Let's move this down after PHP logic that might redirect
}

define('OTP_EXPIRY', 120); // 120 seconds = 2 minutes

if (isset($_GET['resend']) && $_GET['resend'] === "true") {
    // ... (Your existing OTP resend logic - this part seems okay) ...
    // For brevity, I'm keeping your resend logic as is.
    // Ensure $pdo is correctly named and initialized.
    if (!isset($pdo) || !($pdo instanceof PDO)) { // Or $conn if that's your PDO variable name
        $_SESSION['otp_message'] = "Error: Database connection is not available. Cannot fetch email.";
        $_SESSION['otp_message_type'] = 'danger';
        error_log("OTP Resend Error: PDO Database connection object not available or not a PDO instance.");
        header("Location: otp_form.php"); // Redirect back to OTP form
        exit();
    }

    if (!isset($_SESSION['username'])) {
        $_SESSION['otp_message'] = "Error: User session not found. Please login again.";
        $_SESSION['otp_message_type'] = 'danger';
        $log_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'N/A';
        error_log("OTP Resend Error: username not found in session. User ID: " . $log_user_id);
        header("Location: login.php"); // Redirect to login
        exit();
    }

    $session_username = $_SESSION['username'];
    $user_email_to_send_otp = null;

    try {
        $sql = "SELECT email FROM users WHERE username = :username LIMIT 1"; // Assuming table is 'users'
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $session_username, PDO::PARAM_STR);
        $stmt->execute();
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_row && isset($user_row['email'])) {
            $user_email_to_send_otp = $user_row['email'];
        } else {
            $_SESSION['otp_message'] = "Error: Could not find an email address for username '" . htmlspecialchars($session_username) . "'.";
            $_SESSION['otp_message_type'] = 'danger';
            error_log("OTP Resend Error: Email not found in DB for username: " . $session_username);
            // No redirect here, let the otp_form.php display the message
        }
    } catch (PDOException $e) {
        $_SESSION['otp_message'] = "Database error fetching email. Try again.";
        $_SESSION['otp_message_type'] = 'danger';
        error_log("OTP Resend PDO DB Error for username " . $session_username . ": " . $e->getMessage());
        // No redirect here, let the otp_form.php display the message
    }

    if ($user_email_to_send_otp) {
        $_SESSION['otp'] = rand(100000, 999999);
        $_SESSION['otp_expire'] = time() + OTP_EXPIRY;
        $otp_code = $_SESSION['otp'];

        $mail = new PHPMailer(true);
        try {
            // ... (Your PHPMailer configuration using Mailtrap) ...
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Use DEBUG_SERVER for detailed logs from Mailtrap
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'b861ac03d48b61'; // Replace with your Mailtrap username
            $mail->Password   = '19954cd8f5c94a'; // Replace with your Mailtrap password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_SMTPS if port 465/587 with SSL
            $mail->Port       = 2525; // or 587 for TLS, 465 for SSL
            
            // Optional: For development with local servers or self-signed certs, not usually needed for Mailtrap
            /*
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            */
            
            $mail->setFrom('otp-service@your-app.test', 'Your Voting App (Test)'); // From address
            $mail->addAddress($user_email_to_send_otp); // Recipient from DB

            $mail->isHTML(true);
            $mail->Subject = '[TEST OTP] Your Verification Code - Voting App';
            $session_username_display = htmlspecialchars($session_username);
            $mail->Body    = "Dear {$session_username_display},<br><br>Your One-Time Password (OTP) for testing is: <b>{$otp_code}</b><br><br>This OTP is valid for " . (OTP_EXPIRY / 60) . " minutes.<br><br>This is a test email captured by Mailtrap.<br><br>Thanks,<br>Your Application Team";
            $mail->AltBody = "Dear {$session_username_display},\n\nYour One-Time Password (OTP) for testing is: {$otp_code}\n\nThis OTP is valid for " . (OTP_EXPIRY / 60) . " minutes.\n\nThis is a test email captured by Mailtrap.\n\nThanks,\nYour Application Team";

            $mail->send();
            $_SESSION['otp_message'] = 'A test OTP has been sent. Check Mailtrap (intended for: ' . htmlspecialchars($user_email_to_send_otp) . ').';
            $_SESSION['otp_message_type'] = 'success';

        } catch (Exception $e) {
            $_SESSION['otp_message'] = "Mailtrap Test Email Error: {$mail->ErrorInfo}. OTP was not sent.";
            $_SESSION['otp_message_type'] = 'danger';
            error_log("PHPMailer (Mailtrap) Error for {$user_email_to_send_otp} (Username: " . $session_username . "): {$mail->ErrorInfo}");
        }
    }
    // Always redirect back to otp_form.php after attempting to resend, to show messages.
    header("Location: otp_form.php");
    exit();
}

// OTP Verification Logic (when form is submitted with OTP)
$otp_submit_button_name = 'login'; // Assuming 'login' is the name of your verify button
if (isset($_POST[$otp_submit_button_name])) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) { // Check for both
        error_log("OTP Verify Attempt: Critical - user_id or user_role not found in session.");
        // Don't set otp_message here as it might overwrite login messages
        echo "<script type='text/javascript'>alert('Your session is invalid or incomplete. Please login again.'); window.location='login.php';</script>";
        exit();
    }

    $user_otp_input = isset($_POST['otp_input']) ? trim($_POST['otp_input']) : '';

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expire'])) {
        $_SESSION['otp_message'] = 'OTP session is invalid. Please request a new OTP.';
        $_SESSION['otp_message_type'] = 'danger';
        header("Location: otp_form.php");
        exit();
    } elseif (time() > $_SESSION['otp_expire']) {
        $_SESSION['otp_message'] = 'OTP has expired. Please request a new one.';
        $_SESSION['otp_message_type'] = 'danger';
        unset($_SESSION['otp'], $_SESSION['otp_expire']);
        header("Location: otp_form.php");
        exit();
    } elseif ($user_otp_input == $_SESSION['otp']) {
        $_SESSION['otp_verified'] = true; // Mark OTP as verified
        unset($_SESSION['otp'], $_SESSION['otp_expire']); // Clear OTP data
        // session_write_close(); // Not strictly needed before header if no more session writes

        $role_id_after_otp = $_SESSION['user_role'];
        $redirect_url = 'login.php'; // Default redirect if role is unknown

        // --- MODIFIED REDIRECT LOGIC ---
        if ($role_id_after_otp == 1 || $role_id_after_otp == 4) { 
            $redirect_url = 'admin/index.php';
        } elseif ($role_id_after_otp == 2) {
            $redirect_url = 'admin2/dashboard.php';
        } elseif ($role_id_after_otp == 3) {
            $redirect_url = 'voter_home.php';
        } elseif ($role_id_after_otp == 5) { // ADDED CHECK FOR ROLE 5
            $redirect_url = 'faculty_home.php'; 
        } else {
            error_log("OTP Verified: Unknown role_id '{$role_id_after_otp}' for user_id '{$_SESSION['user_id']}'.");
            $_SESSION['login_message'] = 'Login successful, but your user role is unrecognized. Please contact support.';
            // $redirect_url remains 'login.php' to show this message
        }
        
        header("Location: " . $redirect_url);
        exit();
        // --- END MODIFIED REDIRECT LOGIC ---

    } else { // Incorrect OTP
        $_SESSION['otp_message'] = 'Incorrect OTP entered. Please try again.';
        $_SESSION['otp_message_type'] = 'danger';
        header("Location: otp_form.php");
        exit();
    }
}

// If we reach here, it means we are displaying the OTP form (not processing a submission or resend)
if (file_exists('head.php')) {
    include('head.php'); // Now include head.php as no more redirects will happen before HTML output
}
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
    /* ... (Your existing CSS for otp_form.php - Keep it as is) ... */
    body { background-color: #f7f7f7; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; font-family: Arial, sans-serif; }
    .otp-container-custom { 
        margin-top: 30px;
        margin-bottom: 50px;
         max-width: 530px; padding-left: 15px; padding-right: 15px; margin-left: auto; margin-right: auto; }
    .otp-form-wrapper { background: #e8e8e8; padding: 30px 15px; border-radius: 8px; text-align: center; }
    .otp-box { background: #fff; border-radius: 6px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); padding: 30px; margin-top: 20px; }
    .otp-box h4 { text-align: center; margin-bottom: 15px; font-size: 22px; font-weight: 600;}
    .form-group { margin-bottom: 20px; }
    .form-control { height: 45px; border-radius: 4px; }
    .btn-primary-custom { background-color: #337ab7; border-color: #2e6da4; color: white; padding: 10px 15px; font-size: 16px; line-height: 1.5; border-radius: 4px; width: 100%; font-weight: bold; }
    .btn-primary-custom:hover { background-color: #286090; border-color: #204d74; }
    .btn-warning-custom { background-color: #f0ad4e; border-color: #eea236; color: white; }
    .btn-warning-custom:hover { background-color: #ec971f; border-color: #d58512; }
    .alert-custom-otp { margin-top: 10px; margin-bottom: 20px; padding: 10px 15px; border-radius: 4px; }
    .input-group-otp { display: flex; gap: 10px; align-items: center; }
    .input-group-otp .form-control { flex: 1; }
    .text-muted-otp { color: #777; margin-bottom: 25px; font-size: 16px; }
    footer { margin-top: auto; padding: 20px; background-color: #f8f9fa; width: 100%; text-align: center; }
</style>

<body>
<?php
if (file_exists('view_banner.php')) include('view_banner.php');
?>

<div class="otp-container-custom ">
    <div class="otp-form-wrapper">
        <h2 class="text-center">Two-Factor Authentication</h2> 
        <p class="text-center text-muted-otp">Please verify your account by entering the OTP sent to your email (via Mailtrap).</p>

        <div class="otp-box">
            <h4>Verify your Account</h4>
            
            <?php
            if (isset($_SESSION['otp_message'])) {
                $message_type = $_SESSION['otp_message_type'] ?? 'info'; // Default to info if not set
                $alert_class = ($message_type === 'danger') ? 'alert-danger' : 
                               (($message_type === 'success') ? 'alert-success' : 'alert-info');
                echo '<div class="alert ' . htmlspecialchars($alert_class) . ' alert-custom-otp" role="alert">' . htmlspecialchars($_SESSION['otp_message']) . '</div>';
                unset($_SESSION['otp_message'], $_SESSION['otp_message_type']);
            }
            ?>

            <form role="form" method="post" class="index-form" action="otp_form.php">
                <div class="form-group">
                    <div class="input-group-otp"> 
                        <input type="text" id="otp_input_id" name="otp_input" class="form-control" placeholder="Enter OTP" required pattern="\d{6}" title="OTP must be 6 digits" autocomplete="one-time-code">
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
                    <button type="submit" class="btn btn-primary-custom" name="<?php echo $otp_submit_button_name; ?>">Verify</button> 
                </div>
            </form>
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
                if (timerElement) timerElement.innerText = `OTP expires in: ${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                countdown--;
            }
        }
        updateTimerDisplay(); // Initial call to display immediately
        intervalId = setInterval(updateTimerDisplay, 1000);
    }

    <?php
    if (isset($_SESSION['otp']) && isset($_SESSION['otp_expire']) && time() < $_SESSION['otp_expire']) {
        $remainingTime = $_SESSION['otp_expire'] - time();
        echo "if (typeof startTimer === 'function') startTimer(" . $remainingTime . ");\n";
    } elseif (isset($_SESSION['otp_message']) && strpos($_SESSION['otp_message'], 'expired') !== false) {
         // If message already indicates expiry, don't start timer
         echo 'if (timerElement) timerElement.innerText = "OTP expired. Please request a new one.";';
    } else {
        echo 'if (timerElement) timerElement.innerText = "Click button to get OTP.";';
    }
    ?>

    if (getCodeBtn) {
        getCodeBtn.addEventListener("click", () => {
            getCodeBtn.disabled = true; 
            getCodeBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...'; // Spinner
            window.location.href = "?resend=true";
        });
        //#e8e8e8
    }
</script>

<?php
if (file_exists('footer.php')) include('footer.php');
if (file_exists('script.php')) include('script.php'); // For Bootstrap JS etc.
?>
</body>
</html>