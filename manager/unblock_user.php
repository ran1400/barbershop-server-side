<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed");

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem");
   
$userMail = $_POST["mail"]; 

$cmd = "UPDATE User SET block = 0 WHERE User.Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");

require "utils/send_user_unblock_notifications.php";
if ($sendUserUnblockNotifications)
    mail($userMail,"חסימת החשבון שלך במספרה בוטלה","");
echo 'V';

?>