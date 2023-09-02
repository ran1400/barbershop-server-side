<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
else
{
    $userMail = $_POST["userMail"]; 
    $cmd = "SELECT Name FROM User WHERE Mail = '$userMail'";
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die("cmd failed");
    $res = mysqli_fetch_assoc($query);
    if ($res['Name'])
       echo "no";
    else
       echo "yes";
}
?>