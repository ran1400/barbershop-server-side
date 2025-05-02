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
    
require_once "utils/is_manager_block_the_system.php";
$managerBlockSystem = isManagerBlockTheSystem($conn);
if ($managerBlockSystem === null)
    die(json_encode(["error" => "cmd failed - check if the manager block the system"]));
if ($managerBlockSystem)
    die(json_encode(["error" => "manager block the system"]));
    
require_once "utils/check_if_blocked_user.php";
$checkIfBlockedUser = checkIfBlockedUser($conn,$userMail);
if ($checkIfBlockedUser === null)
    die(json_encode(["error" => "cmd failed - check if blocked user"]));
if ($checkIfBlockedUser)
    die(json_encode(["error" => "permission problem"]));

$newDate = $_POST["newDate"];
$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';

if ($newDate <= $timeStamp) 
    die(json_encode(["error" => "cmd failed - not future queue"]));
    
$conn->begin_transaction();

$query = "SELECT Time FROM EmptyQueue WHERE Time = ? AND Time >= '$timeStamp'";
$haveTheQueue = runSelectQuery($conn,$query,[$newDate]);
if (! $haveTheQueue)
    die(json_encode(["error" => "cmd failed : " .$query]));

$query = "DELETE FROM EmptyQueue WHERE Time = ?";
$deleteFromEmptyQueue = runExecQuery($conn,$query,[$newDate]);
if ($deleteFromEmptyQueue != 1)
    die(json_encode(["error" => "cmd failed : " .$query]));
  
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ?";
$reservedQueue = runSelectQuery($conn,$query,[$userMail]);
if ($reservedQueue === null)
    die(json_encode(["error" => "cmd failed : " .$query]));
if ($reservedQueue)
{
    $query = "SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = ?";
    $pastReservedQueue = runSelectQuery($conn,$query,[$userMail]);
    if ($pastReservedQueue === null)
        die(json_encode(["error" => "cmd failed : " .$query]));
    if ($pastReservedQueue)  
    {
        $query = "INSERT INTO PastReservedQueue VALUES (?,?)";
        $queuesInsert = runExecQuery($conn,$query,[$reservedQueue['Time'],$userMail]);
        if ($queuesInsert != 1)
            die(json_encode(["error" => "cmd failed : " .$query]));
        $query = "DELETE FROM ReservedQueue WHERE UserMail = ?";
        $queuesDelete = runExecQuery($conn,$query,[$userMail]);
        if ($queuesDelete != 1) 
            die(json_encode(["error" => "cmd failed : " .$query]));
    }
}
$query = "INSERT INTO ReservedQueue VALUES (?, ?)";
$queusInsert = runExecQuery($conn,$query,[$newDate,$userMail]);
if ($queusInsert != 1)
     die(json_encode(["error" => "cmd failed : " .$query])); 
$dateString = makeDateString($newDate) ; // make this string -> "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
$mailSended = sendMailToUser($userMail,$dateString);
if (! $mailSended)
        die(json_encode(["error" => "cmd failed : send mail failed"])); 
require_once 'utils/to_send_notification.php';
$toSendNotification = toSendNotification($conn,$newDate,$timeStamp);
if ($toSendNotification === null)
    die(json_encode(["error" => "cmd failed : check if to send notification"]));
if ($toSendNotification)
{
    $notificationSended = sendNotificationToManager($userMail,$_POST["userName"],$dateString);
    if ($notificationSended === false)
        die(json_encode(["error" => "cmd failed : send notification failed"])); 
}
     
$conn->commit(); 
echo json_encode(["newQueue" => $newDate]);


function makeDateString($date)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $res = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    return $res;
}

function sendNotificationToManager($userMail,$userName,$dateString)
{
    $notiTitle = $userName . " קבע תור";
    require_once "utils/send_notification_to_manager.php";
    return sendFCM('userAddQueue',$notiTitle,$dateString);
}

function sendMailToUser($userMail,$dateString)
{
    $mailTitle = "נקבע לך תור למספרה";
    $mailBody = "נקבע לך תור למספרה " . $dateString;
    require_once "../send_mail.php";
    return sendSingleMail($userMail, $mailTitle, $mailBody);
}

?>