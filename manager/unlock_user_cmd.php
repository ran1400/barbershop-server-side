<?php

require_once "../sql.php";

$conn = getConn();

if ($conn->connect_error)
    die(json_encode(["error" => "sql connection failed"]));

$secretKey = $_POST["secretKey"];

require_once "utils/permission_check.php";

if (permissionCheck($secretKey) == false)
   die(json_encode(["error" => "permission problem"]));

$query = "UPDATE Setting SET ManagerBlockSystem = 0";
$res = mysqli_query($conn,$query);
if ( ! $res)
    die (json_encode(["error" => "cmd failed : " . $query]));
else 
    echo json_encode(["error" => "no"])


?>