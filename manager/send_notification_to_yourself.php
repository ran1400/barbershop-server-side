<?php

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
    die(json_encode(["error" => "permission problem"])); 
   
require "../user/utils/send_notification_to_manager.php";

$title = $_POST["title"];
$body = $_POST["body"];

if (sendFCM('',$title,$body) === false)
    die(json_encode(["error" => "cmd failed"])); 
echo json_encode(["error" => "no"]); 
    

?>