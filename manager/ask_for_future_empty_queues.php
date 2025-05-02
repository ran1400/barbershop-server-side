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
    $cmd = "DELETE FROM EmptyQueue WHERE Time < '$timeStamp'";
    $res = mysqli_query($conn,$cmd);
    if (!$res)
        die(json_encode(["error" => "cmd failed : " . $cmd])); 
    $cmd = "SELECT Time FROM EmptyQueue WHERE Time >= '$timeStamp' ";
    $res = mysqli_query($conn,$cmd);
    if($res)
    {
        $queues = array_map(function($row) {return $row['Time'];}, mysqli_fetch_all($res, MYSQLI_ASSOC));
        echo json_encode(["queues" => $queues]);
    }
    else
         die(json_encode(["error" => "cmd failed : " . $cmd])); 
}

?>