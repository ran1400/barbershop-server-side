<?php

require "utils/manager_block_system.php";

if ($managerBlockSystem)
    die(json_encode(["error" => "manager block the system"]));

require_once "../sql.php";

$conn = getConn();
if ($conn->connect_error)
   die(json_encode(["error" => "sql connection failed"]));  
   
$userMail = $_POST["userMail"];
$secretKey = $_POST["secretKey"];

require "utils/permission_check.php";

$permissionCheck = permissionCheck($conn,$userMail,$secretKey);

if ($permissionCheck === null)
    die(json_encode(["error" => "cmd failed : permissin check"]));
else if ($permissionCheck === false)
   die(json_encode(["error" => "permission problem"]));  
  
$date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
$timeStamp = $date->format('Y-m-d H:i:').'00';
$query = "SELECT Time FROM EmptyQueue WHERE Time >= '$timeStamp'";
$res = mysqli_query($conn,$query);
if($res)
{
    $queues = array_map(function($row) {return $row['Time'];}, mysqli_fetch_all($res, MYSQLI_ASSOC));
    echo json_encode(["queues" => $queues]);
}
else
    echo json_encode(["error" => "cmd failed : " . $query]);

?>