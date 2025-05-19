<div>
<nav class="navbar navbar-default custom-navbar" style="background-color: #0d1b2a; border: none; margin-bottom: 0; margin-top: 0;">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarNav">
        <span class="icon-bar" style="background-color: white;"></span>
        <span class="icon-bar" style="background-color: white;"></span>
        <span class="icon-bar" style="background-color: white;"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="nav navbar-nav navbar-center nav-links">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'candidate_path.php') echo 'active'; ?>" href="../candidate_path.php">Candidates</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>" href="../about.php">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'live.php') echo 'active'; ?>" href="../live.php">Live</a>
            </li>
            <li class="nav-item">
                <a  id="register-link" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">Register</a>
            </li>
            <li class="nav-item">
                <a id="login-link" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'login.php') echo 'active'; ?>" href="../login.php">Login</a>
            </li>
        </ul>
    </div>
</nav>
</div>
<script>
  window.onload = function() {
    // Get the current page URL
    var currentPage = window.location.pathname;

    // Select the Register and Login links
    var registerLink = document.getElementById('register-link');
    var loginLink = document.getElementById('login-link');

    // If on the register page, hide Register link and show Login link
    if (currentPage.includes('register')) {
      registerLink.style.display = 'none';
      loginLink.textContent = 'Login';  // Change the text if necessary
    }
    // If on the login page, hide Login link and show Register link
    else if (currentPage.includes('login')) {
      loginLink.style.display = 'none';
      registerLink.textContent = 'Register';  // Change the text if necessary
    }
  }
</script>


<style>
   
  html, body {
    margin: 0;
    padding: 0;
  }


  .custom-navbar {
    height: 130px;
    display: flex;
    align-items: center;
  }

  .nav-links {
    margin: 0 auto;
    display: table;
    float: none;
  }

  .nav-links li a {
    color: white !important;
    font-size: 22px;
    color: white;
    
    font-weight: 500;
    padding: 12px 18px;
    position: relative;
    transition: all 0.4s ease;
  }

  .nav-links li a::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: 8px;
    transform: translateX(-50%);
    width: 0%;
    height: 2px;
    background: #00bcd4;
    transition: width 0.4s ease;
  }

  .nav-links li a:hover {
    color: #00bcd4 !important;
  }

  .nav-links li a:hover::after {
    width: 80%;
  }

  .navbar-brand {
    padding: 20px 15px;
  }

  /* Optional: active page underline */
  .nav-links li a.active {
    color: #00bcd4 !important;
    font-weight: bold;
  }

  .nav-links li a.active::after {
    width: 80%;
  }
</style>
