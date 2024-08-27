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
    $cmd = "DELETE FROM `EmptyQueue` WHERE `Time` < '$timeStamp'";
    mysqli_query($conn,$cmd);
    $cmd = "SELECT Time FROM EmptyQueue WHERE Time >= '$timeStamp' ";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
       while ($row = mysqli_fetch_array($res))
          echo $row['Time']."<br>";
    }
    else
     echo("cmd failed");
}

?>