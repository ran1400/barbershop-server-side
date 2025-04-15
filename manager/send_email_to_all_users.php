<?php

require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 
   
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

if ($permission == false)
   die("permission problem"); 
else
{
    $cmd = "SELECT Mail FROM User";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
        $mailTitle = $_POST["mailTitle"];
        $mailBody = $_POST["mailBody"];
        require "../send_mail.php";
        $mail = createMail($mailTitle,$mailBody);
        while ($row = mysqli_fetch_array($res))
            $mail->addBCC($row['Mail']);
        if ( sendMail($mail) )
            echo 'V';
        else
            echo 'cmd failed';       
    }
    else
        echo "cmd failed";
}

?>