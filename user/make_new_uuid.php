<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
require "utils/generate_uuid.php";
$newUuid = generateUuid();
$cmd = "UPDATE User SET SecretKey = '$newUuid' WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd );
if ($query && $conn->affected_rows == 1)
    echo("V");
else
    echo "cmd failed";
?>