<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$userMail = $_POST["userMail"];
$cmd = "SELECT SecretKey,Name,Phone,Block FROM User WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if (!$query)
   die("cmd failed");
$queryRes = mysqli_fetch_assoc($query);
$serverSecretKey = $queryRes['SecretKey'];
$userSecretKey = $_POST["secretKey"];
if ($serverSecretKey == null || $serverSecretKey != $userSecretKey)
    die("permission problem");
if ($queryRes['Block'])
    die("block user");
require "utils/msg.php";
$res = $queryRes['Name']."<".$queryRes['Phone']."<".$msg;
$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';
$cmd = "SELECT Time FROM ReservedQueue WHERE UserMail = '$userMail' AND Time >= '$timeStamp'";
$query = mysqli_query($conn,$cmd);
if ($query == null)
    die("cmd failed");
$queryRes = mysqli_fetch_assoc($query);
if ($queryRes['Time'] )
       echo $res."<".$queryRes['Time'];
else
       echo $res."<"."no";
?>