<?php
include('dbcon.php');
$voters_id=$_GET['user_id'];
mysql_query("delete from voters where user_id='$voters_id'") or die($conn->error);
header('location:voters.php');
?>