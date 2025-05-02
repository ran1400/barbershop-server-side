<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
else
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem'));
    $timeStamp =  $date->format('Y-m-d H:i:00');
    $firstDate =  $_POST["firstDate"]; 
    $secondDate =  $_POST["secondDate"]; 
    $query = "DELETE FROM EmptyQueue WHERE Time > '$timeStamp' AND Time BETWEEN ? AND ?";
    $queueDeleted = runExecQuery($conn,$query,[$firstDate,$secondDate]);
    if ($queueDeleted === null)
       die(json_encode(["error" => "cmd failed : " . $query]));
    else
       echo json_encode(["queuesDeleted" => $queueDeleted]);
}


?>