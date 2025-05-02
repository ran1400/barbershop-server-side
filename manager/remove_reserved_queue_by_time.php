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
    $date =  $_POST["date"];
    $query = "SELECT UserMail FROM ReservedQueue WHERE Time = ?";
    $conn->begin_transaction();
    $queue = runSelectQuery($conn,$query,[$date]);
    if ($queue === null)
        die(json_encode(["error" => "cmd failed : " . $query]));
    if ($queue === false)
        die(json_encode(["error" => "queue not found"]));
    $userMail = $queue['UserMail'];
    $query = "DELETE FROM ReservedQueue WHERE Time = ?";
    $queueDelete = runExecQuery($conn,$query,[$date]);
    if ($queueDelete == 1)
    {
        $notiContent = makeNotiContent($date);
        if (sendNotificationToUser($notiContent,$userMail) === false)
            die(json_encode(["error" => "cmd failed : send notification to user"]));
        if (sendMailToUser($notiContent,$userMail) === false)
            die(json_encode(["error" => "cmd failed : send mail to user"])); 
        echo json_encode(["error" => "no"]);
        $conn->commit();
    }
    else
       die(json_encode(["error" => "cmd failed : " . $query]));
}


function makeNotiContent($date)
{
    $year = substr($date,0,4);
    $month = substr($date,4,2);
    $day = substr($date,6,2);
    $hour =  substr($date,8,2);
    $min = substr($date,10,2);
    return "התור שלך ב ".$day.".".$month.".".$year." בשעה ".$hour.":".$min . " בוטל" ;
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