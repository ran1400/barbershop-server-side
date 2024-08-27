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
    $date =  $_POST["date"];
    $cmd = "SELECT UserMail FROM ReservedQueue WHERE Time = '$date'";
    $conn->begin_transaction();
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die("cmd failed");
    $res = mysqli_fetch_assoc($query);
    if ($res['UserMail'] )
         $userMail = $res['UserMail'];
    else
        die("not found");
    $cmd = "DELETE FROM ReservedQueue WHERE Time = '$date'";
    $query = mysqli_query($conn,$cmd);
    if ($query && $conn->affected_rows == 1)
    {
        require "utils/send_user_queue_notifications.php";
        if ($sendUserQueueNotifications)
            sendNotificationAndMailToUser($date,$userMail); 
        echo 'V';
    }
    else
       echo("cmd failed");
    $conn->commit();
}

function sendNotificationAndMailToUser($date,$userMail)
{
    $year = substr($date,0,4);
    $month = substr($date,4,2);
    $day = substr($date,6,2);
    $hour =  substr($date,8,2);
    $min = substr($date,10,2);
    $notiBody = "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour.":".$min . " בוטל" ;
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
    mail($userMail,"התור שלך במספרה בוטל על ידי המנהל",$notiBody);
}

?>