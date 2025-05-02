<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));  

$userMail = $_POST["userMail"];
$userSecretKey = $_POST["secretKey"];
$userName = $_POST["userName"];

require_once "utils/permission_check.php";

$permissionCheck = permissionCheck($conn,$userMail,$userSecretKey);
if ($permissionCheck  === null)
    die(json_encode(["error" => "cmd failed - permission check"]));
else if ($permissionCheck  === false)
    die(json_encode(["error" => "permission problem"]));

$conn->begin_transaction();
$query = "SELECT Time FROM ReservedQueue WHERE UserMail = ?";
$reservedQueue = runSelectQuery($conn,$query,[$userMail]);
if ($reservedQueue === null)
    die(json_encode(["error" => "cmd failed : " . $query]));
if ($reservedQueue) //to user have queue
{
    $timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
    $query = "SELECT Time FROM ReservedQueue WHERE Time < '$timeStamp' AND UserMail = ?";
    $pastReservedQueue = runSelectQuery($conn,$query,[$userMail]);
    if ($pastReservedQueue === null)
        die(json_encode(["error" => "cmd failed : " . $query]));
    $query = "DELETE FROM ReservedQueue WHERE UserMail  = ?";
    $queuesDeletedFromReservedQueue = runExecQuery($conn,$query,[$userMail]);
    if ( $queuesDeletedFromReservedQueue != 1)
        die(json_encode(["error" => "cmd failed : " . $query])); 
    if ($pastReservedQueue) //to the user have pest reserved queue
    {
        $query = "INSERT INTO PastReservedQueue VALUES (?,?)";
        $queuesInsertToPastReservedQueue = runExecQuery($conn,$query,[$reservedQueue["Time"],$userMail]);
        if ( $queuesInsertToPastReservedQueue != 1)
            die(json_encode(["error" => "cmd failed : " . $query]));  
    }
    else // to user have future reserved queue
    {
        $query = "INSERT INTO EmptyQueue VALUE (?)";
        $queuesInsertToEmptyQueue = runExecQuery($conn,$query,[$reservedQueue["Time"]]);
        if ( $queuesInsertToEmptyQueue != 1)
            die(json_encode(["error" => "cmd failed : " . $query]));  
        require_once "utils/to_send_notification.php";
        $toSendNotitficationToManager = toSendNotification($conn,$reservedQueue["Time"], $timeStamp);
        if ($toSendNotitficationToManager === null)
            die(json_encode(["error" => "cmd failed : check if send notification to manager"])); 
        if ($toSendNotitficationToManager)
        {
            $notificationSended = sendNotification($userName,$reservedQueue["Time"]);
            if ($notificationSended === false)
                die(json_encode(["error" => "cmd failed : send notification failed"]));    
        }
    }
}
    
$query = "DELETE FROM User WHERE Mail = ?";
$usersDeleted = runExecQuery($conn,$query,[$userMail]);
if ( $usersDeleted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));  
$query = "INSERT INTO DeletedUser VALUES (?,?)";
$usersInsertedToDeleteUser = runExecQuery($conn,$query,[$userMail,$userName]);
if ( $usersInsertedToDeleteUser != 1)
        die(json_encode(["error" => "cmd failed : " . $query])); 
$conn->commit();
echo json_encode(["error" => "no"]);  

function sendNotification($userName,$date)
{
    $year = substr($date,0,4);
    $month = substr($date,5,2);
    $day = substr($date,8,2);
    $hour =  substr($date,11,5);
    $notiTitle = $userName . " ביטל תור";
    $notiBody = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    require_once "utils/send_notification_to_manager.php";
    return sendFCM('userDeleteQueue',$notiTitle,$notiBody);
}

?>