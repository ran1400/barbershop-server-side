<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$userMail = $_POST["mail"];    
$date =  $_POST["date"];
$cmd = "DELETE FROM ReservedQueue WHERE Time = '$date' AND UserMail = '$userMail'";
$conn->begin_transaction();
$query = mysqli_query($conn,$cmd);
if ($query && $conn->affected_rows == 1)
{
    require "utils/send_user_queue_notifications.php";
    if ($sendUserQueueNotifications)
        sendNotificationAndMailToUser($date,$userMail);
    $conn->commit();
    echo 'V';
}
else
      die("cmd failed");

function sendNotificationAndMailToUser($date,$userMail)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $notiBody = "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " בוטל" ;
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
    require "../send_mail.php";
    sendSingleMail($userMail, "התור שלך במספרה בוטל על ידי המנהל", $notiBody);

}

?>