<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$userMail = $_POST["mail"];    
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$newDate = $_POST["newDate"];

$conn->begin_transaction();

$cmd = "SELECT Time FROM ReservedQueue WHERE Time = '$newDate'";
if ( checkIfExist($conn,$cmd) != false)
    die("queue exist");
    
$deleteEmptyQueueCmd = "DELETE FROM EmptyQueue WHERE Time = '$newDate'";

$query = mysqli_query($conn,$deleteEmptyQueueCmd); // if exist he remove
if (!$query)
    die("cmd failed");
    
$selectReservedQueueCmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail'";

$reservedQueue = checkIfExist($conn,$selectReservedQueueCmd);
if ($reservedQueue)
{
       $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
       $timeStamp =  $date->format('Y-m-d H:i:').'00';
       $selectPastReservedQueueCmd ="SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = '$userMail'";
       $pastReservedQueue = checkIfExist($conn,$selectPastReservedQueueCmd);
       if ( $pastReservedQueue )  
       {
          $insertToPastReservedQueueCmd = "INSERT INTO PastReservedQueue VALUES ('$reservedQueue','$userMail')";
          $query = mysqli_query($conn,$insertToPastReservedQueueCmd);
          if ( (!$query) || $conn->affected_rows != 1)
             die("cmd failed");
       }
       else //to user have future reserved queue
         die("cmd failed");
       $deleteReservedQueueCmd = "DELETE FROM ReservedQueue WHERE UserMail = '$userMail'";
       $query = mysqli_query($conn,$deleteReservedQueueCmd);
       if ( (!$query) || $conn->affected_rows != 1)
             die("cmd failed");
}
$insertToReservedQueueCmd = "INSERT INTO ReservedQueue VALUES ('$newDate','$userMail')";
$query = mysqli_query($conn,$insertToReservedQueueCmd);
if ($query && $conn->affected_rows == 1)
{
   require "utils/send_user_queue_notifications.php";
    if ($sendUserQueueNotifications)
        sendNotificationAndMailToUser($newDate,$userMail);
    $conn->commit();  
    echo 'V';
}
else
      die ("cmd failed");

      
function checkIfExist($conn,$cmd)
{
    $query = mysqli_query($conn,$cmd);
    $res = mysqli_fetch_assoc($query);
    if ($res['Time'] )
       return $res['Time'];
    else
       return false;
}

function sendNotificationAndMailToUser($date,$userMail)
{
    $year = substr($date,0,4);
    $month = substr($date,4,2);
    $day = substr($date,6,2);
    $hour =  substr($date,8,2);
    $min = substr($date,10,2);
    $notiBody = "בתאריך ".$day.".".$month.".".$year." בשעה ".$hour.":".$min  ;
    $notiTitle = "נקבע לך תור חדש";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
    $mailTitle = "נקבע לך תור חדש במספרה";
    $mailBody = "נקבע לך תור חדש במספרה " . $notiBody;
    mail($userMail,$mailTitle,$mailBody);
}

?>