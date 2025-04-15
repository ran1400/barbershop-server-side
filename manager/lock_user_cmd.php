<?php

$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem");

$file = fopen("../user/utils/manager_block_system.php", "w") or die("cmd failed");
$fileContent = "<?php \$managerBlockSystem = true;?>";
fwrite($file, $fileContent);
fclose($file);
echo("V");

?>