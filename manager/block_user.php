<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed");

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$userMail = $_POST["mail"];

$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';
$cmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail' AND Time >= '$timeStamp'";
$conn->begin_transaction();
$query = mysqli_query($conn,$cmd);
if (! $query)
    die("cmd failed");
$query = mysqli_fetch_assoc($query);
if ($query['Time']) //to the user have reserved queue 
{
    $reservedQueue = $query['Time'];
    $cmd = "DELETE FROM ReservedQueue WHERE UserMail  = '$userMail'";
    $query = mysqli_query($conn,$cmd);
    if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");
    $cmd = "INSERT INTO EmptyQueue VALUE ('$reservedQueue')";
    $query = mysqli_query($conn,$cmd);
    if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");
}
$cmd = "UPDATE User SET Block = 1 WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");
require "utils/send_user_block_notifications.php";
if ($sendUserBlockNotifications)
    sendNotificationAndMailToUser($userMail);
$conn->commit();
echo 'V';

function sendNotificationAndMailToUser($userMail)
{
    $title = "החשבון שלך נחסם על ידי המנהל";
    $body = "חסימת החשבון כוללת מחיקת תורים עתידיים";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'other',$title,$body);
    require "../send_mail.php";
    sendSingleMail($userMail, "החשבון שלך במספרה נחסם על ידי המנהל", $body);
}

?>