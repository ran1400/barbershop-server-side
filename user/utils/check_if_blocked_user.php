<?php

$cmd_ = "SELECT Block FROM User WHERE Mail = '$userMail'";
$query_ = mysqli_query($conn,$cmd_);
if (! $query_)
    die("cmd failed");
$res_ = mysqli_fetch_assoc($query_);
$blockedUser = $res_['Block'];

?>