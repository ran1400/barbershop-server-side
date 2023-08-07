<?php
require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$msg =  $_POST["msg"];
$msgFile = fopen("/home/u389811808/domains/ran140009g.online/public_html/commands/user/utils/msg.php", "w") or die("cmd failed");
$fileText = "<?php \$msg = '" .$msg. "';?>";
fwrite($msgFile, $fileText);
fclose($msgFile);
echo $msg;
?>