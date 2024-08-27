<?php

$idToken = $_POST['idToken']; 
require "utils/check_google_login.php";
$userMail = checkGoogleLogin($idToken);
if($userMail == false) // check Google Login retrun false 
    die ("X");

$name = $_POST["name"];
$phone = $_POST["phone"];
if ( strpos($name,"<") || strpos($name,"'") || mb_strlen($name) > 25 || strlen($phone) > 15 || !is_numeric($phone))
    die("cmd failed");
 
require "utils/connect.php";

if ($conn->connect_error)
    die("connection failed");
    
$conn->begin_transaction();
$cmd = "SELECT Mail FROM DeletedUser WHERE Mail = '$userMail'";
$query = mysqli_query($conn,$cmd);
if (!$query)
    die("cmd failed");
$res = mysqli_fetch_assoc($query);
if ($res['Mail']) //the user is in deletedUser table
{
    $cmd = "DELETE FROM DeletedUser WHERE Mail = '$userMail'";
    $query = mysqli_query($conn,$cmd);
    if ((!$query) || $conn->affected_rows != 1)
        die("cmd failed");
}    
require "utils/generate_uuid.php";
$secretKey = generateUuid();
$cmd = "INSERT INTO User VALUES ('$userMail','$name','$phone','$secretKey',0)"; // 0 is block user (false)
$query = mysqli_query($conn,$cmd);
if ($query && $conn->affected_rows == 1)
{
    $conn->commit();
    mail($userMail,"ברוך הבא למספרה","");
    echo $secretKey;
}
else
     echo "cmd failed"; 

?>