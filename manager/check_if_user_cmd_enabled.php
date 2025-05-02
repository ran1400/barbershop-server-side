<?php
   
require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
  
require_once "../checkIfUserCmdLock.php";

$userCmdLock = checkIfUserCmdLock($conn);
if ($userCmdLock === null)
    die(json_encode(["error" => "cmd failed : checkIfUserCmdLock"]));
echo json_encode(["userCmdLock" => $userCmdLock]);
?>