<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require "/home/u389811808/domains/ran140009g.online/public_html/commands/user/utils/msg.php";
echo $msg;

?>