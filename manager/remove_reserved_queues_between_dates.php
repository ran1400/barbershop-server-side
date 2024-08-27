<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
else
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $firstDate =  $_POST["firstDate"];
    $secondDate =  $_POST["secondDate"];
    $selectCmd = "SELECT Time,UserMail FROM ReservedQueue WHERE Time > '$timeStamp' AND Time BETWEEN '$firstDate' AND '$secondDate'";
    $conn->begin_transaction();
    $query = mysqli_query($conn,$selectCmd);
    if(!$query)
        die("cmd failed");
    require "utils/send_user_queue_notifications.php";
    if ($sendUserQueueNotifications)
    {
        require 'utils/send_notification_to_user.php';
        while ($row = mysqli_fetch_array($query))
            sendNotificationAndMailToUser($row['UserMail'],$row['Time']);
    }
    $deleteCmd = "DELETE FROM ReservedQueue WHERE Time > '$timeStamp' AND Time BETWEEN '$firstDate' AND '$secondDate'";
    $query = mysqli_query($conn,$deleteCmd);
    if ($query)
       echo $conn->affected_rows; 
    else
       echo("cmd failed");
    $conn->commit();   
}

  
function sendNotificationAndMailToUser($userMail,$queue)
{
    $year = substr($queue,0,4);
    $month = substr($queue,5,2);
    $day = substr($queue,8,2);
    $hour =  substr($queue,11,5);
    $notiBody = "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour . " בוטל" ;
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
    mail($userMail,"התור שלך במספרה בוטל על ידי המנהל",$notiBody);
}


?>