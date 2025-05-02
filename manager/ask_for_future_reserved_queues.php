<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));
   
$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"]));
else
{
    $timeStamp = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $timeStamp->format('Y-m-d H:i:').'00';
    $cmd = "SELECT ReservedQueue.Time,User.Name,User.Phone,User.Mail FROM ReservedQueue JOIN User 
    ON User.Mail = ReservedQueue.UserMail AND Time >= '$timeStamp' ORDER BY ReservedQueue.Time ASC";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
        $queues = mysqli_fetch_all($res, MYSQLI_ASSOC); 
        echo json_encode(["queues" => $queues]);
    }
    else
       echo json_encode(["error" => "cmd failed : " . $cmd]);
}

?>