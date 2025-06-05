
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

<nav class="navbar1 custom-navbar">
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
        <li><a href="index.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>">Home</a></li>
        <li><a href="candidate_path.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'candidate_path.php') echo 'active'; ?>">Candidates</a></li>
        <li><a href="about.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>">About</a></li>
        <li><a href="live.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'live.php') echo 'active'; ?>">Live</a></li>
        <li><a href="register/index.php" id="register-link">Register</a></li>
        <li><a href="login.php" id="login-link">Login</a></li>
        <li><a href="faq.php" class="<?php if (basename($_SERVER['PHP_SELF']) == 'faq.php') echo 'active'; ?>">FAQ</a></li>
        <li class="announcement-link">
          <a href="announcement.php">
            📢
            <?php if ($unread_count > 0): ?>
              <span class="badge"><?= $unread_count ?></span>
            <?php endif; ?>
          </a>
        </li>
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
