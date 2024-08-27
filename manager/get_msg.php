<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require "/home/u902940937/domains/ran-yehezkel.online/public_html/barbershop/commands/user/utils/msg.php";

echo $msg;

?>