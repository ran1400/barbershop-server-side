<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require "utils/check_if_blocked_user.php";

if ($blockedUser)
   die("permission problem"); 

$date = $_POST["date"];
$selectReservedQueueCmd = "SELECT Time FROM ReservedQueue WHERE Time = '$date' AND UserMail = '$userMail'";
$deleteReservedQueueCmd = "DELETE FROM ReservedQueue WHERE Time = '$date' AND UserMail = '$userMail'";  
$insertEmptyQueueCmd = "INSERT INTO EmptyQueue VALUE ('$date')";  
$conn->begin_transaction();
$query = mysqli_query($conn,$selectReservedQueueCmd);
if(! $query)
    die("cmd failed");
$res = mysqli_fetch_assoc($query);
if ($res["Time"])
{
       mysqli_query($conn,$deleteReservedQueueCmd);
       $insertCmd = mysqli_query($conn,$insertEmptyQueueCmd);
       if ($insertCmd && $conn->affected_rows == 1)
       {
            $timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
            $timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
            require 'utils/to_send_notification.php';
            if (toSendNotification($date,$timeStamp))
               sendNotification($_POST["userName"],$date);
            $conn -> commit();
            echo 'V';
       }
       else
           die("cmd failed");
}
else
    die("cmd failed");
    
function sendNotification($userName,$date)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $notiTitle = $userName . " ביטל תור";
    $notiBody = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    require "utils/send_notification_to_manager.php";
    sendFCM('userDeleteQueue',$notiTitle,$notiBody);
}
   

?>