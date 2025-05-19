<nav class="navbar-expand-lg navheader">
    <a class="navbar-brand" href="#">AMUVOTING</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
        <ul class="navbar-nav">
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
                <a class="nav-link " href="../register/index.php">Register</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'login.php') echo 'active'; ?>" href="../login.php">Login</a>
            </li>
        </ul>
    </div>
</nav>
