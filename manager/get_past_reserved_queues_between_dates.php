<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';

$conn->begin_transaction();
$cmd = "INSERT INTO PastReservedQueue SELECT * FROM ReservedQueue WHERE ReservedQueue.Time < '$timeStamp'";
$query = mysqli_query($conn,$cmd); 
if (!$query)
    die("cmd failed"); 
$cmd = "DELETE FROM ReservedQueue WHERE Time < '$timeStamp'";
$query = mysqli_query($conn,$cmd);
if (!$query)
    die("cmd failed");
$conn->commit();
     

$startTime = $_POST["startTime"]."000000"; //hhmmss
$endTime = $_POST["endTime"]."235900"; //hhmmss
$cmd = "CREATE TEMPORARY TABLE AllUsers SELECT Mail,Name
        FROM User UNION SELECT Mail,Name from DeletedUser;
        SELECT Time,Name FROM PastReservedQueue JOIN AllUsers
        ON Mail = PastReservedQueue.UserMail AND
        Time >= '$startTime' AND Time <= '$endTime' ORDER BY Time ASC";
if (! $conn-> multi_query($cmd))
    die("cmd failed");
$conn -> next_result();
$query = $conn->store_result();
if (! $query)
    die("cmd failed");
while ($row = mysqli_fetch_array($query))
    echo $row{'Time'}."<br>".$row{'Name'}."<br>";

?>