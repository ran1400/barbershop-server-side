<?php

$name = $_POST["name"];

if ( strpos($name,"<") || strpos($name,"'") || mb_strlen($name) > 25)
    die(json_encode(["error" => "cmd failed : input error"]));
   
require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));  
   
$userMail = $_POST["userMail"];

$userSecretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

$permissionCheck = permissionCheck($conn,$userMail,$userSecretKey);
if ($permissionCheck  === null)
    die(json_encode(["error" => "cmd failed - permission check"]));
else if ($permissionCheck  === false)
    die(json_encode(["error" => "permission problem"]));
    
require_once "utils/check_if_blocked_user.php";

if (checkIfBlockedUser($conn,$userMail))
   die(json_encode(["error" => "permission problem"]));
   
$query = "UPDATE User SET Name = ? WHERE Mail = ?";
$usersUpdate = runExecQuery($conn,$query,[$name,$userMail]);
if ($usersUpdate == 1)
    echo json_encode(["userName" => $name]);
else
    die(json_encode(["error" => "cmd failed : " . $query]));
    
?>