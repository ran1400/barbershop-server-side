<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require '../user/utils/msg.php';

echo $msg;

?>