<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 
   
$userMail = $_POST["userMail"];
$sendNotificationAboutReservedQueue = false;

$conn->begin_transaction();
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ?";
$reservedQueue = runSelectQuery($conn,$query,[$userMail]);
if ($reservedQueue === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
if ($reservedQueue !== false) //to the user have reserved queue 
{
    $timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
    $query ="SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = ?";
    $pastReservedQueue = runSelectQuery($conn,$query,[$userMail]);
    if ($pastReservedQueue === null)
        die(json_encode(["error" => "cmd failed : " . $query]));
    if ( $pastReservedQueue )  
    {
        $query = "INSERT INTO PastReservedQueue VALUES (?,?)";
        $queuesInserted = runExecQuery($conn,$query,[$reservedQueue['Time'],$userMail]);
        if ( $queuesInserted != 1)
            die(json_encode(["error" => "cmd failed : " . $query]));
    }
    else //future reserved queue
    {
        $query = "INSERT INTO EmptyQueue VALUE (?)";
        $queuesInserted = runExecQuery($conn,$query,[$reservedQueue['Time']]);
        if ( $queuesInserted != 1)
            die(json_encode(["error" => "cmd failed : " . $query]));
        $sendNotificationAboutReservedQueue = true;
    }
    $query = "DELETE FROM ReservedQueue WHERE UserMail  = ?";
    $queuesDeleted = runExecQuery($conn,$query,[$userMail]);
    if ($queuesDeleted != 1)
        die(json_encode(["error" => "cmd failed : " . $query]));
}
$query = "UPDATE User SET Block = 1 WHERE Mail = ?";
$usersUpdate = runExecQuery($conn,$query,[$userMail]);
if ($usersUpdate != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));

require_once "utils/to_send_user_block_notification.php";
$toSendUserBlockNotification = toSendUserBlockNotification($conn);
if ($toSendUserBlockNotification === null)
    die(json_encode(["error" => "cmd failed : check if send user block notification"]));
if ($toSendUserBlockNotification)
{
    if (sendBlockMailToUser($userMail) === false)
        die(json_encode(["error" => "cmd failed : send block mail to user"]));
    if(sendBlockNotificationToUser($userMail) === false)
        die(json_encode(["error" => "cmd failed : send block notification to user"]));  
}
if ($sendNotificationAboutReservedQueue)
{
    $notiContent = makeQueueNotiContent($reservedQueue['Time']);
    if (sendQueueNotificationToUser($notiContent,$userMail) === false)
        die(json_encode(["error" => "cmd failed : send remove queue notification to user"]));
    if (sendQueueMailToUser($notiContent,$userMail) === false)
        die(json_encode(["error" => "cmd failed : send remove queue mail to user"]));
}
    
$conn->commit();
echo json_encode(["error" => "no"]);


function sendBlockMailToUser($userMail)
{
    $title = "החשבון שלך במספרה נחסם על ידי המנהל";
    $body = "חסימת החשבון כוללת מחיקת תורים עתידיים";
    require_once "../send_mail.php";
    return sendSingleMail($userMail,$title,$body);
}
function sendBlockNotificationToUser($userMail)
{
    $title = "החשבון שלך נחסם על ידי המנהל";
    $body = "חסימת החשבון כוללת מחיקת תורים עתידיים";
    require_once "utils/send_notification_to_user.php";
    return sendFCM($userMail,'other',$title,$body);
}

function makeQueueNotiContent($time)
{
    $year = substr($time,0,4);
    $month = substr($time,5,2);
    $day = substr($time,8,2);
    $hour =  substr($time,11,5);
    return "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " בוטל" ;
}

function sendQueueNotificationToUser($notiBody,$userMail)
{
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    require_once "utils/send_notification_to_user.php";
    return sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}
      
function sendQueueMailToUser($mailContent,$userMail)
{
    $mailTitle = "התור שלך במספרה בוטל על ידי המנהל";
    require_once "../send_mail.php";
    return sendSingleMail($userMail, $mailTitle, $mailContent);
}

?>