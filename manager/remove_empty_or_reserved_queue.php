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
    $conn->begin_transaction();
    $query = "DELETE FROM EmptyQueue WHERE Time = ?";
    $emptyQueueDelete = runExecQuery($conn,$query,[$date]);
    if ($emptyQueueDelete === null)
        die(json_encode(["error" => "cmd failed : " . $query]));
    if ($emptyQueueDelete === 1)
    {
        $conn->commit();
        echo json_encode(["queueDelete" => "empty queue"]);  
    }
    else // the queue is reserved queue
    {
        $query = "SELECT UserMail FROM ReservedQueue WHERE Time = ?";
        $reservedQueue = runSelectQuery($conn,$query,[$date]);
        if ($reservedQueue === null)
            die(json_encode(["error" => "cmd failed : " . $query]));
        if ($reservedQueue === false)
            die(json_encode(["error" => "queue not found"]));
         $userMail = $reservedQueue['UserMail'];
         $query = "DELETE FROM ReservedQueue WHERE Time = ? AND UserMail = ?";
         $reservedQueueDelete = runExecQuery($conn,$query,[$date,$userMail]);
         if ($reservedQueueDelete != 1)
            die(json_encode(["error" => "cmd failed : " . $query]));
         
        $notiContent = makeNotiContent($date);
        if (sendNotificationToUser($notiContent,$userMail) === false)
            die(json_encode(["error" => "send notification to user"]));
        if (sendMailToUser($notiContent,$userMail) === false)
            die(json_encode(["error" => "send mail to user"]));
        $conn->commit();
        echo json_encode(["queueDelete" => "reserved queue"]); 
    }
   
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