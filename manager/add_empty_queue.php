<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 

$time = $_POST["time"];
$query = "SELECT Time FROM (SELECT Time FROM ReservedQueue UNION SELECT Time FROM EmptyQueue) as allQueues where allQueues.Time = ?";
$conn->begin_transaction();
$queueExist = runSelectQuery($conn,$query,[$time]);
if ($queueExist === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
if ($queueExist !== false)
    die(json_encode(["error" => "queue exist"]));
$query = "INSERT INTO EmptyQueue VALUE (?)";
$queuesInserted = runExecQuery($conn,$query,[$time]);
if ($queuesInserted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$conn->commit();
echo json_encode(["error" => "no"]);

?>