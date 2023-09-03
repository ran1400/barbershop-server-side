<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem");

$file = fopen("/home/u902940937/domains/ran-yehezkel.online/public_html/barbershop/commands/user/utils/manager_block_system.php", "w") or die("cmd failed");
$fileContent = "<?php \$managerBlockSystem = true;?>";
fwrite($file, $fileContent);
fclose($file);
echo("V");

?>