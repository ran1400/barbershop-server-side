<?php
require "utils/connect.php";

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
   
$seconds =  $_POST["seconds"];
$file = fopen("/home/u389811808/domains/ran140009g.online/public_html/commands/user/utils/seconds_amount_to_send_notification.php", "w") or die("cmd failed");
$fileContent = "<?php \$secondsAmountToSendNotification = " .$seconds. ";?>";
fwrite($file, $fileContent);
fclose($file);
echo $seconds;
?>