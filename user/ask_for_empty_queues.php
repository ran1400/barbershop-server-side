<?php

require "utils/manager_block_system.php";

if ($managerBlockSystem)
    die("managerBlockSystem");

require "utils/connect.php";

if ($conn->connect_error)
   die("connection faild"); 
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem");
   

$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp =  $date->format('Y-m-d H:i:').'00';
$cmd = "SELECT Time FROM EmptyQueue WHERE Time >= '$timeStamp'";
$res = mysqli_query($conn,$cmd);
if($res)
{
    while ($row = mysqli_fetch_array($res))
        echo $row{'Time'};
}
else
    echo "cmd failed";


?>