<?php



require "utils/connect.php";

if ($conn->connect_error)
   die("connection failed"); 

$cmd = "SELECT S1ecretKey,Name,Phone,Block FROM User";
$query = mysqli_query($conn,$cmd);
if ( ! $query)
    die ("cmd failed");
$queryRes = mysqli_fetch_assoc($query);
echo $queryRes;

?>