<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$newQueue =  $_POST["newQueue"];

$conn->begin_transaction();

$cmd = "SELECT Time FROM ReservedQueue WHERE Time = '$newQueue'";
if ( checkIfExist($conn,$cmd) != false)
    die("queue exist");

    
$userMail = $_POST["mail"];    

$prevQueue =  $_POST["prevQueue"] ;
    
$cmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail' AND Time = '$prevQueue'"; //check that the request info is also coorect in the server
if (checkIfExist($conn,$cmd) == false) 
    die("cmd failed");

$cmd = "DELETE FROM EmptyQueue WHERE Time = '$newQueue'"; // if the new queue is on the empry queues list delete it
$query = mysqli_query($conn,$cmd);
if ( (!$query))
    die("cmd failed");

$cmd = "UPDATE ReservedQueue SET Time = '$newQueue' WHERE UserMail = '$userMail'"; //make the update
$query = mysqli_query($conn,$cmd);
if ( (!$query) || $conn->affected_rows != 1)
    die("cmd failed");
    
$addToEmptyQueue = $_POST["addToEmptyQueue"];
    
if ($addToEmptyQueue == "yes")
{
   $cmd = "INSERT INTO EmptyQueue VALUE ('$prevQueue')";
   $query = mysqli_query($conn,$cmd); 
   if ( (!$query) || $conn->affected_rows != 1)
    die("cmd failed");
}
require "utils/send_user_queue_notifications.php";
if ($sendUserQueueNotifications)
    sendNotificationToUser($prevQueue,$userMail);
$conn->commit();
echo 'V';

function sendNotificationToUser($prevQueue,$userMail)
{
    $year = substr($prevQueue,0,4);
    $month = substr($prevQueue,5,2);
    $day = substr($prevQueue,8,2);
    $hour =  substr($prevQueue,11,5);
    $notiBody = "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " הוזז" ;
    $notiTitle = "התור שלך שונה על ידי המנהל";
    require "utils/send_notification_to_user.php";
    sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}

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

?>