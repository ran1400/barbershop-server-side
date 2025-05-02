<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 

$newDate = $_POST["newDate"];
$userMail = $_POST["userMail"];

$conn->begin_transaction();

$query = "SELECT Time FROM ReservedQueue WHERE Time = ?";
$res = runSelectQuery($conn,$query,[$newDate]);
if ($res === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
else if ($res !== false) 
    die(json_encode(["error" => "queue exist"]));

    
$query = "DELETE FROM EmptyQueue WHERE Time = ?";
$queuesDeleted = runExecQuery($conn,$query,[$newDate]); // if exist he remove
if ($queuesDeleted === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
    
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ?";
$reservedQueue = runSelectQuery($conn,$query,[$userMail]);
if ($reservedQueue === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
if ($reservedQueue)
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
    else //to user have future reserved queue
        die(json_encode(["error" => "cmd failed : to user already have reserved qeuue"]));
    $query = "DELETE FROM ReservedQueue WHERE UserMail = ?";
    $queuesDeleted = runExecQuery($conn,$query,[$userMail]);
    if ( $queuesDeleted != 1)
        die(json_encode(["error" => "cmd failed : " . $query]));
}
$query = "INSERT INTO ReservedQueue VALUES (?,?)";
$queuesInserted = runExecQuery($conn,$query,[$newDate,$userMail]);
if ($queuesInserted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));

$dateString = makeDateString($newDate);
if (sendNotificationToUser($dateString,$userMail) === false)
    die(json_encode(["error" => "cmd failed : send notification to user"]));
if (sendMailToUser($dateString,$userMail) === false)
    die(json_encode(["error" => "cmd failed : send mail to user"]));

$conn->commit();  
echo json_encode(["error" => "no"]);


function makeDateString($date)
{
    $year = substr($date,0,4);
    $month = substr($date,4,2);
    $day = substr($date,6,2);
    $hour =  substr($date,8,2);
    $min = substr($date,10,2);
    return "בתאריך ".$day.".".$month.".".$year." בשעה ".$hour.":".$min;
}

function sendNotificationToUser($notiBody,$userMail)
{
    $notiTitle = "נקבע לך תור חדש";
    require_once "utils/send_notification_to_user.php";
    return sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}
      
function sendMailToUser($dateString,$userMail)
{
    $mailTitle = "נקבע לך תור חדש במספרה";
    $mailBody = "נקבע לך תור חדש במספרה " . $dateString;
    require_once "../send_mail.php";
    return sendSingleMail($userMail, $mailTitle, $mailBody);
}

?>