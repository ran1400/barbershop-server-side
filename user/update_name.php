<?php

$name = $_POST["name"];

if ( strpos($name,"<") || strpos($name,"'") || mb_strlen($name) > 25)
    die("cmd failed");
   
require "utils/connect.php";
if ($conn->connect_error)
   die("connection failed"); 
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";
require "utils/check_if_blocked_user.php";

if ($permission == false || $blockedUser)
   die("permission problem"); 
   
$cmd = "UPDATE User SET Name = '$name' WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if ($query &&  $conn->affected_rows == 1)
    echo("V");
else
    echo("cmd failed");
    
?>