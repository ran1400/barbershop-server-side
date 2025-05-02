<?php


require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"])); 


$userMail = $_POST["userMail"];
$userSecretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

$permissionCheck = permissionCheck($conn,$userMail,$userSecretKey);
if ($permissionCheck  === null)
    die(json_encode(["error" => "cmd failed - permission check"]));
else if ($permissionCheck  === false)
    die(json_encode(["error" => "permission problem"])); 
   
require_once "utils/check_if_blocked_user.php";

$checkIfBlockedUser = checkIfBlockedUser($conn,$userMail);
if ($checkIfBlockedUser === null)
    die(json_encode(["error" => "cmd failed - check if blocked user"]));
if ($checkIfBlockedUser)
    die(json_encode(["error" => "permission problem"]));

require_once "utils/is_manager_block_the_system.php";
$managerBlockSystem = isManagerBlockTheSystem($conn);
if ($managerBlockSystem === null)
    die(json_encode(["error" => "cmd failed - check if the manager block the system"]));
if ($managerBlockSystem)
    die(json_encode(["error" => "manager block the system"]));
   

$newDate =  $_POST["newDate"];
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ?";
$conn->begin_transaction();
$prevQueue = runSelectQuery($conn, $query , [$userMail]);
if (!$prevQueue)
    die(json_encode(["error" => "cmd failed : " . $query]));

$prevDate = $prevQueue['Time'];
$query = "DELETE FROM ReservedQueue WHERE UserMail = ?"; 
$queuesDeletedFromReservedQueue = runExecQuery($conn,$query,[$userMail]);
if ( $queuesDeletedFromReservedQueue != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$query = "INSERT INTO EmptyQueue VALUE (?)";
$queuesInsertedToEmptyQueue = runExecQuery($conn,$query,[$prevDate]);
if ( $queuesInsertedToEmptyQueue != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$query = "DELETE FROM EmptyQueue WHERE Time = ?";
$queuesDeletedFromEmptyQueues = runExecQuery($conn,$query,[$newDate]);
if ( $queuesDeletedFromEmptyQueues != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$query = "INSERT INTO ReservedQueue VALUES (?,?)";
$queuesInsertedToReservedQueue = runExecQuery($conn,$query,[$newDate,$userMail]);
if ( $queuesInsertedToReservedQueue != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$datesString = makeDatesString($prevDate,$newDate); // make this string  "prevQueue" -> "newQueue" (formet : dd.mm.yy hh:mm)
sendMailToUser($userMail,$datesString);
$timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
require_once 'utils/to_send_notification.php';
$toSendNotification = toSendNotification($conn,$prevDate,$timeStamp);
if ($toSendNotification === null)
    die(json_encode(["error" => "cmd failed : check if to send notification"]));
if ($toSendNotification)
    sendNotification($_POST["userName"],$datesString);
else
{
    $toSendNotification = toSendNotification($conn,$newDate,$timeStamp);
    if ($toSendNotification === null)
        die(json_encode(["error" => "cmd failed : check if to send notification"]));
    if ($toSendNotification)
        sendNotification($_POST["userName"],$datesString);
}
$conn->commit(); 
echo json_encode(["newQueue" => $newDate]);
  
function makeDatesString($prevDate,$newDate)
{
    $year = substr($prevDate,0,4);
    $month = substr($prevDate,5,2);
    $day = substr($prevDate,8,2);
    $hour =  substr($prevDate,11,5);
    $prevDateStr = $day.".".$month.".".$year. " " . $hour;
    $year = substr($newDate,0,4);
    $month = substr($newDate,5,2);
    $day = substr($newDate,8,2);
    $hour =  substr($newDate,11,5);
    $newDateStr = $day.".".$month.".".$year. " " . $hour;
    $res = $prevDateStr . " -> " . $newDateStr;
    return $res;
}

function sendMailToUser($userMail,$datesString)
{
    $mailTitle = "התור שלך במספרה עודכן";
    $mailBody = "התור עודכן : " . $datesString;
    require "../send_mail.php";
    sendSingleMail($userMail, $mailTitle, $mailBody);
}
  
function sendNotification($userName,$datesString)
{
    require "utils/send_notification_to_manager.php";
    $notiTitle = $userName . " עדכן תור";
    sendFCM('userUpdateQueue',$notiTitle,$datesString);
}

?>