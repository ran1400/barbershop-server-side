<?php


$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$sendNotifications =  $_POST["sendNotifications"];
$file = fopen("utils/send_user_unblock_notifications.php", "w") or die("cmd failed");
$fileContent = "<?php \$sendUserUnblockNotifications = " .$sendNotifications. ";?>";
fwrite($file, $fileContent);
fclose($file);
echo $sendNotifications;
?>