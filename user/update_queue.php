<?php

require "utils/manager_block_system.php";

if ($managerBlockSystem)
    die("managerBlockSystem");

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
   

$newDate =  $_POST["newDate"];
$cmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail'";
$conn->begin_transaction();
$query = mysqli_query($conn,$cmd);
if (!$query)
    die("cmd failed");
$res = mysqli_fetch_assoc($query);
$prevDate = $res['Time'];
if ($prevDate) //there are reserved queue
{    
    $cmd = "DELETE FROM ReservedQueue WHERE UserMail = '$userMail'"; 
    $query = mysqli_query($conn,$cmd);
    if ( (!$query) ||  $conn->affected_rows != 1)
        die("cmd failed");
    $cmd = "INSERT INTO EmptyQueue VALUE ('$prevDate')";
    $query = mysqli_query($conn,$cmd);
    if ( (!$query) ||  $conn->affected_rows != 1)
        die("cmd failed");
    $cmd = "DELETE FROM EmptyQueue WHERE Time = '$newDate'";
    $query = mysqli_query($conn,$cmd);
    if ( (!$query) ||  $conn->affected_rows != 1)
        die("cmd failed");
    $cmd = "INSERT INTO ReservedQueue VALUES ('$newDate','$userMail')";
    $query = mysqli_query($conn,$cmd);
    if ( (!$query) ||  $conn->affected_rows != 1)
        die("cmd failed");
    $timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
    require 'utils/to_send_notification.php';
    if (toSendNotification($prevDate,$timeStamp))
            sendNotification($_POST["userName"],$prevDate,$newDate);
    else if (toSendNotification($newDate,$timeStamp))
            sendNotification($_POST["userName"],$prevDate,$newDate);
    $conn->commit(); 
    echo 'V';
}
else
  die("cmd failed");
  
function sendNotification($userName,$prevDate,$newDate)
{
    $year = substr($prevDate,0,4);
    $month = substr($prevDate,5,2);
    $day = substr($prevDate,8,2);
    $hour =  substr($prevDate,11,5);
    $prevDateStr = $day.".".$month.".".$year. " " . $hour;
    $year = substr($newDate,0,4);
    $month = substr($newDate,5,2);
    $day = substr($newDate,8,2);
    $hour =  substr($newDate,11,5);
    $newDateStr = $day.".".$month.".".$year. " " . $hour;
    $notiTitle = $userName . " עדכן תור";
    $notiBody = $prevDateStr . " -> " . $newDateStr;
    require "utils/send_notification_to_manager.php";
    sendFCM('userUpdateQueue',$notiTitle,$notiBody);
}
?>