<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
else
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $cmd = "SELECT ReservedQueue.Time,User.Name,User.Phone,User.Mail FROM ReservedQueue JOIN User 
    ON User.Mail = ReservedQueue.UserMail AND Time >= '$timeStamp' ORDER BY ReservedQueue.Time ASC";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
       while ($row = mysqli_fetch_array($res))
          echo $row{'Time'}."<br>".$row{'Name'}."<br>".$row{'Phone'}."<br>".$row{'Mail'}."<br>";
    }
    else
       echo("cmd failed");
}

?>