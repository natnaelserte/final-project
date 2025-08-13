<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0; background-color: #90D1CA;">
    <div class="navbar-header">
        <a class="navbar-brand" href="index_banner.php" style="color:#333333;">
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
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: #333333;">
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

<style>
    /* Custom styles with #90D1CA as primary color */
    :root {
        --primary-color: #90D1CA;
        --primary-dark: #75B5AE;  /* Darker shade for hover/active states */
        --primary-light: #A8DCD6; /* Lighter shade for backgrounds */
        --primary-very-light: #E5F4F2; /* Very light shade for subtle backgrounds */
        --text-on-primary: #333333; /* Dark text on primary color */
    }

    /* Navbar styling */
    .navbar-default {
        background-color: var(--primary-color);
        border-color: var(--primary-dark);
    }

    .navbar-default .navbar-brand,
    .navbar-default .navbar-nav > li > a,
    .navbar-default .navbar-text {
        color: var(--text-on-primary);
    }

    .navbar-default .navbar-brand:hover,
    .navbar-default .navbar-nav > li > a:hover {
        color: var(--text-on-primary);
        background-color: var(--primary-dark);
    }

    /* Sidebar styling */
    .navbar-default .sidebar {
        background-color: #f8f8f8;
        border-right: 1px solid #e7e7e7;
    }

    .sidebar ul li {
        border-bottom: 1px solid #e7e7e7;
    }

    .sidebar ul li a {
        color: #555;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a:focus,
    .sidebar ul li a.active {
        background-color: var(--primary-very-light);
    }

    .sidebar ul li a.active {
        border-left: 3px solid var(--primary-color);
    }

    /* Dropdown menu styling */
    .dropdown-menu {
        border-color: var(--primary-light);
    }

    .dropdown-menu > li > a:hover,
    .dropdown-menu > li > a:focus {
        background-color: var(--primary-very-light);
        color: var(--text-on-primary);
    }

    /* Navbar toggle button */
    .navbar-default .navbar-toggle {
        border-color: var(--primary-dark);
    }

    .navbar-default .navbar-toggle:hover,
    .navbar-default .navbar-toggle:focus {
        background-color: var(--primary-dark);
    }

    .navbar-default .navbar-toggle .icon-bar {
        background-color: var(--text-on-primary);
    }

    /* Active menu item */
    .nav > li > a:hover,
    .nav > li > a:focus {
        background-color: var(--primary-very-light);
    }

    /* Highlight current page in sidebar */
    #side-menu > li > a.active,
    #side-menu > li > a:hover {
        background-color: var(--primary-very-light);
        color: var(--primary-dark);
    }
</style>

<script>
    $(document).ready(function() {
        // Add active class to current page in sidebar
        var currentPage = window.location.pathname.split('/').pop();
        $('#side-menu li a').each(function() {
            var href = $(this).attr('href');
            if (href === currentPage) {
                $(this).addClass('active');
            }
        });
    });
</script>
