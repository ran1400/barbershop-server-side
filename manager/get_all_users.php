<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
   
$timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp = $timeStamp->format('Y-m-d H:i:').'00';

$cmd = "SELECT Name,Phone,Mail,Block,IF(ReservedQueue.UserMail IS NULL,'X',ReservedQueue.Time) AS Queue 
        FROM User LEFT JOIN ReservedQueue ON User.Mail = ReservedQueue.UserMail AND ReservedQueue.Time >= '$timeStamp'";
$res = mysqli_query($conn,$cmd);
if ( ! $res)
    die (json_encode(["error" => "cmd failed : " . $cmd]));
$users = mysqli_fetch_all($res, MYSQLI_ASSOC);
echo json_encode(["users" => $users]);

?>