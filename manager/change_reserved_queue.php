<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 

$newQueue =  $_POST["newQueue"];

$conn->begin_transaction();

$query = "SELECT Time FROM ReservedQueue WHERE Time = ?";
$res = runSelectQuery($conn,$query,[$newQueue]);
if ( $res === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
else if ($res !== false) 
    die(json_encode(["error" => "queue exist"]));

    
$userMail = $_POST["mail"];    

$prevQueue =  $_POST["prevQueue"] ;
    
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ? AND Time = ?"; //check that the request info is also coorect in the server
if (! runSelectQuery($conn,$query,[$userMail,$prevQueue])) 
    die(json_encode(["error" => "cmd failed : " . $query]));

$query = "DELETE FROM EmptyQueue WHERE Time = ?"; // if the new queue is on the empty queues list delete him
$queuesDeleted = runExecQuery($conn,$query,[$newQueue]);
if ($queuesDeleted === null)
    die(json_encode(["error" => "cmd failed : " . $query]));

$query = "UPDATE ReservedQueue SET Time = ? WHERE UserMail = ?"; //make the update
$queuesThatUpdate = runExecQuery($conn,$query,[$newQueue,$userMail]);
if ( $queuesThatUpdate != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
    
$addToEmptyQueue = $_POST["addToEmptyQueue"];
    
if ($addToEmptyQueue)
{
    $query = "INSERT INTO EmptyQueue VALUE (?)";
    $queuesInserted = runExecQuery($conn,$query,[$prevQueue]);
    if ( $queuesInserted != 1)
        die(json_encode(["error" => "cmd failed : " . $query]));
}

$notiBody = getNotiBody($prevQueue);

$notificationSended = sendNotificationToUser($notiBody,$userMail);
if ($notificationSended === false)
    die(json_encode(["error" => "cmd failed : send notification"]));

$mailSended = sendMailToUser($newQueue,$notiBody,$userMail);
if ($mailSended === false)
    die(json_encode(["error" => "cmd failed : send mail"]));

$conn->commit();
echo json_encode(["error" => "no"]);

function sendMailToUser($newQueue,$notiBody,$userMail)
{
    $year = substr($newQueue,0,4);
    $month = substr($newQueue,4,2);
    $day = substr($newQueue,6,2);
    $hour = substr($newQueue,8,2);
    $min = substr($newQueue,10,2);
    $mailTitle = "התור שלך במספרה שונה על ידי המנהל";
    $mailBody = $notiBody . " ל " . $day.".".$month.".".$year." בשעה ".$hour . ":" . $min ; 
    require "../send_mail.php";
    return sendSingleMail($userMail, $mailTitle, $mailBody);
}

function sendNotificationToUser($notiBody,$userMail)
{
    $notiTitle = "התור שלך שונה על ידי המנהל";
    require_once "utils/send_notification_to_user.php";
    return sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}

function getNotiBody($prevQueue)
{
    $year = substr($prevQueue,0,4);
    $month = substr($prevQueue,5,2);
    $day = substr($prevQueue,8,2);
    $hour =  substr($prevQueue,11,5);
    return "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " הוזז" ;
}


?>