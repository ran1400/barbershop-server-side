<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 

$timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';

$conn->begin_transaction();
$cmd = "INSERT INTO PastReservedQueue SELECT * FROM ReservedQueue WHERE ReservedQueue.Time < '$timeStamp'";
$query = mysqli_query($conn,$cmd); 
if (!$query)
    die(json_encode(["error" => "cmd failed : " . $cmd])); 
$cmd = "DELETE FROM ReservedQueue WHERE Time < '$timeStamp'";
$query = mysqli_query($conn,$cmd);
if (!$query)
    die(json_encode(["error" => "cmd failed : " . $cmd])); 
$conn->commit();
     
$cmd = "CREATE TEMPORARY TABLE AllUsers 
                SELECT Mail, Name FROM User 
                UNION 
                SELECT Mail, Name FROM DeletedUser";

if (!$conn->query($cmd)) 
    die(json_encode(["error" => "cmd failed : " . $cmd]));
    
$startTime = $_POST["startTime"]."000000"; //hhmmss
$endTime = $_POST["endTime"]."235900"; //hhmmss
$cmd = "SELECT Time,Name FROM PastReservedQueue JOIN AllUsers
        ON Mail = PastReservedQueue.UserMail AND
        Time >= ? AND Time <= ? ORDER BY Time ASC";
$pastQueues = runSelectMultipleRowsQuery($conn,$cmd,[$startTime,$endTime]);
if (! $pastQueues)
    die(json_encode(["error" => "cmd failed : " . $cmd]));
echo json_encode(["queues" => $pastQueues]);


?>