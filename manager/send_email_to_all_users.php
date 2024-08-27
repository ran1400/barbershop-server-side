<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
else
{
    $mailTitle = $_POST["mailTitle"];
    $mailBody = $_POST["mailBody"];
    $cmd = "SELECT Mail FROM User";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
       while ($row = mysqli_fetch_array($res))
          mail($row['Mail'],$mailTitle,$mailBody);
        die("V");
    }
    else
     echo("cmd failed");
}

?>