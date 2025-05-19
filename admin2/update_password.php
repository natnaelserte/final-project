<?php
include('session.php'); // Keep session management
?>

<?php include('head.php'); ?>

<body>
    <div id="wrapper">
        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="container">
                <h2>Change Password</h2>
                <form id="changePasswordForm" method="post">  <!-- Remove action attribute -->
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        <div id="passwordStrength" class="help-block"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        <div id="passwordMatch" class="help-block"></div>
                    </div>
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="otp" name="otp" required>
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="getOtp">Get Code</button>
                            </span>
                        </div>
                        <div id="otpStatus" class="help-block"></div>
                        <div id="otpDisplay" class="help-block"></div> <!-- Added for displaying OTP -->
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
</body>

<?php include('script.php'); ?>
<script>
    $(document).ready(function() {
        var otpSent = false;
        var timerInterval;
        var timeLeft = 120; // 2 minutes

        // Password Strength Checker
        $('#newPassword').keyup(function() {
            var password = $(this).val();
            var strength = 0;

            if (password.length < 8) {
                $('#passwordStrength').text('Password must be at least 8 characters.');
                return;
            } else {
                $('#passwordStrength').text('');
            }

            if (password.match(/[a-z]+/)) {
                strength += 1;
            }
            if (password.match(/[A-Z]+/)) {
                strength += 1;
            }
            if (password.match(/[0-9]+/)) {
                strength += 1;
            }
            if (password.match(/[$@#&!]+/)) {
                strength += 1;
            }

            if (strength < 2) {
                $('#passwordStrength').text('Weak: Include uppercase, numbers, and symbols.');
            } else if (strength == 2) {
                $('#passwordStrength').text('Fair: Consider adding more complexity.');
            } else if (strength == 3) {
                $('#passwordStrength').text('Good');
            } else {
                $('#passwordStrength').text('Strong');
            }
        });

        // Password Match Checker
        $('#confirmPassword').keyup(function() {
            var newPassword = $('#newPassword').val();
            var confirmPassword = $(this).val();

            if (newPassword != confirmPassword) {
                $('#passwordMatch').text('Passwords do not match.');
            } else {
                $('#passwordMatch').text('Passwords match.');
            }
        });

        // OTP Functionality
        $('#getOtp').click(function() {
            if (!otpSent) {
                // Send OTP via AJAX
                $.ajax({
                    url: 'update_password_api.php', // API endpoint
                    type: 'POST',
                    data: {
                        action: 'getOtp'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            otpSent = true;
                            $('#otpStatus').text('OTP sent to phone ' + response.phone + '. Check below.');
                            startTimer();
                            $('#getOtp').prop('disabled', true).text('Resend Code in ' + timeLeft);
                            $('#otpDisplay').text('Simulated OTP: ' + response.otp); // Display the OTP
                        } else {
                            $('#otpStatus').text('Error sending OTP: ' + response.message);
                        }
                    },
                    error: function() {
                        $('#otpStatus').text('Error sending OTP. Please try again.');
                    }
                });
            } else {
                // Resend OTP
                $.ajax({
                    url: 'update_password_api.php', // API endpoint
                    type: 'POST',
                    data: {
                        action: 'resendOtp'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            timeLeft = 120; // Reset timer
                            startTimer();
                            $('#otpStatus').text('New OTP sent to phone ' + response.phone + '. Check below.');
                            $('#otpDisplay').text('Simulated OTP: ' + response.otp); // Display the OTP
                        } else {
                            $('#otpStatus').text('Error resending OTP: ' + response.message);
                        }
                    },
                    error: function() {
                        $('#otpStatus').text('Error resending OTP. Please try again.');
                    }
                });
            }
        });

        function startTimer() {
            timeLeft = 120;
            $('#getOtp').prop('disabled', true);
            timerInterval = setInterval(function() {
                timeLeft--;
                $('#getOtp').text('Resend Code in ' + timeLeft);
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    $('#getOtp').text('Resend Code').prop('disabled', false);
                    otpSent = false;
                }
            }, 1000);
        }

        // Form Validation and Submission
        $('#changePasswordForm').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            var currentPassword = $('#currentPassword').val();
            var newPassword = $('#newPassword').val();
            var confirmPassword = $('#confirmPassword').val();
            var otp = $('#otp').val();

            if (currentPassword == '' || newPassword == '' || confirmPassword == '' || otp == '') {
                alert('Please fill in all fields.');
                return;
            }

            if ($('#passwordMatch').text() != 'Passwords match.') {
                alert('Passwords do not match.');
                return;
            }

            // Send data to the API for password update
            $.ajax({
                url: 'update_password_api.php', // API endpoint
                type: 'POST',
                data: {
                    action: 'updatePassword',
                    currentPassword: currentPassword,
                    newPassword: newPassword,
                    confirmPassword: confirmPassword,
                    otp: otp
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location = 'candidate.php'; // Redirect on success
                    } else {
                        alert(response.message); // Display error message
                    }
                },
                error: function() {
                    alert('Error updating password. Please try again.');
                }
            });
        });
    });
</script>