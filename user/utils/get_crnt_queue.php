<?php

function getCrntQueue($userMail,$conn)
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $query = "SELECT Time FROM ReservedQueue WHERE UserMail = ? AND Time >= '$timeStamp'";
    //require_once __DIR__ . "../../sql.php"; this in comment because its import already
    $queue = runSelectQuery($conn,$query,[$userMail]); 
    if ($queue === null)
        return null;
    if ($queue === false)
        return false;
    return $queue['Time'];
}

?>