<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
   
$userMail = $_POST["mail"];    
$time =  $_POST["time"] ;
$query = "DELETE FROM ReservedQueue WHERE Time = ? AND UserMail = ?";
$conn->begin_transaction();
$queuesDeleted = runExecQuery($conn,$query,[$time,$userMail]);
if ($queuesDeleted != 1)
    die(json_encode(["error" => "cmd failed : " . $query])); 
$query = "INSERT INTO EmptyQueue VALUE (?)";
$queuesInserted = runExecQuery($conn,$query,[$time]);
if ($queuesInserted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$notiBody = getNotiBody($time);
if (sendNotificationToUser($userMail,$notiBody) === false)
    die(json_encode(["error" => "cmd failed : send notificatin to user"]));
if (sendMailToUser($userMail,$notiBody) == false)
    die(json_encode(["error" => "cmd failed : send mail to user"]));
$conn->commit();
echo json_encode(["error" => "no"]); 


function getNotiBody($time)
{
    $year = substr($time,0,4);
    $month = substr($time,5,2);
    $day = substr($time,8,2);
    $hour =  substr($time,11,5);
    return "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " בוטל" ;
}

function sendNotificationToUser($userMail,$notiBody)
{
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    require "utils/send_notification_to_user.php";
    return sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}

function sendMailToUser($userMail,$mailBody)
{
    $mailTitle = "התור שלך במספרה בוטל על ידי המנהל";
    require "../send_mail.php";
    return sendSingleMail($userMail,$mailTitle,$mailBody);
}


?>