<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"]));
   

$sendNotifications =  $_POST["sendNotifications"];

if ($sendNotifications)
    $query = "UPDATE Setting SET SendUserBlockNotification = 1";
else
    $query = "UPDATE Setting SET SendUserBlockNotification = 0";
$res = mysqli_query($conn,$query);
if ( ! $res)
    die (json_encode(["error" => "cmd failed : " . $query]));
echo json_encode(["sendUserBlockNotification" => $sendNotifications]);
   
?>