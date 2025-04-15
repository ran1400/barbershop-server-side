<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require "utils/send_notification_to_user.php";

$title = $_POST["title"];
$body = $_POST["body"];

$response = sendFCMHelper('managerMsgs',$title,$body);

if ($response == false)
    echo "cmd failed";
else
    echo "V";

?>