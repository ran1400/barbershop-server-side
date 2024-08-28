<?php
   
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
require $_SERVER['DOCUMENT_ROOT'].'/barbershop/commands/user/utils/manager_block_system.php';

if($managerBlockSystem)
    die("false");
else
    die("true");
?>