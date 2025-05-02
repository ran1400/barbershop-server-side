<?php

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"])); 


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
        if ( sendMail($mail) === false)
            die(json_encode(["error" => "cmd failed : send mail"])); 
        echo json_encode(["error" => "no"]);
    }
    else
        die(json_encode(["error" => "cmd failed : " . $cmd]));


?>