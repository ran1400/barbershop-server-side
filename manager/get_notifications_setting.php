<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require '../user/utils/seconds_amount_to_send_notification.php';

require "utils/send_user_queue_notifications.php";
require "utils/send_user_block_notifications.php";
require "utils/send_user_unblock_notifications.php";
require "utils/send_user_remove_notifications.php";
$response = $secondsAmountToSendNotification . ">";
if ($sendUserQueueNotifications)
    $response = $response . "t" . ">";
else
     $response = $response . "f" . ">";

if ($sendUserBlockNotifications)
    $response = $response . "t" . ">";
else
     $response = $response . "f" . ">";

if ($sendUserUnblockNotifications)
    $response = $response . "t" . ">";
else
     $response = $response . "f" . ">";
     
if ($sendUserRemoveNotifications)
    $response = $response . "t" . ">";
else
     $response = $response . "f" . ">";


echo $response;

?>