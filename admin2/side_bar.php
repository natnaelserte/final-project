<nav class="navbar navbar-static-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle btn btn-default" data-toggle="collapse" data-target=".navbar-collapse">
            <span><li class="fa fa-line"></li></span>
            <span><li class="fa fa-line"></li></span>
            <span><li class="fa fa-line"></li></span>
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
                    <a href="dashboard.php"><i class="fa fa-user fa-fw"></i> Dashboard</a>
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
                    <a href="staff_complaint.php"><i class="fa fa-user fa-fw"></i> Handle Complaint</a>
                </li>
                <li>
                    <a href="post_announcement.php"><i class="fa fa-user fa-fw"></i> Post Announcement</a>
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
    /* Updated Color Scheme with #90D1CA as primary */
    :root {
        --primary-color: #90D1CA;
        --primary-dark: #75B5AE;  /* Darker shade for hover/active states */
        --primary-light: #A8DCD6; /* Lighter shade for backgrounds */
        --primary-very-light: #E5F4F2; /* Very light shade for subtle backgrounds */
        --text-on-primary: #333333; /* Dark text on primary color */
        --text-light: #ffffff;
        --text-dark: #333333;
        --accent-color: #FF9E80; /* Complementary accent if needed */
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
    }

    .navbar {
        background-color: var(--primary-color);
        margin-bottom: 0;
        border: none;
    }

    /* Sidebar styling */
    .navbar-default .sidebar {
        background-color: #f5f5f5;
        border-right: 1px solid #e7e7e7;
    }

    .sidebar-nav {
        padding: 0;
    }

    .hover-menu-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .hover-menu-items li {
        border-bottom: 1px solid #e7e7e7;
    }

    .hover-menu-items li a {
        color: var(--text-dark);
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        transition: all 0.3s ease;
    }

    .hover-menu-items li a:hover {
        background-color: var(--primary-color);
        color: var(--text-dark);
    }

    .hover-menu-items li.active a {
        background-color: var(--primary-color);
        color: var(--text-dark);
    }

    /* Navbar brand and links */
    .navbar-brand {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark) !important;
    }

    .navbar-top-links li a {
        color: var(--text-dark) !important;
        padding: 15px;
        min-height: 50px;
    }

    .navbar-top-links li a:hover {
        background-color: var(--primary-dark);
    }

    /* Toggle button */
    .navbar-toggle {
        margin-top: 8px;
        background-color: transparent;
        border-color: var(--text-dark);
    }

    .navbar-toggle .fa-line {
        background-color: var(--text-dark);
        display: block;
        height: 2px;
        width: 22px;
        margin: 4px 0;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .sidebar {
            position: static;
            width: auto;
            overflow-x: visible;
            padding-top: 0;
        }

        .sidebar-nav {
            width: 100%;
        }

        .hover-menu-items {
            position: static;
            display: none;
            background-color: transparent;
            box-shadow: none;
        }

        .navbar-collapse:hover .hover-menu-items {
            display: block;
        }

        #page-wrapper {
            margin-left: 0;
        }
    }

    /* Active page indicator */
    .hover-menu-items li a.active {
        background-color: var(--primary-color);
        color: var(--text-dark);
        border-left: 4px solid var(--primary-dark);
    }

    /* Add icons styling */
    .hover-menu-items li a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
</style>

<script>
    // Add active class to current page link
    $(document).ready(function() {
        var currentPage = window.location.pathname.split('/').pop();
        $('.hover-menu-items li a').each(function() {
            var href = $(this).attr('href');
            if (href === currentPage) {
                $(this).addClass('active');
                $(this).parent().addClass('active');
            }
        });
    });
</script>
