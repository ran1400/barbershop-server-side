<?php


$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem");

$file = fopen("/home/u389811808/domains/ran140009g.online/public_html/commands/user/utils/manager_block_system.php", "w") or die("cmd failed");
$fileContent = "<?php \$managerBlockSystem = false;?>";
fwrite($file, $fileContent);
fclose($file);
echo("V");


?>