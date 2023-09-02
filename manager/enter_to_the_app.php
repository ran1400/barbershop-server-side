
<?php

//delete past empty queues and check if the manager block the system 

require "utils/connect.php";

if ($conn->connect_error)
   die("cmd failed");
   
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 

$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:s');

$cmd = "DELETE FROM `EmptyQueue` WHERE `Time` < '$timeStamp'";
$query = mysqli_query($conn,$cmd);
if ($query)
{
        require '/home/u389811808/domains/ran140009g.online/public_html/commands/user/utils/manager_block_system.php';
        if($managerBlockSystem)
            die("false");
        else
            die("true");
} 
else
         die("cmd failed");

?>

