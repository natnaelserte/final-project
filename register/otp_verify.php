<?php
session_start();
require_once 'dbcon.php';
date_default_timezone_set('Africa/Nairobi');

$otpScript = '';

// Generate a 6-digit numeric OTP
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Simulate sending OTP (popup)
function sendMockSMS($phoneNumber, $otp) {
    return "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const box = document.createElement('div');
            box.style.position = 'fixed';
            box.style.top = '25%';
            box.style.left = '50%';
            box.style.transform = 'translate(-50%, -50%)';
            box.style.background = '#fff';
            box.style.border = '1px solid #333';
            box.style.padding = '20px';
            box.style.zIndex = 9999;
            box.style.borderRadius = '12px';
            box.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
            box.id = 'otpBox';
            box.innerHTML = `
                OTP sent to {$phoneNumber}: <strong>{$otp}</strong><br><br>
                <button id='copyBtn' style='padding: 5px 10px; border: none; background: #4e54c8; color: white; border-radius: 6px; cursor: pointer;'>Copy OTP</button>
            `;
            document.body.appendChild(box);

            document.getElementById('copyBtn').addEventListener('click', function() {
                navigator.clipboard.writeText('{$otp}').then(function() {
                    document.getElementById('otpBox').remove();
                }).catch(function(err) {
                    alert('Failed to copy OTP: ' + err);
                });
            });
        });
    </script>";
}

// Handle OTP Request
function handleOTP($pdo) {
    if (isset($_POST['get_code']) || isset($_POST['resend_code'])) {
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $maskedPhone = '******' . substr($phone, -4);
        $otp = generateOTP();
        $expiration = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $stmt = $pdo->prepare("SELECT id FROM otp_table WHERE phone_number = ?");
        $stmt->execute([$maskedPhone]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE otp_table SET otp = ?, expiration_time = ?, is_verified = 0 WHERE phone_number = ?");
            $stmt->execute([$otp, $expiration, $maskedPhone]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO otp_table (phone_number, otp, expiration_time, is_verified) VALUES (?, ?, ?, 0)");
            $stmt->execute([$maskedPhone, $otp, $expiration]);
        }

        $_SESSION['phone_number'] = $maskedPhone;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_sent'] = true;
        $_SESSION['otp_expiration'] = time() + 120;

        return sendMockSMS($maskedPhone, $otp);
    }
    return '';
}

// Process OTP form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitted_otp'])) {
    if (!isset($_SESSION['phone_number'], $_SESSION['otp'])) {
        echo "<script>alert('Please request OTP first.'); window.location='otp_verify.php';</script>";
        exit();
    }

    $userOtp = $_POST['submitted_otp'];
    $_SESSION['user_otp'] = $userOtp; // Save user-submitted OTP for register.php
    header("Location: register.php");
    exit();
}

$otpScript = handleOTP($pdo);
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 60px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background: url('download (1).jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .form-container {
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-in-out;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #4e54c8;
            font-weight: 600;
        }

        .form-group label {
            font-weight: 500;
            color: #555;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4e54c8;
            box-shadow: 0 0 8px rgba(78, 84, 200, 0.4);
        }

        .btn-block {
            border-radius: 8px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #4e54c8;
            border: none;
        }

        .btn-primary:hover {
            background: #3b40b1;
        }

        .btn-success {
            background: rgb(7, 8, 54);
            border: none;
        }

        .btn-success:hover {
            background: rgb(84, 17, 243);
        }

        p.text-center {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        #timer {
            font-weight: bold;
            color: #e74c3c;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>OTP Verification</h2>
    <form method="POST">
        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" class="form-control"
                   pattern="\+251[9][0-9]{8}" required
                   value="<?= $_SESSION['phone_number'] ?? '+251' ?>"
                   <?= (isset($_SESSION['otp_sent']) && $_SESSION['otp_expiration'] > time()) ? 'readonly' : '' ?>>
        </div>

        <button type="submit" name="<?= (isset($_SESSION['otp_sent']) && $_SESSION['otp_expiration'] > time()) ? 'resend_code' : 'get_code' ?>"
                class="btn btn-primary btn-block"
                <?= (isset($_SESSION['otp_expiration']) && $_SESSION['otp_expiration'] > time()) ? 'disabled' : '' ?>>
            <?= (isset($_SESSION['otp_sent']) && $_SESSION['otp_expiration'] > time()) ? 'Resend Code' : 'Get Code' ?>
        </button>

        <?php if (isset($_SESSION['otp_sent'])): ?>
            <p class="text-center">OTP is valid for <span id="timer">2:00</span></p>
        <?php endif; ?>
    </form>

    <?php if (isset($_SESSION['otp_sent'])): ?>
        <form method="POST" class="mt-3">
            <div class="form-group">
                <label>Enter OTP</label>
                <input type="text" name="submitted_otp" class="form-control" placeholder="Enter OTP" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">Verify & Register</button>
        </form>
    <?php endif; ?>
</div>

<?= $otpScript ?>

<?php if (isset($_SESSION['otp_expiration'])): ?>
    <script>
        let timeLeft = <?= $_SESSION['otp_expiration'] - time() ?>;
        const timer = document.getElementById('timer');

        function updateTimer() {
            if (timeLeft <= 0) {
                timer.textContent = "Expired";
                return;
            }
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timer.textContent = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        updateTimer();
    </script>
<?php endif; ?>

</body>
</html>
