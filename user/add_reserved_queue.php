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

$newDate = $_POST["newDate"];
$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';

if ($newDate <= $timeStamp) //secure check also for the notifications
    die("cmd failed");

$selectEmptyQueueCmd = "SELECT Time FROM EmptyQueue WHERE Time = '$newDate' AND Time >= '$timeStamp' ";
$selectReservedQueueCmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail'";
$selectPastReservedQueueCmd ="SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = '$userMail'";
$deleteReservedQueueCmd = "DELETE FROM ReservedQueue WHERE UserMail = '$userMail'";
$deleteEmptyQueueCmd = "DELETE FROM EmptyQueue WHERE Time = '$newDate'";
$insertToReservedQueueCmd = "INSERT INTO ReservedQueue VALUES ('$newDate','$userMail')";

$conn->begin_transaction();

if (checkIfExist($conn,$selectEmptyQueueCmd))
    mysqli_query($conn,$deleteEmptyQueueCmd);
else
  die("cmd failed");
$reservedQueue = checkIfExist($conn,$selectReservedQueueCmd);
if ($reservedQueue)
{
       $pastReservedQueue = checkIfExist($conn,$selectPastReservedQueueCmd);
       if ($pastReservedQueue)  
       {
          $insertToPastReservedQueueCmd = "INSERT INTO PastReservedQueue VALUES ('$reservedQueue','$userMail')";
          $query = mysqli_query($conn,$insertToPastReservedQueueCmd);
          if ($conn->affected_rows != 1 || (!$query))
             die("cmd failed");
          $query = mysqli_query($conn,$deleteReservedQueueCmd);
          if ($conn->affected_rows != 1 || (!$query))
             die("cmd failed");
       }
       else //to user have future reserved queue
         die("cmd failed");
}
$query = mysqli_query($conn,$insertToReservedQueueCmd);
if ((!$query) || $conn->affected_rows != 1)
      die ("cmd failed");

require 'utils/to_send_notification.php';
if (toSendNotification($newDate,$timeStamp))
     sendNotification($_POST["userName"],$newDate);
$conn->commit();   
die ('V');

function sendNotification($userName,$date)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $notiTitle = $userName . " קבע תור";
    $notiBody = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    require "utils/send_notification_to_manager.php";
    sendFCM('userAddQueue',$notiTitle,$notiBody);
}
      
function checkIfExist($conn,$cmd)
{
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die ("cmd failed");
    $res = mysqli_fetch_assoc($query);
    if ($res['Time'] )
       return $res['Time'];
    else
       return false;
}

?>