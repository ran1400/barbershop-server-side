<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"])); 
    
    
$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
    die(json_encode(["error" => "permission problem"])); 
   
$seconds =  $_POST["seconds"];
$query = "UPDATE Setting SET SecondsToSendQueueNotification = ?";
if (runExecQuery($conn,$query,[$seconds]) != 1)
    die(json_encode(["error" => "cmd failed : " . $query])); 
echo json_encode(["seconds" => $seconds])
?>