<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));   
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

$permissionCheck = permissionCheck($conn,$userMail,$secretKey);

if ($permissionCheck === null)
    die(json_encode(["error" => "cmd failed : permissin check"]));
else if ($permissionCheck === false)
   die(json_encode(["error" => "permission problem"]));  
   
require "utils/generate_uuid.php";
$newUuid = generateUuid();
$query = "UPDATE User SET SecretKey = ? WHERE Mail = ?";
$res = runExecQuery($conn,$query,[$newUuid,$userMail]);
if ($res == 1)
     die(json_encode(["error" => "no"]));
else
    die(json_encode(["error" => "cmd failed : " , $query]));
?>