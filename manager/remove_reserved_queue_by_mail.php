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
$time =  $_POST["time"];
$conn->begin_transaction();
$query = "DELETE FROM ReservedQueue WHERE Time = ? AND UserMail = ?";
$queuesDeleted = runExecQuery($conn,$query,[$time,$userMail]);
if ($queuesDeleted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$notiContent = makeNotiContent($time);
if (sendNotificationToUser($notiContent,$userMail) === false)
    die(json_encode(["error" => "cmd failed : send notification to user"]));
if (sendMailToUser($notiContent,$userMail) === false)
    die(json_encode(["error" => "cmd failed : send mail to user"]));
$conn->commit();
echo json_encode(["error" => "no"]);

function makeNotiContent($time)
{
    $year = substr($time,0,4);
    $month = substr($time,5,2);
    $day = substr($time,8,2);
    $hour =  substr($time,11,5);
    return "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour. " בוטל" ;
}

function sendNotificationToUser($notiBody,$userMail)
{
    $notiTitle = "מצטערים,התור שלך בוטל על ידי המנהל";
    require_once "utils/send_notification_to_user.php";
    return sendFCM($userMail,'queuesUpdates',$notiTitle,$notiBody);
}
      
function sendMailToUser($mailContent,$userMail)
{
    $mailTitle = "התור שלך במספרה בוטל על ידי המנהל";
    require_once "../send_mail.php";
    return sendSingleMail($userMail, $mailTitle, $mailContent);
}

?>