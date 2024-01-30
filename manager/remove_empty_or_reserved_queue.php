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
    if ($res['UserMail'] )// the queue is reserved queue
    {
         $userMail = $res['UserMail'];
         $cmd = "DELETE FROM ReservedQueue WHERE Time = '$date'";
         $query = mysqli_query($conn,$cmd);
         if ($query && $conn->affected_rows == 1)
         {
            require "utils/send_user_queue_notifications.php";
            if ($sendUserQueueNotifications)
                sendNotificationToUser($date,$userMail);
            $conn->commit();
            die('reservedQueue');
         }
         else
            die("cmd failed");
    }
    else // the queue is empty queue
    {
        $cmd = "DELETE FROM EmptyQueue WHERE Time = '$date'";
        $query = mysqli_query($conn,$cmd);
        if (!$query)
            die("cmd failed");  
        if ($conn->affected_rows == 1)
        {
            $conn->commit();
            die('emptyQueue');  
        }
        else
           die("not found");  
    }
}

function sendNotificationToUser($date,$userMail)
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
}

?>