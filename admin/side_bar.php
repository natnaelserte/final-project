<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0; background-color: rgba(30, 110, 157);;">
    <div class="navbar-header">
        <a class="navbar-brand" href="index_banner.php" style="color:white;">
            <i class="fa fa-home fa-large"></i> HOME | Admin Portal
        </a>
    </div>

    <ul class="nav navbar-top-links navbar-right">
        <?php
        require 'dbcon.php';
//$bool
        try {
            // Use a parameterized query to fetch user details securely
            $query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $query->execute([$session_id]);

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $user_username = htmlspecialchars($row['firstname'] . " " . $row['lastname']);
        ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: white">
                        <i>Welcome: <?php echo $user_username; ?></i>
                    </a>
                </li>
        <?php
            }
        } catch (PDOException $e) {
            echo "<li><i>Error fetching user details: " . htmlspecialchars($e->getMessage()) . "</i></li>";
        }
        ?>
    </ul>

    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav" id="side-menu">
                <li>
                    <a href="#"><i class="fa fa-menu fa-fw"></i> Menu</a>
                </li>
                <li>
                    <a href="index.php"><i class="fa fa-user fa-fw"></i> dashboard</a>
                </li>
                <li>
                    <a href="voters.php"><i class="fa fa-user fa-fw"></i> Voters</a>
                </li>
                <li>
                    <a href="user.php"><i class="fa fa-users"></i> View User</a>
                </li>
                <li>
                    <a href="login_times.php"><i class="fa fa-users"></i> User Login Time</a>
                </li>
                <li>
                     <a href="update_password.php"><i class="fa fa-key"></i> Change Password</a>
                    </li>
                <li>
                    <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>