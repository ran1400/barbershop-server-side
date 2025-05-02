<?php

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
    die(json_encode(["error" => "permission problem"])); 
    
require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"])); 

$msg = $_POST["msg"];
$query = "UPDATE Setting SET MsgFromManager = ?";
if (runExecQuery($conn,$query,[$msg]) != 1)
    die(json_encode(["error" => "cmd failed : " . $query])); 
echo json_encode(["msg" => $msg]); 
?>
