<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed");

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$userMail = $_POST["mail"];
$userName = $_POST["name"];

$conn->begin_transaction();

$selectReservedQueueCmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail'";
$reservedQueue = checkIfExist($conn,$selectReservedQueueCmd);
if ($reservedQueue) //to the user have reserved queue 
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $selectPastReservedQueueCmd ="SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = '$userMail'";
    $pastReservedQueue = checkIfExist($conn,$selectPastReservedQueueCmd);
    if ($pastReservedQueue) //to the user have pest reserved queue
    {
        $insertToPastReservedQueueCmd = "INSERT INTO PastReservedQueue VALUES ('$pastReservedQueue','$userMail')";
        $query = mysqli_query($conn,$insertToPastReservedQueueCmd);
        if ($conn->affected_rows != 1)
            die("cmd failed");
        $cmd = "DELETE FROM ReservedQueue WHERE UserMail  = '$userMail'";
        $query = mysqli_query($conn,$cmd);
        if ($conn->affected_rows != 1)
            die("cmd failed");
    }
    else // to user have future reserved queue
    {
        $cmd = "DELETE FROM ReservedQueue WHERE UserMail  = '$userMail'";
        $query = mysqli_query($conn,$cmd);
        if ($conn->affected_rows != 1)
            die("cmd failed");
        $cmd = "INSERT INTO EmptyQueue VALUE ('$reservedQueue')";
        $query = mysqli_query($conn,$cmd);
        if ($conn->affected_rows != 1)
            die("cmd failed");       
    }
}
$cmd = "DELETE FROM User WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");
$cmd = "INSERT INTO DeletedUser VALUE ('$userMail','$userName')";
$query = mysqli_query($conn,$cmd);
if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");

require "utils/send_user_remove_notifications.php";
if ($sendUserRemoveNotifications)
    sendNotificationAndMailToUser($userMail);
$conn->commit();
echo 'V';

function checkIfExist($conn,$cmd)
{
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die("cmd failed");
    $res = mysqli_fetch_assoc($query);
    if ($res['Time'] )
       return $res['Time'];
    else
       return false;
}

function sendNotificationAndMailToUser($userMail)
{
    $notiTitle = "החשבון שלך נמחק על ידי המנהל";
    $notiBody = "מחיקת החשבון כוללת מחיקת תורים עתידיים";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'other',$notiTitle,$notiBody);
    mail($userMail,"החשבון שלך במספרה נמחק על ידי המנהל",$notiBody);
}
?>