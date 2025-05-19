
<?php
include('admin/dbcon.php');

try {
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM (
            SELECT id 
            FROM announcements 
            WHERE read_status = 0 
            ORDER BY created_at DESC 
            LIMIT 3
        ) AS recent_unread
    ");
    $count_stmt->execute();
    $unread_count = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}
?>

<nav class="navbar navbar-default custom-navbar" style="background-color: #0d1b2a; border: none; margin-bottom: 0;">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarNav">
        <span class="icon-bar" style="background-color: white;"></span>
        <span class="icon-bar" style="background-color: white;"></span>
        <span class="icon-bar" style="background-color: white;"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="nav navbar-nav navbar-center nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="candidate_path.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'candidate_path.php') echo 'active'; ?>">Candidates</a></li>
        <li><a href="about.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>">About</a></li>
        <li><a href="live.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'live.php') echo 'active'; ?>">Live</a></li>
        <li><a href="register/index.php" id="register-link">Register</a></li>
        <li><a href="login.php" id="login-link">Login</a></li>
        <li><a href="faq.php">FAQ</a></li>
        <div class="announcement-link">
      

                <a href="announcement.php">
                    📢  
                    <?php if ($unread_count > 0): ?>
                        <span class="badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
          
            </div>
     


 
      </ul>
    </div>
  </div>
</nav>
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
body {
            margin: 0;
            padding: 0;
        }
        .announcement-link {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 1000;
            font-size: 20px;
        }
        .announcement-link a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .announcement-link .badge {
            background-color: red;
            color: white;
            padding: 2px 6px;
            font-size: 25px;
            border-radius: 10px;
            vertical-align: middle;
            margin-left: 4px;
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
