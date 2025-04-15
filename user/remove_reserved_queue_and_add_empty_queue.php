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

if (checkIfBlockedUser($conn,$userMail))
   die(json_encode(["error" => "permission problem"]));


$conn->begin_transaction();
$query = "DELETE FROM ReservedQueue WHERE Time = ? AND UserMail = ?"; 
$queuesDeleted = runExecQuery($conn,$query,[$_POST["time"],$userMail]);
if ($queuesDeleted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$query = "INSERT INTO EmptyQueue VALUE (?)"; 
$queuesInserted = runExecQuery($conn,$query,[$_POST["time"]]);
if ($queuesInserted != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));
$timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
require 'utils/to_send_notification.php';
if (toSendNotification($_POST["time"],$timeStamp))
    sendNotification($_POST["userName"],$_POST["time"]);
$conn -> commit();
echo json_encode(["error" => "no"]);

                  
    
function sendNotification($userName,$time)
{
    $year = substr($time,0,4);
    $month = substr($time,5,2);
    $day = substr($time,8,2);
    $hour =  substr($time,11,5);
    $notiTitle = $userName . " ביטל תור";
    $notiBody = "בתאריך ".$day.".".$month.".".$year. " בשעה " . $hour;
    require "utils/send_notification_to_manager.php";
    sendFCM('userDeleteQueue',$notiTitle,$notiBody);
}

?>