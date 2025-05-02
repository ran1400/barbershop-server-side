<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"])); 

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
   
require_once "../getMsgFromManager.php";

$msg = getMsgFromManager($conn);
if ($msg === null)
    die(json_encode(["error" => "cmd failed : get massage from manager"])); 
echo json_encode(["msg" => $msg])

?>