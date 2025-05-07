<?php

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
    die(json_encode(["error" => "permission problem"])); 
   
require "../user/utils/send_notification_to_manager.php";

$title = $_POST["title"];
$body = $_POST["body"];

if (sendFCM('testMsg',$title,$body) === false)
    die(json_encode(["error" => "cmd failed : send notification"])); 
echo json_encode(["error" => "no"]); 
    

?>