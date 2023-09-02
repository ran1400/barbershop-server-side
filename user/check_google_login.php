<?php
//not for new user
$idToken = $_POST['idToken']; 
require "utils/check_google_login.php";
$userMail = checkGooglelogin($idToken);
if($userMail == false)
    die ("X");
else
{
    require "utils/connect.php";
    if ($conn->connect_error)
        die("connection faild"); 
    $cmd = "SELECT SecretKey FROM User WHERE Mail = '$userMail'";
    $query = mysqli_query($conn,$cmd);
    if (!$query)
        die("cmd faild");
    $res = mysqli_fetch_assoc($query);
    echo $res['SecretKey'];
}
?>