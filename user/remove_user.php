<?php

require "utils/connect.php";

if ($conn->connect_error)
    die("connection failed"); 

$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$cmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail'";
$conn->begin_transaction();
$reservedQueue = checkIfExist($conn,$cmd);
if ($reservedQueue) //to user have queue
{
        $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
        $timeStamp =  $date->format('Y-m-d H:i:').'00';
        $selectPastReservedQueueCmd ="SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = '$userMail'";
        $pastReservedQueue = checkIfExist($conn,$selectPastReservedQueueCmd);
        if ($pastReservedQueue) //to the user have pest reserved queue
        {
            $insertToPastReservedQueueCmd = "INSERT INTO PastReservedQueue VALUES ('$pastReservedQueue','$userMail')";
            $query = mysqli_query($conn,$insertToPastReservedQueueCmd);
            if ( (!$query) || $conn->affected_rows != 1)
                die("cmd failed");
            $cmd = "DELETE FROM ReservedQueue WHERE UserMail  = '$userMail'";
            $query = mysqli_query($conn,$cmd);
            if ( (!$query) || $conn->affected_rows != 1)
                die("cmd failed");
        }
        else // to user have future reserved queue
        {
            $cmd = "DELETE FROM ReservedQueue WHERE UserMail  = '$userMail'";
            $query = mysqli_query($conn,$cmd);
            if ( (!$query) || $conn->affected_rows != 1)
                die("cmd failed");
            $cmd = "INSERT INTO EmptyQueue VALUE ('$reservedQueue')";
            $query = mysqli_query($conn,$cmd);
            if ( (!$query) || $conn->affected_rows != 1)
                die("cmd failed");   
            require 'utils/to_send_notification.php';
            if (toSendNotification($reservedQueue,$timeStamp))
                sendNotification($_POST["userName"],$reservedQueue);
        }
}
    
$cmd = "DELETE FROM User WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if ( (!$query) || $conn->affected_rows != 1)
        die("cmd failed");
$userName = $_POST["userName"];
$cmd = "INSERT INTO DeletedUser VALUE ('$userMail','$userName')";
$query = mysqli_query($conn,$cmd);
if ( (!$query) || $conn->affected_rows != 1)
        die("cmd failed");
$conn->commit();
echo 'V';

function sendNotification($userName,$date)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $notiTitle = $userName . " ביטל תור";
    $notiBody = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    require "utils/send_notification_to_manager.php";
    sendFCM('userDeleteQueue',$notiTitle,$notiBody);
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