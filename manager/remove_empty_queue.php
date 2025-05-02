<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 


$date =  $_POST["date"];
$query = "DELETE FROM EmptyQueue WHERE Time = ?";
$queuesDeleted = runExecQuery($conn,$query,[$date]);
if ($queuesDeleted === 0)
   die(json_encode(["error" => "not found"])); 
else // ($queuesDeleted == null) 
    die(json_encode(["error" => "cmd failed : " . $query]));
echo json_encode(["error" => "no"]);
?>