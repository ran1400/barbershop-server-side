<?php

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
    die(json_encode(["error" => "permission problem"])); 
    
require "utils/send_notification_to_user.php";

$title = $_POST["title"];
$body = $_POST["body"];

if (sendFCMHelper('managerMsg','managerMsgs',$title,$body))
    echo json_encode(["error" => "no"]); 
else
    die(json_encode(["error" => "cmd failed"])); 
?>