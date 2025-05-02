<?php

function toSendNotification($conn,$date,$timeStamp)
{
    $query = "SELECT SecondsToSendQueueNotification FROM Setting";
    $res = mysqli_query($conn,$query);
    if (! $res)
        return null;
    $res = mysqli_fetch_assoc($res);
    if (! $res)
        return null;
    $secnondToSendNotification = (int) $res["SecondsToSendQueueNotification"];
    if ($secnondToSendNotification == 2000000) //2000000 is always send notification to manager
        return true;
    else if ($secnondToSendNotification == 0)
        return false;
    if ($secondsAmountToSendNotification > (strtotime($date) - strtotime($timeStamp)))
        return true;
    else
        return false;
}

?>