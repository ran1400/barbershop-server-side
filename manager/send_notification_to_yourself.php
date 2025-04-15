<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require "../user/utils/send_notification_to_manager.php";

$title = $_POST["title"];
$body = $_POST["body"];

$response = sendFCM('',$title,$body);

if ($response == false)
    echo "cmd failed";
else
    echo "V";
   


?>