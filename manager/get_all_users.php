<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp = $date->format('Y-m-d H:i:').'00';

$cmd = "SELECT Name,Phone,Mail,IF(ReservedQueue.UserMail IS NULL,NULL,ReservedQueue.Time) AS haveQueue 
        FROM User LEFT JOIN ReservedQueue ON User.Mail = ReservedQueue.UserMail AND ReservedQueue.Time >= '$timeStamp'
        WHERE User.Block = false";
$query = mysqli_query($conn,$cmd);
if ($query)
{
    $haveQueueUserCount = 0;
    $notHaveQueueUserCount = 0;
	while ($row = mysqli_fetch_array($query))
	{
	   if ($row['haveQueue'])
	       $haveQueueUserCount +=1;
	   else
	       $notHaveQueueUserCount += 1;
        echo $row['Name']."<".$row['Phone']."<".$row['Mail']."<".$row['haveQueue']."<";
	}
}
else
    die("cmd failed");  
$blockedUserCount = 0;
$cmd = "SELECT Name,Phone,Mail FROM User WHERE Block = true";
$query = mysqli_query($conn,$cmd);
if (!$query)
    die("cmd failed"); 
while ($row = mysqli_fetch_array($query))
{
	 echo $row['Name']."<".$row['Phone']."<".$row['Mail']."<";
	 $blockedUserCount += 1;
}
echo $notHaveQueueUserCount."<";
echo $haveQueueUserCount."<";
echo $blockedUserCount;
      

?>