<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0; background-color:green;">
    <div class="navbar-header">
        <a class="navbar-brand" href="" style="color:white; padding-left:25px;">
            <i class="fa fa-home fa-large"></i> HOME | OVS - Online Voting System
        </a>
    </div>

    <ul class="nav navbar-top-links navbar-right">
        <?php
        require 'admin/dbcon.php';

        try {
            // Use a parameterized query to fetch voter details securely
            $query = $pdo->prepare("SELECT * FROM voters WHERE voters_id = ?");
            $query->execute([$session_id]);

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $user_username = htmlspecialchars($row['firstname'] . " " . $row['lastname']);
        ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: white">
                        <i>Welcome: <?php echo $user_username; ?></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="logout.php" style="color: white; padding-right: 30px;">
                        <i class="fa fa-sign-out" style="color: white"></i> Logout
                    </a>
                </li>
        <?php
            }
        } catch (PDOException $e) {
            echo "<li><i>Error fetching user details: " . htmlspecialchars($e->getMessage()) . "</i></li>";
        }
        ?>
    </ul>
</nav>