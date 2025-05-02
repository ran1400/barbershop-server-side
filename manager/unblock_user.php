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
$conn->begin_transaction();

require "utils/to_send_user_unblock_notification.php";
$toSendUserUnblockNotification = toSendUserUnblockNotification($conn);
if ($toSendUserUnblockNotification === null)
    die(json_encode(["error" => "cmd failed : check if send unblock notification"])); 
    
$query = "UPDATE User SET block = 0 WHERE User.Mail = ?";
$usersUpdate = runExecQuery($conn,$query,[$userMail]);
if ($usersUpdate != 1)
    die(json_encode(["error" => "cmd failed : " . $query]));

if ($toSendUserUnblockNotification)
{
    require "../send_mail.php";
    $mailTitle = "חסימת החשבון שלך במספרה בוטלה";
    if (sendSingleMailWithoutBody($userMail,$mailTitle) === false)
        die(json_encode(["error" => "cmd failed : send mail to user"]));
}
 
$conn->commit();   
echo json_encode(["error" => "no"]);

?>