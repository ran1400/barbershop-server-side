<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$date =  $_POST["date"];
$cmd = "DELETE FROM EmptyQueue WHERE Time = '$date'";
$query = mysqli_query($conn,$cmd);
if (!$query)
    die("cmd failed");
if ($conn->affected_rows == 1)
    die("V");
else
    die("not found");
?>