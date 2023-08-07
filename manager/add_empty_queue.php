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
    $time = $_POST["time"]."00";
    $cmd = "SELECT Time FROM (SELECT Time FROM ReservedQueue UNION SELECT Time FROM EmptyQueue) as allQueues where allQueues.Time = '$time'";
    $conn->begin_transaction();
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die("cmd failed");
    $res = mysqli_fetch_assoc($query);
    if ($res['Time'] )
        die("X");
    $cmd = "INSERT INTO EmptyQueue VALUE ('$time')";
    $query = mysqli_query($conn,$cmd);
    if ($query && $conn->affected_rows == 1)
    {
        $conn->commit(); 
        die("V");
    }
    else
      echo("cmd failed");
}

?>