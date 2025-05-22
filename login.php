<?php
session_start();
include('head.php'); // Your HTML <head> etc.
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<body style="min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">


<style>
    .container-login-custom {
        margin-top: 0;
        margin-bottom: 50px;
        max-width: 530px;
        padding-left: 15px;
        padding-right: 15px;
        margin-left: auto;
        margin-right: auto;
    }
    .login-box {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        padding: 30px;
    }
    .text-muted {
        color: #777;
    }
    footer {
        margin-top: auto;
        padding: 20px;
        background-color: #f8f9fa;
        width: 100%;
    }
    .form-wrapper {
        background: #f7f7f7;
        padding: 30px 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .alert-custom-login {
        margin-top: 10px;
        margin-bottom: 20px;
        padding: 10px 15px;
        border-radius: 4px;
    }
</style>
<?php include('view_banner.php'); ?>

<div class="container-login-custom">
    
    <div class="form-wrapper">
        <h2 class="text-center">Welcome Back!</h2>
        <p class="text-center text-muted" style="margin-bottom: 25px;">Please login to continue</p>

        <div class="login-box">
            <form method="post" action="login_query.php">
                <h4 class="text-center">Login to your account</h4>
                <p class="text-center text-muted" style="font-size: 14px;">
                    Don’t have an account? <a href="register/index.php">Create a new account</a>
                </p>

                <div class="form-group">
                    <label for="username" class="sr-only">Username</label>
                    <input type="text" id="username" class="form-control" name="username" placeholder="Username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" id="password" class="form-control" name="password" placeholder="Password" required>
                    
                    <?php
                    if (isset($_SESSION['login_message']) && is_array($_SESSION['login_message'])) {
                        $msg_type = htmlspecialchars($_SESSION['login_message']['type']);
                        $msg_text = htmlspecialchars($_SESSION['login_message']['text']);
                        echo "<div class='alert alert-{$msg_type} alert-custom-login' role='alert'>{$msg_text}</div>";
                        unset($_SESSION['login_message']);
                    }
                    ?>
                </div>

                <div class="form-group text-right">
                    <a href="#" class="small">Forgot password?</a>
                </div>

                <div class="form-group">
                    <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="text-center" style="font-size: 12px; color: #888;">
    <?php include('footer.php'); ?>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<?php include('script.php'); ?>
</body>
</html>
