<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"]));  
else
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $firstDate =  $_POST["firstDate"];
    $secondDate =  $_POST["secondDate"];
    $conn->begin_transaction();
    $query = "SELECT Time,UserMail FROM ReservedQueue WHERE Time > '$timeStamp' AND Time BETWEEN ? AND ?";
    $reservedQueues = runSelectMultipleRowsQuery($conn,$query,[$firstDate,$secondDate]);
    if($reservedQueues === null)
        die(json_encode(["error" => "cmd failed : " . $query]));
    $query = "DELETE FROM ReservedQueue WHERE Time > '$timeStamp' AND Time BETWEEN ? AND ?";
    $queuesDeleted = runExecQuery($conn,$query,[$firstDate,$secondDate]);
    if ($queuesDeleted === null)
       die(json_encode(["error" => "cmd failed : " . $query]));
    foreach ($reservedQueues as $row)
    {
        $notiContent = makeNotiContent($row["Time"]);
        if (sendNotificationToUser($notiContent,$row["UserMail"]) === false)
            die(json_encode(["error" => "cmd failed : send notification to user"]));
        if (sendMailToUser($notiContent,$row["UserMail"]) === false)
            die(json_encode(["error" => "cmd failed : send mail to user"]));    
    }
    $conn->commit(); 
    echo json_encode(["queuesDeleted" => $queuesDeleted]);
}

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