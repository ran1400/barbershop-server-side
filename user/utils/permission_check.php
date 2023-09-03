<?php

$cmd_ = "SELECT SecretKey FROM User WHERE Mail = '$userMail'";
$query_ = mysqli_query($conn,$cmd_);
if (!$query_)
    die("cmd failed");
$res_ = mysqli_fetch_assoc($query_);
$secretKey_ = $res_['SecretKey'];
if ($secretKey && $secretKey == $secretKey_)
  $permission = true;
else
  $permission = false;
?>