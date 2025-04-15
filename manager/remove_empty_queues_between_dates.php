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
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem'));
    $timeStamp =  $date->format('Y-m-d H:i:00');
    $firstDate =  $_POST["firstDate"]; 
    $secondDate =  $_POST["secondDate"]; 
    $cmd = "DELETE FROM EmptyQueue WHERE Time > '$timeStamp' AND Time BETWEEN '$firstDate' AND '$secondDate'";
    $query = mysqli_query($conn,$cmd);
    if ($query)
       echo $conn->affected_rows; //how much rows deleted
    else
       echo("cmd failed");
}


?>