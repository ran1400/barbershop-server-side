<?php

function getCrntQueue($userMail,$conn)
{
    $date = new DateTime("now", new DateTimeZone('Asia/Jerusalem') );
    $timeStamp =  $date->format('Y-m-d H:i:').'00';
    $query = "SELECT Time FROM ReservedQueue WHERE UserMail = ? AND Time >= '$timeStamp'";
    $queue = runSelectQuery($conn,$query,[$userMail]); //from sql.php
    if ($queue === null)
        return null;
    if ($queue === false)
        return false;
    return $queue['Time'];
}

?>