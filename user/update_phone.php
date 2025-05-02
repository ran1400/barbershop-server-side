<?php

$phone = $_POST["phone"];
if (strlen($phone) > 15 || !is_numeric($phone))
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

$blockedUser = checkIfBlockedUser($conn,$userMail);
if ($blockedUser === null)
    die(json_encode(["error" => "cmd failed - check if blocked user"]));
if ($blockedUser)
   die(json_encode(["error" => "permission problem"]));

$query = "UPDATE User SET Phone = ? WHERE Mail = ?";
$usersUpdate = runExecQuery($conn,$query,[$phone,$userMail]);
if ($usersUpdate == 1)
    echo json_encode(["userPhone" => $phone]);
else
    die(json_encode(["error" => "cmd failed : " . $query]));
    
?>