<nav class="navbar  navbar-static-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle btn btn-default" data-toggle="collapse" data-target=".navbar-collapse">
            <span ><li class="fa fa-line "></li></span>
            <span ><li class="fa fa-line "></li></span>
            <span ><li class="fa fa-line "></li></span>
        </button>
        <a class="navbar-brand" href="" style="color:white;">
            <i class="fa fa-home"></i>Staff Portal
        </a>
    </div>

    <ul class="nav navbar-top-links navbar-right">
        <?php
        require 'dbcon.php';

        try {
            // Use a parameterized query to fetch user details securely
            $query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $query->execute([$username]);

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: white;">
                        <i>Welcome: <?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></i>
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
        <div class="sidebar-nav navbar-collapse collapse">
                    <ul class="nav hover-menu-items">
                    <li>
                        <a href="#"><i class="fa fa-menu fa-fw"></i>Menu </a>
                        </li>
                        <li>
                            <a href="candidate.php"><i class="fa fa-user fa-fw"></i> View Candidates</a>
                        </li>
                        <li>
                            <a href="add_position.php"><i class="fa fa-plus fa-fw"></i> Add Position</a>
                        </li>
                        <li>
                            <a href="voters.php"><i class="fa fa-user fa-fw"></i> View Voters</a>
                        </li>
                        <li>
                            <a href="current_students.php"><i class="fa fa-user fa-fw"></i> Voters_Id</a>
                        </li>
                        <li>
                            <a href="report.php"><i class="fa fa-download fa-fw"></i> Voting Report</a>
                        </li>
                        <li>
                            <a href="staff_complaint.php"><i class="fa fa-user fa-fw"></i> handle complaint</a>
                        </li>
                        <li>
                            <a href="post_announcement.php"><i class="fa fa-user fa-fw"></i> post announcement</a>
                        </li>
                         <li>
                            <a href="update_password.php"><i class="fa fa-key fa-fw"></i> Change Password</a>
                        </li>
                        <li>
                            <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                        </li>
                 </ul>
          </div>
    </div>
</nav>

<style>
    .navbar {
        background-color: rgba(30, 110, 157);
        margin-bottom: 0;
    }

    /* Gray Sidebar */
    .navbar-default .sidebar {
        background-color: #ddd; /* Light gray */
    }
    /* Styles for small devices (adjust breakpoint as needed) */
    @media (max-width: 768px) {
        .sidebar {
            position: static; /* Revert to static positioning */
            width: auto; /* Take up full width */
            overflow-x: visible; /* Allow horizontal scrolling if needed */
            padding-top: 0; /* Remove padding */
        }

        .sidebar-nav {
            width: 100%; /* Take up full width */
        }

        .hover-menu-items {
            position: static; /* Revert to static positioning */
            display: none; /* Hide by default */
            background-color: transparent;
            box-shadow: none;
        }

         .navbar-collapse:hover .hover-menu-items {
            display: block; /* Show on hover */
        }

        #page-wrapper {
            margin-left: 0; /* Remove left margin */
        }

        .hover-menu-items li a:hover {
            background-color: green;
       }
    }
    .hover-menu-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .hover-menu-items li a {
        color: white;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        color: #000000; 
    }
    

    .hover-menu-items li a:hover {
        background-color: green;
    }
</style>
