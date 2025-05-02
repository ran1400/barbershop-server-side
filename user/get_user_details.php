<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"])); 

$userMail = $_POST["userMail"];
$query = "SELECT SecretKey,Name,Phone,Block FROM User WHERE Mail = ?";
$userDetails = runSelectQuery($conn,$query,[$userMail]);
if (! $userDetails)
   die(json_encode(["error" => "cmd failed :" .$query ]));
$serverSecretKey = $userDetails['SecretKey'];
$userSecretKey = $_POST["secretKey"];
if (! $serverSecretKey || !hash_equals($serverSecretKey,$userSecretKey))
    die(json_encode(["error" => "permission problem"]));
if ($userDetails['Block'])
    die(json_encode(["blockedUser" => "V"]));
$userName = $userDetails['Name'];
$userPhone = $userDetails['Phone'];
require "utils/get_crnt_queue.php";
$queue = getCrntQueue($userMail,$conn);
if($queue === null)
    die(json_encode(["error" => "cmd failed : get crnt queue"]));
require_once "../getMsgFromManager.php";
$msg = getMsgFromManager($conn);
if ($msg === null)
    die(json_encode(["error" => "cmd failed : get massage from manager"])); 
$userData = 
[
    "userName" => $userName,
    "userPhone" => $userPhone,
    "managerMsg" => $msg,
    "userMail" => $userMail
];
if($queue != false)
    $userData["userQueue"] = $queue;
$json = json_encode($userData);
echo $json;
?>