<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"]));
   
   
$query = "SELECT SecondsToSendQueueNotification, SendUserBlockNotification, SendUserUnblockNotification, sendUserRemoveNotification FROM Setting";
$res = mysqli_query($conn, $query);

if (!$res)
    die(json_encode(["error" => "cmd failed : " . $query]));

$res = mysqli_fetch_assoc($res);
if (!$res)
    die(json_encode(["error" => "cmd failed : " . $query]));

echo json_encode([
    "secondsToSendQueueNotification" => $res["SecondsToSendQueueNotification"],
    "sendUserBlockNotification" => $res["SendUserBlockNotification"],
    "sendUserUnblockNotification" => $res["SendUserUnblockNotification"],
    "sendUserRemoveNotification" => $res["sendUserRemoveNotification"]]);

?>